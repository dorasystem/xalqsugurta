<?php

namespace Modules\ApiPolic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
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
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'vin' => 'required|string|unique:vehicles,vin|size:17',
            'license_plate' => 'required|string|unique:vehicles,license_plate|max:20',
            'color' => 'required|string|max:50',
            'engine_type' => 'required|string|max:50',
            'fuel_type' => 'required|in:gasoline,diesel,electric,hybrid',
            'transmission' => 'required|in:manual,automatic,cvt',
            'mileage' => 'required|integer|min:0',
            'status' => 'sometimes|in:active,inactive,sold',
            'owner_id' => 'required|exists:users,id',
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
