<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait OsgorInsuranceApi
{
    public function sendOsgorRequest(string $endpoint, array $data = [])
    {
        $baseUrl = config('services.insurance.eshop.base_url');
        $username = config('services.insurance.eshop.username');
        $password = config('services.insurance.eshop.password');

        if (empty($username) || empty($password)) {
            throw new \RuntimeException(
                'OSGOR API credentials missing. Set INSURANCE_ESHOP_USERNAME and INSURANCE_ESHOP_PASSWORD in .env'
            );
        }

        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
        ])->timeout(60)
            ->retry(3, 1000)
            ->post($url, $data);
    }
}
