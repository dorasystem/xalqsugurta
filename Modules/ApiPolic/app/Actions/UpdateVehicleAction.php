<?php

namespace Modules\ApiPolic\Actions;

use Modules\ApiPolic\Models\Vehicle;
use Modules\ApiPolic\DTOs\VehicleData;

final class UpdateVehicleAction
{
    /**
     * Execute the action to update a vehicle.
     */
    public function execute(Vehicle $vehicle, VehicleData $data): Vehicle
    {
        $updateData = array_filter([
            'brand' => $data->brand,
            'model' => $data->model,
            'year' => $data->year,
            'vin' => $data->vin,
            'license_plate' => $data->license_plate,
            'color' => $data->color,
            'engine_type' => $data->engine_type,
            'fuel_type' => $data->fuel_type,
            'transmission' => $data->transmission,
            'mileage' => $data->mileage,
            'status' => $data->status,
            'owner_id' => $data->owner_id,
            'insurance_expires_at' => $data->insurance_expires_at,
        ], fn($value) => $value !== null);

        $vehicle->update($updateData);

        return $vehicle->fresh();
    }
}
