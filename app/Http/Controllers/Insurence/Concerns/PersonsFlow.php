<?php

namespace App\Http\Controllers\Insurence\Concerns;

use Carbon\Carbon;

/**
 * Shared view data for "applicant → insured persons → term → confirm → payment"
 * products (accident; tourist can use it too). Views: pages/insurence/flow/applicant,
 * pages/insurence/persons/{persons,term}, pages/insurence/flow/confirm.
 *
 * Premiums come from the provider calculator per person, so there is no fixed rate.
 * The using controller defines a FLOW constant:
 *   key, icon, min, max, step, default, presets (sum insured per person, UZS)
 * Sums, presets and term can be overridden per product in the admin panel;
 * always read them through $this->flow(), never FLOW directly.
 */
trait PersonsFlow
{
    use ConfigurableFlow;

    protected function flowViewData(array $extra = []): array
    {
        $flow        = $this->flow();
        $key         = $flow['key'];
        $locale      = getCurrentLocale();
        $applicant   = $this->sess('applicant');
        $persons     = $this->sess('persons', []);
        $calculation = $this->sess('calculation', []);

        $totalSum     = (int) array_sum(array_column($persons, 'sum_insured'));
        $totalPremium = (int) array_sum(array_column($persons, 'insurance_premium'));

        $applicantName = $applicant
            ? trim($applicant['lastname'] . ' ' . $applicant['firstname'] . ' ' . ($applicant['middlename'] ?? ''))
            : null;

        $period = null;
        if (!empty($calculation['start_date'])) {
            $period = Carbon::parse($calculation['start_date'])->format('d.m.Y')
                . ' – ' . Carbon::parse($calculation['end_date'])->format('d.m.Y');
        }

        $personsUrl = route($key . '.getPersons', ['locale' => $locale]);
        $termUrl    = route($key . '.getCalculator', ['locale' => $locale]);

        $personRows = [];
        foreach ($persons as $i => $p) {
            $name = ($i + 1) . '. ' . trim($p['lastname'] . ' ' . $p['firstname'] . ' ' . ($p['middlename'] ?? ''));
            $personRows[$name] = formatMoney($p['sum_insured']) . ' · ' . __t('messages.insurance_premium') . ': ' . formatMoney($p['insurance_premium']);
        }

        return array_merge([
            'flow'          => $flow + ['rateLabel' => null],
            'applicant'     => $applicant,
            'persons'       => $persons,
            'calculation'   => $calculation,
            'applicantName' => $applicantName,
            'totalSum'      => $totalSum,
            'premiumTotal'  => $persons ? $totalPremium : null,
            'flowSteps'     => [
                __t('messages.flow.applicant'),
                __t('messages.flow.persons'),
                __t('messages.flow.term'),
                __t('messages.confirm_details'),
                __t('messages.flow.payment'),
            ],
            'flowUrls'      => [
                route($key . '.index', ['locale' => $locale]),
                $personsUrl,
                $termUrl,
                route($key . '.getConfirm', ['locale' => $locale]),
            ],
            'summaryItems'  => [
                'applicant' => [__t('messages.flow.applicant'), $applicant ? $applicant['lastname'] . ' ' . mb_substr($applicant['firstname'], 0, 1) . '.' : null],
                'persons'   => [__t('messages.flow.persons'), $persons ? __t('messages.flow.persons_count', ['count' => count($persons)]) : null],
                'sum'       => [__t('messages.flow.total_sum'), $persons ? formatMoney($totalSum) : null],
                'period'    => [__t('messages.flow.period'), $period],
            ],
            'confirmBlocks' => [
                ['title' => __t('messages.flow.persons'), 'editUrl' => $personsUrl, 'items' => $personRows, 'wide' => true],
                ['title' => __t('messages.flow.policy_terms'), 'editUrl' => $termUrl, 'items' => [
                    __t('messages.flow.total_sum')   => formatMoney($totalSum),
                    __t('messages.flow.period')      => $period,
                    __('messages.insurance_premium') => formatMoney($totalPremium),
                ]],
            ],
        ], $extra);
    }
}
