<?php

if (!function_exists('__t')) {
    /**
     * Translation helper function
     */
    function __t(string $key, array $replace = [], ?string $locale = null): string
    {
        return trans($key, $replace, $locale);
    }
}

if (!function_exists('getLocalizedUrl')) {
    /**
     * Get localized URL helper
     */
    function getLocalizedUrl(string $locale): string
    {
        return app(\App\Services\LocaleService::class)->getLocalizedUrl($locale);
    }
}

if (!function_exists('getCurrentLocale')) {
    /**
     * Get current locale helper
     */
    function getCurrentLocale(): string
    {
        return app()->getLocale();
    }
}
