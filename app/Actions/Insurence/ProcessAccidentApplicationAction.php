<?php

namespace App\Actions\Insurence;

use App\DTOs\AccidentApplicationData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class ProcessAccidentApplicationAction
{
    private string $apiToken;
    private string $apiUrl;

    public function __construct()
    {
        // $this->apiToken = config('services.napp.api_token', 'https://erspapi.e-osgo.uz/api/v3');
        // $this->apiUrl = config('services.napp.api_url', 'https://erspapi.e-osgo.uz/api/v3');
        $this->apiToken = 'https://erspapi.e-osgo.uz/api/v3';
        $this->apiUrl = 'https://erspapi.e-osgo.uz/api/v3';
    }

    public function execute(AccidentApplicationData $data): array
    {
        try {
            $requestData = $data->toApiFormat();

            Log::info('Sending accident application to API', [
                'data' => $requestData,
            ]);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->apiToken}",
            ])->post("{$this->apiUrl}/contract", $requestData);

            if ($response->failed()) {
                Log::error('API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'API-ga ma\'lumot yuborishda xatolik yuz berdi.',
                    'details' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            $responseData = $response->json();

            Log::info('API response received', [
                'response' => $responseData,
            ]);

            return [
                'success' => true,
                'data' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('Exception during API request', [
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
