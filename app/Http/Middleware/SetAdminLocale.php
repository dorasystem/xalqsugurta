<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The admin panel is Uzbek-only; keep Filament's own strings in Uzbek
 * regardless of the locale the visitor last used on the public site.
 */
class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('uz');

        // Carbon's plain "uz" is Cyrillic; the admin UI is written in Latin script
        Carbon::setLocale('uz_Latn');

        return $next($request);
    }
}
