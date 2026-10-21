<?php

namespace App\Http\Middleware;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Closes a product's pages when "Sotuvda" is switched off in the admin panel.
 * Usage: ->middleware('product.on-sale:gas') with the product's `route` value.
 * A product that has no row in the products table stays open.
 */
final class EnsureProductOnSale
{
    public function handle(Request $request, Closure $next, string $route): Response
    {
        $isActive = Product::where('route', $route)->value('is_active');

        if ($isActive === null || $isActive) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => __t('messages.product_unavailable')], 404);
        }

        return redirect()
            ->route('home', ['locale' => getCurrentLocale()])
            ->with('error', __t('messages.product_unavailable'));
    }
}
