<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Payment system settings edited in the admin panel (Tizim → To'lov tizimlari).
 *
 * Saved values override config('services.click.*' / 'services.payme.*') at boot, so the
 * Click and Payme code keeps reading config(); a field left empty in the panel falls back
 * to .env. Secrets are stored encrypted and never sent back to the form.
 */
final class PaymentSettings
{
    private const CACHE_KEY = 'app_settings.payments';

    /** Setting key => [config path, is secret] */
    public const FIELDS = [
        'click.enabled'          => ['services.click.enabled', false],
        'click.service_id'       => ['services.click.service_id', false],
        'click.merchant_id'      => ['services.click.merchant_id', false],
        'click.merchant_user_id' => ['services.click.merchant_user_id', false],
        'click.secret_key'       => ['services.click.secret_key', true],
        'payme.enabled'          => ['services.payme.enabled', false],
        'payme.merchant_id'      => ['services.payme.merchant_id', false],
        'payme.secret_key'       => ['services.payme.production_secret_key', true],
        'payme.test_secret_key'  => ['services.payme.test_secret_key', true],
        'payme.test_mode'        => ['services.payme.test_mode', false],
    ];

    private const BOOLEANS = ['click.enabled', 'payme.enabled', 'payme.test_mode'];

    /** Copies the saved values into config(); called from AppServiceProvider::boot() */
    public static function apply(): void
    {
        foreach (self::stored() as $key => $value) {
            config([self::FIELDS[$key][0] => $value]);

            // The Payme cashbox id is both the checkout merchant and the callback login
            if ($key === 'payme.merchant_id') {
                config(['services.payme.kassa_id' => $value]);
            }
        }
    }

    /** Click can take payments: switched on and its ids and key are known */
    public static function clickReady(): bool
    {
        return (bool) config('services.click.enabled', true)
            && filled(config('services.click.service_id'))
            && filled(config('services.click.merchant_id'))
            && filled(config('services.click.secret_key'));
    }

    public static function paymeEnabled(): bool
    {
        return (bool) config('services.payme.enabled', true);
    }

    /** Current values for the admin form; secrets come back empty, with a "saved" flag */
    public static function formValues(): array
    {
        $values = [];
        foreach (self::FIELDS as $key => [$path, $secret]) {
            data_set($values, $key, $secret ? null : config($path));
            if ($secret) {
                data_set($values, $key . '_saved', filled(config($path)));
            }
        }

        foreach (self::BOOLEANS as $key) {
            data_set($values, $key, (bool) (data_get($values, $key) ?? $key !== 'payme.test_mode'));
        }

        return $values;
    }

    /** Saves the admin form; an empty secret keeps the stored one */
    public static function save(array $values): void
    {
        foreach (self::FIELDS as $key => [, $secret]) {
            $value = data_get($values, $key);

            if (in_array($key, self::BOOLEANS, true)) {
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
        self::apply();
    }

    /** Saved, non-empty values (decrypted) keyed like FIELDS */
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
                    continue;   // APP_KEY changed: the admin has to enter the secret again
                }
            }

            $values[$key] = in_array($key, self::BOOLEANS, true) ? $value === '1' : $value;
        }

        return $values;
    }
}
