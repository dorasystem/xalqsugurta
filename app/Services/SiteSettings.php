<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

/** Site-wide settings edited in the admin panel (Tizim → Sayt sozlamalari): social network links */
final class SiteSettings
{
    private const CACHE_KEY = 'app_settings.site';

    /** network => label; the icon is #icon-{network} in layouts/app */
    public const SOCIAL = [
        'instagram' => 'Instagram',
        'facebook'  => 'Facebook',
        'telegram'  => 'Telegram',
    ];

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

    public static function formValues(): array
    {
        $values = [];

        foreach (array_keys(self::SOCIAL) as $network) {
            data_set($values, 'site.social.' . $network, self::stored()['site.social.' . $network] ?? null);
        }

        return $values;
    }

    public static function save(array $values): void
    {
        foreach (array_keys(self::SOCIAL) as $network) {
            $key   = 'site.social.' . $network;
            $value = data_get($values, $key);

            AppSetting::updateOrCreate(['key' => $key], ['value' => blank($value) ? null : trim((string) $value)]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    private static function stored(): array
    {
        $keys = array_map(fn (string $n): string => 'site.social.' . $n, array_keys(self::SOCIAL));

        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => AppSetting::query()
                ->whereIn('key', $keys)
                ->pluck('value', 'key')
                ->all());
        } catch (QueryException) {
            return [];   // not migrated yet
        }
    }
}
