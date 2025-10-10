<?php

namespace Modules\ApiPolic\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'vin' => $this->vin,
            'license_plate' => $this->license_plate,
            'color' => $this->color,
            'engine_type' => $this->engine_type,
            'fuel_type' => $this->fuel_type,
            'transmission' => $this->transmission,
            'mileage' => $this->mileage,
            'status' => $this->status,
            'insurance_expires_at' => $this->insurance_expires_at?->format('Y-m-d'),
            'owner' => [
                'id' => $this->owner?->id,
                'name' => $this->owner?->name,
                'email' => $this->owner?->email,
            ],
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
