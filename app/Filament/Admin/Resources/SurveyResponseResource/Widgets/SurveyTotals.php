<?php

namespace App\Filament\Admin\Resources\SurveyResponseResource\Widgets;

use App\Filament\Admin\Resources\SurveyResponseResource;
use App\Models\SurveyResponse;
use Filament\Widgets\Widget;

/** Totals of the questionnaire: reasons and products by count, criteria by average place */
class SurveyTotals extends Widget
{
    protected string $view = 'filament.admin.survey-totals';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        // Few hundred rows at most: counted in PHP, the JSON columns need no SQL per database
        $responses = SurveyResponse::query()->get(['reason', 'products', 'ranking']);
        $total = $responses->count();

        $reasons = collect(SurveyResponse::REASONS)
            ->map(fn (string $k) => [SurveyResponseResource::option('reasons', $k), $responses->where('reason', $k)->count()])
            ->sortByDesc(1)->values();

        $products = collect(SurveyResponse::PRODUCTS)
            ->map(fn (string $k) => [SurveyResponseResource::option('products', $k), $responses->filter(fn ($r) => in_array($k, $r->products ?? [], true))->count()])
            ->sortByDesc(1)->values();

        $criteria = collect(SurveyResponse::CRITERIA)
            ->map(function (string $k) use ($responses) {
                $places = $responses->map(fn ($r) => array_search($k, $r->ranking ?? [], true))->filter(fn ($p) => $p !== false);

                return [SurveyResponseResource::option('criteria', $k), $places->isEmpty() ? null : round($places->avg() + 1, 1), $places->count()];
            })
            ->sortBy(fn ($c) => $c[1] ?? PHP_INT_MAX)->values();

        return compact('total', 'reasons', 'products', 'criteria');
    }
}
