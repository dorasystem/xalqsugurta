<?php

namespace App\Http\Controllers\Insurence\Concerns;

use App\Services\ProductSettings;
use Carbon\Carbon;

/**
 * Shared view data for "applicant → object + sum → confirm → payment" products
 * (gas balloon, property, KASKO). Views: pages/insurence/flow/{applicant,confirm}.
 *
 * The using controller defines a FLOW constant:
 *   key, icon, rate (%), rateLabel, min, max, default, presets, step (optional slider step, default 5 mln),
 *   objectKey   — session key of step 2 data ('property' | 'vehicle')
 *   objectStep  — step 2 route suffix ('getProperty' | 'getVehicle')
 *   objectTitle — translation key for the step 2 label
 * Rate, sums, presets and term can be overridden per product in the admin panel;
 * always read them through $this->flow(), never FLOW directly.
 */
trait InsuranceFlow
{
    use ConfigurableFlow;

    /** Short one-line label of the insured object for the summary sidebar */
    abstract protected function objectLabel(array $object): ?string;

    /** Rows for the object block on the confirm page: label => value */
    abstract protected function objectReview(array $object): array;

    protected function premiumFor(int $insuranceAmount): int
    {
        return ProductSettings::premium($this->flow(), $insuranceAmount);
    }

    protected function flowViewData(array $extra = []): array
    {
        $flow        = $this->flow();
        $key         = $flow['key'];
        $locale      = getCurrentLocale();
        $applicant   = $this->sess('applicant');
        $object      = $this->sess($flow['objectKey'], []);
        $calculation = $this->sess('calculation', []);
        $objectTitle = __t($flow['objectTitle']);

        $applicantName = $applicant
            ? trim($applicant['lastname'] . ' ' . $applicant['firstname'] . ' ' . ($applicant['middlename'] ?? ''))
            : null;

        $period = null;
        if (!empty($calculation['payment_start_date'])) {
            $period = Carbon::parse($calculation['payment_start_date'])->format('d.m.Y')
                . ' – ' . Carbon::parse($calculation['payment_end_date'])->format('d.m.Y');
        }

        return array_merge([
            'flow'          => $flow,
            'applicant'     => $applicant,
            $flow['objectKey'] => $object,
            'calculation'   => $calculation,
            'applicantName' => $applicantName,
            'objectTitle'   => $objectTitle,
            'objectReview'  => $object ? $this->objectReview($object) : [],
            'premiumTotal'  => $calculation['insurance_premium'] ?? null,
            'confirmBlocks' => [
                ['title' => $objectTitle, 'editUrl' => route($key . '.' . $flow['objectStep'], ['locale' => $locale]), 'items' => $object ? $this->objectReview($object) : []],
                ['title' => __t('messages.flow.policy_terms'), 'editUrl' => route($key . '.' . $flow['objectStep'], ['locale' => $locale]), 'items' => [
                    __('messages.insurance_sum')     => !empty($calculation['insurance_amount']) ? formatMoney($calculation['insurance_amount']) : null,
                    __t('messages.flow.period')      => $period,
                    __('messages.insurance_premium') => !empty($calculation['insurance_premium']) ? formatMoney($calculation['insurance_premium']) : null,
                ]],
            ],
            'flowSteps'     => [
                __t('messages.flow.applicant'),
                $objectTitle,
                __t('messages.confirm_details'),
                __t('messages.flow.payment'),
            ],
            'flowUrls'      => [
                route($key . '.index', ['locale' => $locale]),
                route($key . '.' . $flow['objectStep'], ['locale' => $locale]),
                route($key . '.getConfirm', ['locale' => $locale]),
            ],
            'summaryItems'  => [
                'applicant' => [__t('messages.flow.applicant'), $applicant ? $applicant['lastname'] . ' ' . mb_substr($applicant['firstname'], 0, 1) . '.' : null],
                'object'    => [$objectTitle, $object ? $this->objectLabel($object) : null],
                'sum'       => [__t('messages.insurance_sum'), !empty($calculation['insurance_amount']) ? formatMoney($calculation['insurance_amount']) : null],
                'period'    => [__t('messages.flow.period'), $period],
            ],
        ], $extra);
    }
}
