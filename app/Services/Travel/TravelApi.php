<?php

namespace App\Services\Travel;

use App\Exceptions\TravelApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The insurer's travel insurance API (ВЗР, "website" v2): open references, travel/calc,
 * travel/sale and get (policies after payment). Request bodies hold passport data: they are in
 * the admin "API jurnali" (ApiLogger), never in the app log.
 */
final class TravelApi
{
    /** References change rarely; the insurer asks to keep them for a day */
    private const REFERENCE_TTL = 86400;

    // ─── References ───────────────────────────────────────────────────────────

    /**
     * Countries on sale: [id => [id, name_ru, name_uz, name_en, iso, schengen, programs: [ids]]],
     * sorted by the API (by name).
     */
    public function countries(): array
    {
        $countries = [];

        foreach ($this->reference('reference/countries') as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $countries[$id] = [
                'id'       => $id,
                'name_ru'  => self::title((string) ($row['name1'] ?? '')),
                'name_uz'  => self::title((string) ($row['name3'] ?? $row['name1'] ?? '')),
                'name_en'  => self::title(preg_replace('/^SCHENGEN\s*-\s*/i', '', (string) ($row['ename'] ?? $row['name3'] ?? ''))),
                'iso'      => (string) ($row['kod'] ?? ''),
                'schengen' => (int) ($row['schengen'] ?? 0) === 1,
                'programs' => array_values(array_filter(range(1, 6), fn (int $n): bool => (int) ($row['pr' . $n] ?? 0) === 1)),
            ];
        }

        return $countries;
    }

    /** Programs on sale: [id => [id, name, liability (EUR), single_trip, multi_trip]] */
    public function programs(): array
    {
        $programs = [];

        foreach ($this->reference('reference/programs') as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $programs[$id] = [
                    'id'          => $id,
                    'name'        => (string) ($row['name'] ?? ''),
                    'liability'   => (int) ($row['liability'] ?? 0),
                    'single_trip' => (int) ($row['single_trip'] ?? 1) === 1,
                    'multi_trip'  => (int) ($row['multi_trip'] ?? 0) === 1,
                ];
            }
        }

        return $programs;
    }

    /** Regions of Uzbekistan: [id => [id, name_ru, name_uz (Latin)]] */
    public function regions(): array
    {
        $regions = [];

        foreach ($this->reference('reference/regions') as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $regions[$id] = [
                    'id'      => $id,
                    'name_ru' => (string) ($row['name_ru'] ?? ''),
                    'name_uz' => (string) ($row['name_lat'] ?? $row['name_uz'] ?? $row['name_ru'] ?? ''),
                ];
            }
        }

        return $regions;
    }

    /** All rows of a reference (every page), cached for a day; a failure is not cached */
    public function reference(string $path): array
    {
        $key = 'travel.reference.' . $path;

        if (($cached = Cache::get($key)) !== null) {
            return $cached;
        }

        $items  = [];
        $offset = 0;

        do {
            try {
                $response = Http::timeout(15)->retry(2, 500, throw: false)->acceptJson()
                    ->get($this->url($path), $offset > 0 ? ['offset' => $offset] : []);
            } catch (ConnectionException $e) {
                throw new TravelApiException('Travel API unavailable.', 503, previous: $e);
            }

            if (!$response->successful()) {
                Log::error('Travel reference HTTP error', ['path' => $path, 'status' => $response->status()]);
                throw new TravelApiException('Travel API unavailable.', 503);
            }

            $page    = $response->json() ?? [];
            $items   = [...$items, ...array_values((array) ($page['items'] ?? []))];
            $offset += (int) ($page['limit'] ?? 25) ?: 25;
        } while (($page['hasMore'] ?? false) === true && $offset < 5000);

        Cache::put($key, $items, self::REFERENCE_TTL);

        return $items;
    }

    // ─── Calculation, sale, policies ──────────────────────────────────────────

    /** travel/calc: the price of one program (policy.program) or of every available one (programs[]) */
    public function calc(array $body): array
    {
        return $this->post('travel/calc', $body, 30, $this->credentials());
    }

    /**
     * travel/sale: contract + registration in the national database + payment links. It can take
     * a minute. Retrying with the same order_id never makes a second contract.
     */
    public function sale(array $body): array
    {
        return $this->post('travel/sale', $body, 120, $this->credentials());
    }

    /** True when the `get` login (policies after payment) is set */
    public function canReadPolicies(): bool
    {
        return filled(config('provider.travel.get_username')) && filled(config('provider.travel.get_password'));
    }

    /** get: the contract's policies (none before payment) */
    public function policies(int $contractId): array
    {
        return $this->post('get', ['contract_id' => $contractId], 30, [
            (string) config('provider.travel.get_username'),
            (string) config('provider.travel.get_password'),
        ]);
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function credentials(): array
    {
        return [(string) config('provider.travel.username'), (string) config('provider.travel.password')];
    }

    private function url(string $path): string
    {
        return rtrim((string) config('provider.travel.base_url'), '/') . '/' . $path;
    }

    /** POST with Basic auth; result 0 → the answer, anything else → TravelApiException with the answer */
    private function post(string $path, array $body, int $timeout, array $auth): array
    {
        try {
            $response = Http::timeout($timeout)
                ->withBasicAuth($auth[0], $auth[1])
                ->acceptJson()
                ->withBody(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), 'application/json')
                ->post($this->url($path));
        } catch (ConnectionException $e) {
            Log::error('Travel API connection error', ['path' => $path, 'message' => $e->getMessage()]);
            throw new TravelApiException('Travel API unavailable.', 503, previous: $e);
        }

        return $this->answer($path, $response);
    }

    private function answer(string $path, Response $response): array
    {
        $data   = $response->json();
        $result = is_array($data) && isset($data['result']) ? (int) $data['result'] : null;

        if ($result === 0 && $response->successful()) {
            return $data;
        }

        // Not the API's own answer (a proxy page, an empty 5xx): an outage
        if ($result === null) {
            Log::error('Travel API HTTP error', ['path' => $path, 'status' => $response->status()]);
            throw new TravelApiException('Travel API unavailable.', $response->serverError() ? 503 : $response->status());
        }

        Log::warning('Travel API error', ['path' => $path, 'status' => $response->status(), 'result' => $result]);

        throw new TravelApiException(
            (string) ($data['result_message'] ?? 'Travel API error.'),
            $response->status(),
            $result,
            $data,
        );
    }

    /** "GRESIYA" → "Gresiya", "SCHENGEN - GREECE" → "Greece"; keeps names that are already mixed case */
    private static function title(string $name): string
    {
        $name = trim($name);

        return $name !== '' && mb_strtoupper($name) === $name ? mb_convert_case(mb_strtolower($name), MB_CASE_TITLE) : $name;
    }
}
