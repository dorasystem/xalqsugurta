<?php

namespace App\Http\Controllers\Insurence;

use App\Exceptions\ProviderException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\ApiLogger;
use App\Services\OrderService;
use App\Services\Provider\ProviderApiTrait;
use Carbon\Carbon;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class BaseInsuranceController extends Controller
{
    use ProviderApiTrait, HandlesInsuranceErrors;

    abstract protected function getProductKey(): string;

    public function __construct(protected readonly OrderService $orderService) {}

    // ─── Session helpers ──────────────────────────────────────────────────────

    protected function sess(string $key, mixed $default = null): mixed
    {
        return session($this->getProductKey() . '.' . $key, $default);
    }

    protected function putSess(string $key, mixed $value): void
    {
        session([$this->getProductKey() . '.' . $key => $value]);
    }

    protected function clearSess(): void
    {
        session()->forget($this->getProductKey());
    }

    // ─── Phone normalizer ─────────────────────────────────────────────────────

    protected function cleanPhone(?string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone ?? '');

        if (str_starts_with($phone, '00998')) {
            $phone = substr($phone, 2);
        }

        if (strlen($phone) === 9) {
            $phone = '998' . $phone;
        }

        return $phone;
    }

    // ─── Person data normalizer ───────────────────────────────────────────────

    protected function normalizePerson(array $p, ?Request $r = null): array
    {
        return [
            'pinfl'                => $p['currentPinfl']           ?? '',
            'passport_seria'       => strtoupper($r?->input('passport_seria') ?? ''),
            'passport_number'      => $r?->input('passport_number') ?? '',
            'passport_issue_date'  => $this->personField($p, ['docIssueDate', 'issueDate', 'passportIssueDate', 'dateBegin', 'docGivenDate']),
            'passport_issued_by'   => $this->personField($p, ['docIssuedBy', 'issuedBy', 'passportIssuedBy', 'docGivePlace', 'givePlace']),
            'birth_date'           => $r?->input('birth_date') ?? ($p['birthDate'] ?? ''),
            'lastname'             => $p['lastNameLatin']   ?? $p['lastName']   ?? '',
            'firstname'            => $p['firstNameLatin']  ?? $p['firstName']  ?? '',
            'middlename'           => $p['middleNameLatin'] ?? $p['middleName'] ?? '',
            'gender'               => ($p['gender'] ?? '') == '1' ? 'm' : 'f',
            'address'              => $this->personField($p, ['address', 'permanentAddress', 'fullAddress']),
            'region_id'            => (int) ($this->personField($p, ['regionId', 'region_id', 'regionCode']) ?: 10),
            'district_id'          => (int) $this->personField($p, ['districtId', 'district_id', 'districtCode']),
            'phone'                => $this->cleanPhone($p['phone'] ?? $r?->input('phone') ?? ''),
            'resident_type'        => 1,
            'country_id'           => 210,
        ];
    }

    // ─── Product loader ───────────────────────────────────────────────────────

    protected function getProduct(): ?Product
    {
        return Product::where('route', $this->getProductKey())->first();
    }

    // ─── Offerta validation rule ──────────────────────────────────────────────

    protected function offertaRule(): array
    {
        return ['required', 'accepted'];
    }

    // ─── Session guard ────────────────────────────────────────────────────────

    protected function requireSession(string $key, string $redirectRoute): void
    {
        if (!$this->sess($key)) {
            redirect()->route($redirectRoute, ['locale' => getCurrentLocale()])->send();
            exit;
        }
    }

    // ─── Person lookup (PINFL, or legacy passport + birth date) ────────────────

    /**
     * Validation rules for a person lookup. PINFL is preferred; birth_date is
     * still accepted so pages not yet migrated to the PINFL field keep working.
     */
    protected function personLookupRules(): array
    {
        return [
            'passport_seria'  => ['required', 'string', 'max:4'],
            'passport_number' => ['required', 'digits:7'],
            'pinfl'           => ['required_without:birth_date', 'nullable', 'digits:14'],
            'birth_date'      => ['required_without:pinfl', 'nullable', 'date', 'before:today'],
        ];
    }

    /** Calls the provider by PINFL when given, otherwise by passport + birth date */
    protected function lookupPerson(Request $request): array
    {
        $document = strtoupper((string) $request->input('passport_seria')) . $request->input('passport_number');
        $pinfl    = (string) $request->input('pinfl');

        $person = $pinfl !== ''
            ? $this->findPersonByPinfl($pinfl, $document)
            : $this->findPersonByPassport($document, (string) $request->input('birth_date'));

        // Field names only (no personal data) — to map pinfl-v2 fields if they differ
        \Illuminate\Support\Facades\Log::info('Person lookup response fields', [
            'method' => $pinfl !== '' ? 'pinfl-v2' : 'passport-birth-date-v2',
            'keys'   => array_keys($person),
        ]);

        // pinfl-v2 may not echo the PINFL back under the same key
        if ($pinfl !== '') {
            $person['currentPinfl'] ??= $person['pinfl'] ?? $pinfl;
        }

        return $person;
    }

    /**
     * First non-empty value among the given keys. The pinfl-v2 and
     * passport-birth-date-v2 responses do not always use the same field names.
     */
    protected function personField(array $person, array $keys): string
    {
        foreach ($keys as $key) {
            if (filled($person[$key] ?? null) && !is_array($person[$key])) {
                return trim((string) $person[$key]);
            }
        }

        return '';
    }

    /** "Not found", or "the registry is down, try later" when the provider reports an outage */
    protected function lookupErrorMessage(ProviderException $e, ?string $notFound = null): string
    {
        return $e->isUnavailable()
            ? __t('messages.flow.registry_unavailable')
            : ($notFound ?? __('messages.person_not_found'));
    }

    /** Field that lookup errors are attached to */
    protected function personLookupErrorField(Request $request): string
    {
        return $request->filled('pinfl') ? 'pinfl' : 'passport_seria';
    }

    /**
     * Birth date as Y-m-d: from the request (legacy form), then the API
     * response, then decoded from the PINFL (digits 2–7 are DDMMYY,
     * the first digit gives the century: 1–2 → 1800s, 3–4 → 1900s, 5–6 → 2000s).
     */
    protected function personBirthDate(array $person, Request $request): string
    {
        foreach ([$request->input('birth_date'), $person['birthDate'] ?? null, $person['birth_date'] ?? null] as $value) {
            if (filled($value)) {
                try {
                    return Carbon::parse(str_replace('.', '-', (string) $value))->format('Y-m-d');
                } catch (\Carbon\Exceptions\InvalidFormatException) {
                    // try the next source
                }
            }
        }

        $pinfl = (string) ($person['currentPinfl'] ?? $request->input('pinfl'));

        if (preg_match('/^([1-6])(\d{2})(\d{2})(\d{2})\d{7}$/', $pinfl, $m)) {
            $century = [1 => 1800, 2 => 1800, 3 => 1900, 4 => 1900, 5 => 2000, 6 => 2000][(int) $m[1]];

            if (checkdate((int) $m[3], (int) $m[2], $century + (int) $m[4])) {
                return sprintf('%04d-%s-%s', $century + (int) $m[4], $m[3], $m[2]);
            }
        }

        return '';
    }

    /** '1' male / '2' female, from the API or the PINFL's first digit (odd = male) */
    protected function personGender(array $person): string
    {
        if (isset($person['gender']) && in_array((string) $person['gender'], ['1', '2'], true)) {
            return (string) $person['gender'];
        }

        $first = (int) substr((string) ($person['currentPinfl'] ?? ''), 0, 1);

        return $first > 0 && $first % 2 === 0 ? '2' : '1';
    }

    /**
     * Full step-1 handler body: validate, look the person up, and return the
     * normalized applicant, or a redirect back with the error.
     */
    protected function applicantFromRequest(Request $request): array|RedirectResponse
    {
        $request->validate($this->personLookupRules() + [
            'phone' => ['required', 'string', 'min:9', 'max:20'],
        ]);

        $errorField = $this->personLookupErrorField($request);

        try {
            $person = $this->lookupPerson($request);
        } catch (ProviderException $e) {
            return back()->withErrors([$errorField => $this->lookupErrorMessage($e)])->withInput();
        }

        if (empty($person['currentPinfl'] ?? null)) {
            return back()->withErrors([$errorField => __('messages.person_not_found')])->withInput();
        }

        return array_merge($this->normalizePerson($person, $request), [
            'pinfl'           => (string) $person['currentPinfl'],
            'passport_seria'  => strtoupper($request->input('passport_seria')),
            'passport_number' => $request->input('passport_number'),
            'birth_date'      => $this->personBirthDate($person, $request),
            'phone'           => $this->cleanPhone($request->input('phone')),
            'gender'          => $this->personGender($person),
        ]);
    }

    // ─── Shared AJAX: Find Person ─────────────────────────────────────────────

    public function findPerson(Request $request): JsonResponse
    {
        $request->validate($this->personLookupRules());

        try {
            $person = $this->lookupPerson($request);
        } catch (ProviderException $e) {
            return response()->json(['success' => false, 'message' => $this->lookupErrorMessage($e)], 422);
        }

        if (empty($person['currentPinfl'] ?? null)) {
            return response()->json(['success' => false, 'message' => __('messages.person_not_found')], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'pinfl'               => (string) $person['currentPinfl'],
                'passport_seria'      => strtoupper($request->input('passport_seria')),
                'passport_number'     => $request->input('passport_number'),
                'passport_issue_date' => $this->personField($person, ['docIssueDate', 'issueDate', 'passportIssueDate', 'dateBegin', 'docGivenDate']),
                'passport_issued_by'  => $this->personField($person, ['docIssuedBy', 'issuedBy', 'passportIssuedBy', 'docGivePlace', 'givePlace']),
                'firstname'           => $person['firstNameLatin']  ?? $person['firstName']  ?? '',
                'lastname'            => $person['lastNameLatin']   ?? $person['lastName']   ?? '',
                'middlename'          => $person['middleNameLatin'] ?? $person['middleName'] ?? '',
                'birth_date'          => $this->personBirthDate($person, $request),
                'gender'              => $this->personGender($person) === '1' ? 'm' : 'f',
                'address'             => $this->personField($person, ['address', 'permanentAddress', 'fullAddress']),
                'region_id'           => (int) ($this->personField($person, ['regionId', 'region_id', 'regionCode']) ?: 10),
                'district_id'         => (int) $this->personField($person, ['districtId', 'district_id', 'districtCode']),
                'phone'               => $this->cleanPhone($person['phone'] ?? ''),
                'resident_type'       => 1,
                'country_id'          => 210,
            ],
        ]);
    }

    // ─── Create order and redirect to payment ─────────────────────────────────

    protected function createOrderAndRedirect(array $data, string $productKey): RedirectResponse
    {
        // Embed the product key so payment handlers can identify the product
        if (isset($data['insurances_data']) && is_array($data['insurances_data'])) {
            $data['insurances_data']['_product_key'] = $productKey;
        }

        $order = $this->orderService->createOrder(array_merge([
            'status' => Order::STATUS_NEW,
        ], $data));

        // The contract submit ran before the order existed; link its API log rows now
        ApiLogger::attachToOrder($order);

        $this->clearSess();

        return redirect()->route('payment.show', [
            'locale'  => getCurrentLocale(),
            'orderId' => $order->id,
        ]);
    }
}
