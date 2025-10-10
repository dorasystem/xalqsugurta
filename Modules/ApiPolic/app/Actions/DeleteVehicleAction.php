<?php

namespace Modules\ApiPolic\Actions;

use Modules\ApiPolic\Models\Vehicle;

final class DeleteVehicleAction
{
    /**
     * Execute the action to delete a vehicle.
     */
    public function execute(Vehicle $vehicle): bool
    {
        return $vehicle->delete();
    }
}
