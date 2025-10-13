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

            // For testing - return mock success response
            // TODO: Uncomment when API is ready
            // $response = $this->propertyService->sendPropertyInsuranceRequest($apiData);

            // Mock response for testing
            $mockResponse = [
                'success' => true,
                'data' => [
                    'id' => 'PROP_' . uniqid(),
                    'status' => 'pending',
                    'application_number' => 'PROP' . rand(100000, 999999),
                ]
            ];

            return [
                'success' => true,
                'data' => $mockResponse['data'],
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
