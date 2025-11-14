<?php

namespace App\Services;

class InsuranceApiConfiguratorService
{
    public function setupAccidentApi(InsuranceApiService $apiService): void
    {
        $baseUrl = (string) config('services.insurance.accident.endpoint', 'https://erspapi.e-osgo.uz/api/v3');
        $token = (string) config('services.insurance.accident.api_token');

        $apiService
            ->setEndpoint(rtrim($baseUrl))
            ->setProductType('ACCIDENT')
            ->setHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])
            ->setTimeout((int) config('services.insurance.accident.timeout', 10))
            ->setRetries((int) config('services.insurance.accident.retries', 3));
    }
}
