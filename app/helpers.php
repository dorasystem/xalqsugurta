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

if (!function_exists('formatMoney')) {
    /**
     * Format an amount in UZS: 250000 → "250 000 so'm"
     */
    function formatMoney(int|float|null $amount): string
    {
        return number_format((float) $amount, 0, '.', ' ') . ' ' . __t('messages.currency');
    }
}

if (!function_exists('formatPhone')) {
    /**
     * Format an Uzbek phone number for display: 998901234567 → "+998 90 123 45 67"
     */
    function formatPhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone ?? '');

        if (strlen($digits) !== 12 || !str_starts_with($digits, '998')) {
            return (string) $phone;
        }

        return sprintf('+%s %s %s %s %s',
            substr($digits, 0, 3), substr($digits, 3, 2), substr($digits, 5, 3), substr($digits, 8, 2), substr($digits, 10, 2));
    }
}
