<?php

namespace Modules\ApiPolic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
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
        $vehicleId = $this->route('vehicle');

        return [
            'brand' => 'sometimes|string|max:100',
            'model' => 'sometimes|string|max:100',
            'year' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'vin' => [
                'sometimes',
                'string',
                'size:17',
                Rule::unique('vehicles', 'vin')->ignore($vehicleId),
            ],
            'license_plate' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('vehicles', 'license_plate')->ignore($vehicleId),
            ],
            'color' => 'sometimes|string|max:50',
            'engine_type' => 'sometimes|string|max:50',
            'fuel_type' => 'sometimes|in:gasoline,diesel,electric,hybrid',
            'transmission' => 'sometimes|in:manual,automatic,cvt',
            'mileage' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:active,inactive,sold',
            'owner_id' => 'sometimes|exists:users,id',
            'insurance_expires_at' => 'sometimes|date|after:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'vin.unique' => 'This VIN number is already registered.',
            'vin.size' => 'VIN must be exactly 17 characters.',
            'license_plate.unique' => 'This license plate is already registered.',
            'year.max' => 'Year cannot be greater than current year.',
            'mileage.min' => 'Mileage cannot be negative.',
            'insurance_expires_at.after' => 'Insurance expiration date must be in the future.',
        ];
    }
}
