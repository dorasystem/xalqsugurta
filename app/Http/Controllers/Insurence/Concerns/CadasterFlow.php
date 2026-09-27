<?php

namespace App\Http\Controllers\Insurence\Concerns;

/**
 * InsuranceFlow for cadaster-based products (gas balloon, property):
 * the insured object is real estate found by its cadastral number.
 *
 * FLOW additionally needs: cadasterRoute (AJAX lookup route name).
 */
trait CadasterFlow
{
    use InsuranceFlow;

    protected function objectLabel(array $object): ?string
    {
        if (empty($object['cadasterNumber'])) {
            return null;
        }

        return ($object['vidText'] ?: $object['tipText'] ?: $object['cadasterNumber'])
            . (!empty($object['objectArea']) ? ', ' . $object['objectArea'] . ' m²' : '');
    }

    protected function objectReview(array $object): array
    {
        return [
            __('messages.cadaster_number')        => $object['cadasterNumber'] ?? null,
            __('messages.property_short_address') => $object['shortAddress'] ?? null,
            __('messages.property_object_type')   => $object['tipText'] ?? null,
            __('messages.area')                   => !empty($object['objectArea']) ? $object['objectArea'] . ' m²' : null,
        ];
    }
}
