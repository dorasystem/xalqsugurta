<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/** Product info page (/{locale}/products/{route}): texts, FAQ and rules edited in the admin panel */
final class ProductPageController extends Controller
{
    public function show(string $locale, string $product): View|RedirectResponse
    {
        $model = Product::where('route', $product)->where('is_active', true)->firstOrFail();

        // Nothing written for this locale yet: go straight to the application
        if (!$model->hasInfo($locale)) {
            return redirect($model->url());
        }

        return view('pages.product.show', [
            'product' => $model,
            'info'    => $model->info($locale),
            'locale'  => $locale,
        ]);
    }
}
