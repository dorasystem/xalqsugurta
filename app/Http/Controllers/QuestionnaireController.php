<?php

namespace App\Http\Controllers;

use App\Models\SurveyResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Questionnaire (/{locale}/questionnaire): three questions, answers counted in the admin panel */
final class QuestionnaireController extends Controller
{
    public function create(): View
    {
        return view('pages.questionnaire.create', [
            'reasons'  => SurveyResponse::REASONS,
            'products' => SurveyResponse::PRODUCTS,
            'criteria' => SurveyResponse::CRITERIA,
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        if (filled($request->input('website'))) {
            return redirect()->route('questionnaire', ['locale' => $locale]);
        }

        $count = count(SurveyResponse::CRITERIA);
        $data = validator($request->all(), [
            'reason'        => ['required', Rule::in(SurveyResponse::REASONS)],
            'reason_other'  => ['nullable', 'required_if:reason,other', 'string', 'max:300'],
            'products'      => ['required', 'array', 'min:1'],
            'products.*'    => [Rule::in(SurveyResponse::PRODUCTS)],
            'product_other' => ['nullable', 'string', 'max:300'],
            'rank'          => ['nullable', 'array'],
            'rank.*'        => ['nullable', 'integer', 'between:1,' . $count],
        ], [
            'reason_other.required_if' => __t('messages.survey.other_required'),
        ])->after(function (Validator $validator) use ($request) {
            $ranks = array_filter((array) $request->input('rank', []), 'filled');
            if (count($ranks) !== count(array_unique($ranks))) {
                $validator->errors()->add('rank', __t('messages.survey.rank_unique'));
            }
            if (in_array('other', (array) $request->input('products', []), true) && blank($request->input('product_other'))) {
                $validator->errors()->add('product_other', __t('messages.survey.other_required'));
            }
        })->validate();

        // Criteria the visitor ranked, most important first; unknown keys are dropped
        $ranking = collect($data['rank'] ?? [])
            ->filter(fn ($rank, $key) => filled($rank) && in_array($key, SurveyResponse::CRITERIA, true))
            ->sort()
            ->keys()
            ->values()
            ->all();

        SurveyResponse::create([
            'reason'        => $data['reason'],
            'reason_other'  => $data['reason'] === 'other' ? $data['reason_other'] : null,
            'products'      => array_values(array_unique($data['products'])),
            'product_other' => in_array('other', $data['products'], true) ? ($data['product_other'] ?? null) : null,
            'ranking'       => $ranking ?: null,
            'locale'        => $locale,
        ]);

        Log::info('Questionnaire answered');

        return redirect()->route('questionnaire', ['locale' => $locale])->with('status', __t('messages.survey.sent'));
    }
}
