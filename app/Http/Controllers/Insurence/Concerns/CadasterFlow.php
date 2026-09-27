<?php

namespace App\Http\Controllers\Insurence\Concerns;

use Carbon\Carbon;

/**
 * Shared view data for cadaster-based products (gas balloon, property):
 * applicant → property (cadaster + sum) → confirm → payment.
 *
 * The using controller defines a FLOW constant:
 *   key, icon, rate (%), rateLabel, min, max, default, presets, cadasterRoute
 */
trait CadasterFlow
{
    protected function premiumFor(int $insuranceAmount): int
    {
        return (int) round($insuranceAmount * static::FLOW['rate'] / 100);
    }

    protected function flowViewData(array $extra = []): array
    {
        $key         = static::FLOW['key'];
        $locale      = getCurrentLocale();
        $applicant   = $this->sess('applicant');
        $property    = $this->sess('property', []);
        $calculation = $this->sess('calculation', []);

        $applicantName = $applicant
            ? trim($applicant['lastname'] . ' ' . $applicant['firstname'] . ' ' . ($applicant['middlename'] ?? ''))
            : null;

        $propertyLabel = null;
        if (!empty($property['cadasterNumber'])) {
            $propertyLabel = ($property['vidText'] ?: $property['tipText'] ?: $property['cadasterNumber'])
                . (!empty($property['objectArea']) ? ', ' . $property['objectArea'] . ' m²' : '');
        }

        $period = null;
        if (!empty($calculation['payment_start_date'])) {
            $period = Carbon::parse($calculation['payment_start_date'])->format('d.m.Y')
                . ' – ' . Carbon::parse($calculation['payment_end_date'])->format('d.m.Y');
        }

        return array_merge([
            'flow'          => static::FLOW,
            'applicant'     => $applicant,
            'property'      => $property,
            'calculation'   => $calculation,
            'applicantName' => $applicantName,
            'flowSteps'     => [
                __t('messages.flow.applicant'),
                __t('messages.flow.property'),
                __t('messages.confirm_details'),
                __t('messages.flow.payment'),
            ],
            'flowUrls'      => [
                route($key . '.index', ['locale' => $locale]),
                route($key . '.getProperty', ['locale' => $locale]),
                route($key . '.getConfirm', ['locale' => $locale]),
            ],
            'summaryItems'  => [
                'applicant' => [__t('messages.flow.applicant'), $applicant ? $applicant['lastname'] . ' ' . mb_substr($applicant['firstname'], 0, 1) . '.' : null],
                'property'  => [__t('messages.flow.property'), $propertyLabel],
                'sum'       => [__t('messages.insurance_sum'), !empty($calculation['insurance_amount']) ? formatMoney($calculation['insurance_amount']) : null],
                'period'    => [__t('messages.flow.period'), $period],
            ],
        ], $extra);
    }
}
