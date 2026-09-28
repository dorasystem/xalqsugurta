<?php

namespace App\Http\Controllers\Insurence\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * InsuranceFlow for cadaster-based products (gas balloon, property):
 * the insured object is real estate found by its cadastral number.
 *
 * FLOW additionally needs: cadasterRoute (AJAX lookup route name).
 * The using controller has a PropertyService in $this->propertyService.
 */
trait CadasterFlow
{
    use InsuranceFlow;

    /**
     * XX:XX:XX:XX:XX:XXXX, then any number of blocks after ":" or "/" — both
     * 11:11:11:11:11:1111:2222:333 and 11:11:11:11:11:1111/2222 are real numbers.
     * The page masks the input the same way (cadaster/property.blade.php, formatCadaster()).
     */
    protected function cadasterRule(): array
    {
        return ['required', 'string', 'max:40', 'regex:/^\d{2}(:\d{2}){4}:\d{4}([:\/]\d{1,4})*$/'];
    }

    protected function cadasterMessages(string $field): array
    {
        return [$field . '.regex' => __t('messages.flow.cadaster_format_error')];
    }

    /** AJAX lookup shared by gas balloon and property */
    protected function cadasterLookup(Request $request): JsonResponse
    {
        $request->validate(['cadasterNumber' => $this->cadasterRule()], $this->cadasterMessages('cadasterNumber'));

        $result = $this->propertyService->fetchPropertyByCadaster($request->input('cadasterNumber'));

        if (!$result['success'] || empty($result['result'])) {
            $message = ($result['reason'] ?? 'not_found') === 'unavailable'
                ? __t('messages.flow.cadaster_unavailable')
                : __t('messages.flow.cadaster_not_found');

            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return response()->json(['success' => true, 'result' => $result['result']]);
    }

    protected function objectLabel(array $object): ?string
    {
        if (empty($object['cadasterNumber'])) {
            return null;
        }

        return (($object['vidText'] ?? '') ?: ($object['tipText'] ?? '') ?: $object['cadasterNumber'])
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
