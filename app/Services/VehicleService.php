<?php

namespace App\Services;

use App\Traits\NappApi;

class VehicleService
{
    use NappApi;

    /**
     * Get vehicle info
     *
     * @param array $data
     * @return array
     */
    public function getVehicleInfoByTechPassport(array $data): array
    {
        return $this->getVehicleInfo(
            $data['gov_number'],
            $data['tech_passport_seria'],
            $data['tech_passport_number']
        );
    }

    /**
     * Get vehicle owner info
     *
     * @param array $vehicleData
     * @return array
     */
    public function getVehicleOwnerInfo(array $vehicleData): array
    {
        if (isset($vehicleData['owner_pinfl'])) {
            return $this->getPersonByPinfl($vehicleData['owner_pinfl']);
        }

        return [
            'success' => false,
            'message' => 'Owner PINFL not found in vehicle data',
        ];
    }
}






















