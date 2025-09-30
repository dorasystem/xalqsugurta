<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class LocaleService
{
    /**
     * Available languages
     */
    private array $availableLocales = ['ru', 'uz', 'en'];

    /**
     * Get current locale
     */
    public function getCurrentLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Get available locales
     */
    public function getAvailableLocales(): array
    {
        return $this->availableLocales;
    }

    /**
     * Set locale
     */
    public function setLocale(string $locale): void
    {
        if (in_array($locale, $this->availableLocales)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        }
    }

    /**
     * Get localized URL for current route
     */
    public function getLocalizedUrl(string $locale): string
    {
        $currentUrl = URL::current();
        $segments = explode('/', $currentUrl);

        // Check if current URL already has a locale prefix
        if (in_array($segments[3] ?? null, $this->availableLocales)) {
            $segments[3] = $locale;
        } else {
            // Insert locale as first segment after domain
            array_splice($segments, 3, 0, $locale);
        }

        return implode('/', $segments);
    }

    /**
     * Get locale display name
     */
    public function getLocaleDisplayName(string $locale): string
    {
        return match ($locale) {
            'ru' => 'Русский',
            'uz' => 'O\'zbek',
            'en' => 'English',
            default => $locale,
        };
    }

    /**
     * Check if locale is available
     */
    public function isLocaleAvailable(string $locale): bool
    {
        return in_array($locale, $this->availableLocales);
    }
}

