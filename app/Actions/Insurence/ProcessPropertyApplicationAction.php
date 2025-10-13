<?php

namespace App\Actions\Insurence;

use App\DTOs\PropertyApplicationData;
use App\Services\PropertyService;

final class ProcessPropertyApplicationAction
{

    public function __construct(
        private readonly PropertyService $propertyService
    ) {}

    public function execute(PropertyApplicationData $applicationData): array
    {
        try {
            // Convert DTO to API format
            $apiData = $applicationData->toApiFormat();

            // Send data to external API
            $response = $this->propertyService->sendPropertyInsuranceRequest($apiData);

            if (!$response['success']) {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? 'API xatosi',
                ];
            }

            return [
                'success' => true,
                'data' => $response['data'],
                'message' => 'Property insurance application processed successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Xatolik yuz berdi: ' . $e->getMessage(),
            ];
        }
    }
}
