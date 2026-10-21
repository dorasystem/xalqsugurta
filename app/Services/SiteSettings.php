<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

/**
 * Site-wide settings edited in the admin panel (Tizim → Sayt sozlamalari):
 * social network links and the home page figures.
 */
final class SiteSettings
{
    private const CACHE_KEY = 'app_settings.site';

    /** network => label; the icon is #icon-{network} in layouts/app */
    public const SOCIAL = [
        'instagram' => 'Instagram',
        'facebook'  => 'Facebook',
        'telegram'  => 'Telegram',
    ];

    /** Home page figures ("50+ filial…"): slots 1..STATS, defaults in messages.homepage.stats */
    public const STATS = 4;

    public const LOCALES = ['uz', 'ru', 'en'];

    /** Filled links only (network => url): an empty one hides its icon */
    public static function social(): array
    {
        $stored = self::stored();
        $links  = [];

        foreach (array_keys(self::SOCIAL) as $network) {
            if (filled($stored['site.social.' . $network] ?? null)) {
                $links[$network] = $stored['site.social.' . $network];
            }
        }

        return $links;
    }

    /**
     * Figures for the home page: [['value' => '50+', 'label' => '…'], …]. A slot never saved shows
     * its default; one saved with an empty value is hidden.
     */
    public static function stats(?string $locale = null): array
    {
        $locale ??= getCurrentLocale();
        $stored   = self::stored();
        $stats    = [];

        for ($i = 1; $i <= self::STATS; $i++) {
            $key   = 'site.stats.' . $i . '.value';
            $value = array_key_exists($key, $stored) ? $stored[$key] : self::defaultStat($i, 'value', $locale);

            if (blank($value)) {
                continue;
            }

            $stats[] = [
                'value' => $value,
                'label' => ($stored['site.stats.' . $i . '.label_' . $locale] ?? null) ?: self::defaultStat($i, 'label', $locale),
            ];
        }

        return $stats;
    }

    public static function formValues(): array
    {
        $stored = self::stored();
        $values = [];

        foreach (self::keys() as $key) {
            $default = null;
            if (preg_match('/^site\.stats\.(\d)\.(value|label)(?:_(\w+))?$/', $key, $m)) {
                $default = self::defaultStat((int) $m[1], $m[2], $m[3] ?? 'uz');
            }

            data_set($values, $key, array_key_exists($key, $stored) ? $stored[$key] : $default);
        }

        return $values;
    }

    public static function save(array $values): void
    {
        foreach (self::keys() as $key) {
            $value = data_get($values, $key);

            AppSetting::updateOrCreate(['key' => $key], ['value' => blank($value) ? null : trim((string) $value)]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    /** Every key this class stores */
    private static function keys(): array
    {
        $keys = array_map(fn (string $n): string => 'site.social.' . $n, array_keys(self::SOCIAL));

        for ($i = 1; $i <= self::STATS; $i++) {
            $keys[] = 'site.stats.' . $i . '.value';
            foreach (self::LOCALES as $locale) {
                $keys[] = 'site.stats.' . $i . '.label_' . $locale;
            }
        }

        return $keys;
    }

    private static function defaultStat(int $slot, string $field, string $locale): ?string
    {
        $key  = 'messages.homepage.stats.' . $slot . '.' . $field;
        $text = __($key, [], $locale);

        return $text === $key ? null : $text;
    }

    private static function stored(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => AppSetting::query()
                ->whereIn('key', self::keys())
                ->pluck('value', 'key')
                ->all());
        } catch (QueryException) {
            return [];   // not migrated yet
        }
    }
}
