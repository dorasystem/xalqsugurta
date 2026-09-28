<?php

namespace App\Services\Sms;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Eskiz.uz SMS gateway (notify.eskiz.uz/api).
 * Login (email + password) gives a bearer token valid for ~30 days; it is cached and
 * fetched again once when Eskiz answers 401.
 */
final class EskizClient
{
    public const TOKEN_CACHE_KEY = 'eskiz.token';

    /** Sends $text to 998XXXXXXXXX; throws SmsException when Eskiz refuses or is down */
    public function send(string $phone, string $text): void
    {
        $response = $this->post('/message/sms/send', [
            'mobile_phone' => $phone,
            'message'      => $text,
            'from'         => (string) config('services.eskiz.from'),
        ]);

        if ($response->status() === 401) {
            Cache::forget(self::TOKEN_CACHE_KEY);
            $response = $this->post('/message/sms/send', [
                'mobile_phone' => $phone,
                'message'      => $text,
                'from'         => (string) config('services.eskiz.from'),
            ]);
        }

        if (!$response->successful()) {
            // No phone number or text in the log: only what Eskiz said
            Log::warning('Eskiz SMS refused', ['status' => $response->status(), 'message' => $response->json('message')]);

            throw new SmsException((string) ($response->json('message') ?: 'HTTP ' . $response->status()));
        }
    }

    private function post(string $path, array $data): Response
    {
        try {
            return Http::timeout(10)
                ->asForm()
                ->withToken($this->token())
                ->post($this->url($path), $data);
        } catch (ConnectionException $e) {
            throw new SmsException('Eskiz is not reachable', 0, $e);
        }
    }

    private function token(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addDays(25), function (): string {
            try {
                $response = Http::timeout(10)->asForm()->post($this->url('/auth/login'), [
                    'email'    => (string) config('services.eskiz.email'),
                    'password' => (string) config('services.eskiz.password'),
                ]);
            } catch (ConnectionException $e) {
                throw new SmsException('Eskiz is not reachable', 0, $e);
            }

            $token = $response->json('data.token');
            if (!$response->successful() || !is_string($token) || $token === '') {
                Log::warning('Eskiz login failed', ['status' => $response->status(), 'message' => $response->json('message')]);

                throw new SmsException('Eskiz login failed');
            }

            return $token;
        });
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.eskiz.base_url'), '/') . $path;
    }
}
