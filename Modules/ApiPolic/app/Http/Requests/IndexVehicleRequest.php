<?php

namespace Modules\ApiPolic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search' => 'sometimes|string|max:255',
            'brand' => 'sometimes|string|max:100',
            'year_from' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'year_to' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'status' => 'sometimes|in:active,inactive,sold',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'year_from.max' => 'Year from cannot be greater than current year.',
            'year_to.max' => 'Year to cannot be greater than current year.',
            'per_page.max' => 'Per page cannot exceed 100 items.',
        ];
    }
}
