<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait Api
{
    private string $baseUrl = 'http://online.xalqsugurta.uz/xs/ins';
    private string $username = 'XWEB';
    private string $password = '1GsdMHa053Msd@';

    public function sendRequest(string $endpoint = '', array $data = [], string $url = '/osago/proxy')
    {
        $endpoint = '/' . ltrim($endpoint, '/');

        $response =  Http::withHeaders([
            'param' => $endpoint,
            'mtd' => 'POST',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
        ])->timeout(4)
            ->retry(3, 1000)
            ->post($this->baseUrl . $url, $data);


        Log::info('OSAGO HTTP Status: ' . $response->status());
        Log::info('OSAGO HTTP Headers: ', $response->headers());
        Log::info('OSAGO HTTP Body: ' . $response->body());

        return $response;
    }
}
