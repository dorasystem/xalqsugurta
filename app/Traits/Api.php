<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait Api
{
    private $baseUrl = 'http://online.xalqsugurta.uz/xs/ins/osago/proxy';
    private $username = 'XWEB';
    private $password = '1GsdMHa053Msd@';

    public function sendRequest(string $endpoint, array $data = [])
    {
        $endpoint = '/' . ltrim($endpoint, '/');

        return Http::withHeaders([
            'param' => $endpoint,
            'mtd' => 'POST',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
        ])->timeout(4)
            ->retry(3, 1000)
            ->post($this->baseUrl, $data);
    }
}
