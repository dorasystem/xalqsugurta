<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Eskiz SMS settings edited in the admin panel (Tizim → SMS xabarlar).
 * Saved values override config('services.eskiz.*') at boot; empty fields fall back to .env.
 * The password is stored encrypted and never sent back to the form.
 */
final class SmsSettings
{
    private const CACHE_KEY = 'app_settings.sms';

    /** Setting key => [config path, is secret] */
    public const FIELDS = [
        'eskiz.enabled'  => ['services.eskiz.enabled', false],
        'eskiz.email'    => ['services.eskiz.email', false],
        'eskiz.password' => ['services.eskiz.password', true],
        'eskiz.from'     => ['services.eskiz.from', false],
        'eskiz.template' => ['services.eskiz.template', false],
    ];

    public static function apply(): void
    {
        foreach (self::stored() as $key => $value) {
            config([self::FIELDS[$key][0] => $value]);
        }
    }

    /** Switched on and the login is known */
    public static function ready(): bool
    {
        return (bool) config('services.eskiz.enabled')
            && filled(config('services.eskiz.email'))
            && filled(config('services.eskiz.password'));
    }

    public static function formValues(): array
    {
        $values = [];
        foreach (self::FIELDS as $key => [$path, $secret]) {
            data_set($values, $key, $secret ? null : config($path));
            if ($secret) {
                data_set($values, $key . '_saved', filled(config($path)));
            }
        }
        data_set($values, 'eskiz.enabled', (bool) data_get($values, 'eskiz.enabled'));

        return $values;
    }

    /** An empty password keeps the stored one */
    public static function save(array $values): void
    {
        foreach (self::FIELDS as $key => [, $secret]) {
            $value = data_get($values, $key);

            if ($key === 'eskiz.enabled') {
                $value = $value ? '1' : '0';
            } elseif ($secret && blank($value)) {
                continue;
            } else {
                $value = blank($value) ? null : trim((string) $value);
            }

            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $secret && $value !== null ? Crypt::encryptString($value) : $value],
            );
        }

        Cache::forget(self::CACHE_KEY);
        Cache::forget(Sms\EskizClient::TOKEN_CACHE_KEY);   // new login → new token
        self::apply();
    }

    private static function stored(): array
    {
        try {
            $rows = Cache::rememberForever(self::CACHE_KEY, fn () => AppSetting::query()
                ->whereIn('key', array_keys(self::FIELDS))
                ->pluck('value', 'key')
                ->all());
        } catch (QueryException) {
            return [];   // not migrated yet
        }

        $values = [];
        foreach ($rows as $key => $value) {
            if ($value === null || $value === '' || !isset(self::FIELDS[$key])) {
                continue;
            }

            if (self::FIELDS[$key][1]) {
                try {
                    $value = Crypt::decryptString($value);
                } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                    continue;   // APP_KEY changed: the admin has to enter it again
                }
            }

            $values[$key] = $key === 'eskiz.enabled' ? $value === '1' : $value;
        }

        return $values;
    }
}
