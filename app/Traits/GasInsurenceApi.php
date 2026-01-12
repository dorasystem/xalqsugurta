<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait GasInsurenceApi
{
    private string $baseUrl = 'http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt';
    private string $username = 'gazballonsayt';
    private string $password = 'dorasystem1';

    public function sendGasRequest(string $endpoint, array $data = [])
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
        ])->timeout(60)
            ->retry(3, 1000)
            ->post($url, $data);
    }
}

