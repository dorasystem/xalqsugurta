<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

/**
 * Insurer API settings edited in the admin panel (Tizim → Sug'urtachi API).
 *
 * Saved values override config('provider.*') at boot, so API code keeps reading config();
 * a field left empty falls back to .env.
 */
final class ProviderSettings
{
    private const CACHE_KEY = 'app_settings.provider';

    /** Setting key => config path */
    public const FIELDS = [
        'provider.agency_id'            => 'provider.agency_id',
        'provider.osago_legal_entities' => 'provider.osago.legal_entities',
    ];

    /** On/off fields: saved as '1' / '0', so "off" overrides an .env "on" */
    public const SWITCHES = ['provider.osago_legal_entities'];

    /** Config values before the panel's override (.env; env() is null once config is cached) */
    private static array $fallback = [];

    /** Copies the saved values into config(); called from AppServiceProvider::boot() */
    public static function apply(): void
    {
        foreach (self::FIELDS as $key => $path) {
            self::$fallback[$key] ??= config($path);
        }

        foreach (self::stored() as $key => $value) {
            config([self::FIELDS[$key] => in_array($key, self::SWITCHES, true) ? $value === '1' : $value]);
        }
    }

    /** The saved value (null = .env is used) for each field */
    public static function formValues(): array
    {
        $stored = self::stored();
        $values = [];

        foreach (array_keys(self::FIELDS) as $key) {
            $value = $stored[$key] ?? null;
            data_set($values, $key, in_array($key, self::SWITCHES, true) ? ($value === null ? (bool) self::envValue($key) : $value === '1') : $value);
        }

        return $values;
    }

    public static function save(array $values): void
    {
        foreach (array_keys(self::FIELDS) as $key) {
            $value = data_get($values, $key);
            $value = in_array($key, self::SWITCHES, true)
                ? ($value === null ? null : ((bool) $value ? '1' : '0'))
                : (blank($value) ? null : trim((string) $value));

            AppSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);

        // A cleared field goes back to .env
        foreach (self::FIELDS as $key => $path) {
            if (array_key_exists($key, self::$fallback)) {
                config([$path => self::$fallback[$key]]);
            }
        }

        self::apply();
    }

    /** Value from .env, shown as the fallback in the form */
    public static function envValue(string $key): ?string
    {
        $value = self::$fallback[$key] ?? config(self::FIELDS[$key]);

        return blank($value) ? null : (string) $value;
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

        return array_filter($rows, fn ($value, $key) => filled($value) && isset(self::FIELDS[$key]), ARRAY_FILTER_USE_BOTH);
    }
}
