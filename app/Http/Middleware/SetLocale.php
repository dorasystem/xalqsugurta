<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Available languages
     */
    private array $availableLocales = ['ru', 'uz', 'en'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getLocale($request);

        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Get the locale from request
     */
    private function getLocale(Request $request): string
    {
        // Check if locale is in URL segment
        $urlLocale = $request->segment(1);
        if (in_array($urlLocale, $this->availableLocales)) {
            Session::put('locale', $urlLocale);
            return $urlLocale;
        }

        // Check session
        if (Session::has('locale') && in_array(Session::get('locale'), $this->availableLocales)) {
            return Session::get('locale');
        }

        // Check browser language
        $browserLocale = $request->getPreferredLanguage($this->availableLocales);
        if ($browserLocale) {
            Session::put('locale', $browserLocale);
            return $browserLocale;
        }

        // Default to Russian
        Session::put('locale', 'ru');
        return 'ru';
    }
}
