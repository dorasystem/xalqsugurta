<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class PropertyService
{
    private string $cadasterApiUrl;
    private string $propertyInsuranceApiUrl;

    public function __construct()
    {
        $this->cadasterApiUrl = config('services.impex.cadaster_api_url', 'https://impex-insurance.uz/api/fetch-cadaster');
        $this->propertyInsuranceApiUrl = config('services.impex.property_insurance_api_url', 'https://impex-insurance.uz/api/property-insurance');
    }

    /**
     * Fetch property information by cadaster number
     */
    public function fetchPropertyByCadaster(string $cadasterNumber): array
    {
        try {
            Log::info('Fetching property by cadaster number', [
                'cadaster_number' => $cadasterNumber,
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->cadasterApiUrl, [
                    'cadasterNumber' => $cadasterNumber,
                ]);

            if ($response->failed()) {
                Log::error('Cadaster API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Kadastr ma\'lumotlarini olishda xatolik yuz berdi.',
                    'message' => $response->json('error_message') ?? 'API xatosi',
                ];
            }

            $data = $response->json();

            if (isset($data['error']) && $data['error'] !== 0) {
                return [
                    'success' => false,
                    'error' => $data['error_message'] ?? 'Kadastr ma\'lumoti topilmadi',
                ];
            }

            Log::info('Property data fetched successfully', [
                'result' => $data['result'] ?? null,
            ]);

            return [
                'success' => true,
                'result' => $data['result'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Exception while fetching property data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Tizim xatosi yuz berdi.',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send property insurance application to external API
     */
    public function sendPropertyInsuranceRequest(array $data): array
    {
        try {
            Log::info('Sending property insurance application', [
                'data' => $data,
            ]);

            $response = Http::timeout(60)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->propertyInsuranceApiUrl, $data);

            if ($response->failed()) {
                Log::error('Property insurance API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Property insurance API xatosi',
                    'message' => $response->json('error_message') ?? 'API xatosi',
                ];
            }

            $responseData = $response->json();

            Log::info('Property insurance application sent successfully', [
                'response' => $responseData,
            ]);

            return [
                'success' => true,
                'data' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('Exception while sending property insurance application', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Tizim xatosi yuz berdi.',
                'message' => $e->getMessage(),
            ];
        }
    }
}
