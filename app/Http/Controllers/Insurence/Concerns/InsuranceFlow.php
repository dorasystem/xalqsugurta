<?php

namespace App\Http\Controllers\Insurence\Concerns;

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
 */
trait InsuranceFlow
{
    /** Short one-line label of the insured object for the summary sidebar */
    abstract protected function objectLabel(array $object): ?string;

    /** Rows for the object block on the confirm page: label => value */
    abstract protected function objectReview(array $object): array;

    protected function premiumFor(int $insuranceAmount): int
    {
        return (int) round($insuranceAmount * static::FLOW['rate'] / 100);
    }

    protected function flowViewData(array $extra = []): array
    {
        $key         = static::FLOW['key'];
        $locale      = getCurrentLocale();
        $applicant   = $this->sess('applicant');
        $object      = $this->sess(static::FLOW['objectKey'], []);
        $calculation = $this->sess('calculation', []);
        $objectTitle = __t(static::FLOW['objectTitle']);

        $applicantName = $applicant
            ? trim($applicant['lastname'] . ' ' . $applicant['firstname'] . ' ' . ($applicant['middlename'] ?? ''))
            : null;

        $period = null;
        if (!empty($calculation['payment_start_date'])) {
            $period = Carbon::parse($calculation['payment_start_date'])->format('d.m.Y')
                . ' – ' . Carbon::parse($calculation['payment_end_date'])->format('d.m.Y');
        }

        return array_merge([
            'flow'          => static::FLOW,
            'applicant'     => $applicant,
            static::FLOW['objectKey'] => $object,
            'calculation'   => $calculation,
            'applicantName' => $applicantName,
            'objectTitle'   => $objectTitle,
            'objectReview'  => $object ? $this->objectReview($object) : [],
            'flowSteps'     => [
                __t('messages.flow.applicant'),
                $objectTitle,
                __t('messages.confirm_details'),
                __t('messages.flow.payment'),
            ],
            'flowUrls'      => [
                route($key . '.index', ['locale' => $locale]),
                route($key . '.' . static::FLOW['objectStep'], ['locale' => $locale]),
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
