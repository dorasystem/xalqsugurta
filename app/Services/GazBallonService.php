<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

final class GazBallonService
{
    private string $baseUrl;
    private string $proxyOsago;
    private string $initiateUrl;
    private string $authHeader;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $cfg = config('services.gaz');
        $this->baseUrl = rtrim((string)($cfg['base_url'] ?? ''), '/');
        $this->proxyOsago = (string)($cfg['proxy_osago'] ?? '/xs/ins/osago/proxy');
        $this->initiateUrl = (string)($cfg['initiate'] ?? '/xs/ins/unv/gazballonsayt/InitiateTransactionRequest');
        $user = (string)($cfg['basic_user'] ?? '');
        $pass = (string)($cfg['basic_pass'] ?? '');
        $this->authHeader = 'Basic ' . base64_encode($user . ':' . $pass);
        $this->timeout = (int)($cfg['timeout'] ?? 15);
        $this->retries = (int)($cfg['retries'] ?? 2);
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => $this->authHeader,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout($this->timeout)->retry($this->retries, 500);
    }

    public function fetchPerson(string $pinfl, string $passport, string $birthDate): array
    {
        $payload = [
            'transactionId' => (string) round(microtime(true) * 1000),
            'isConsent' => 'Y',
            'senderPinfl' => $pinfl,
            'document' => $passport,
            'birthDate' => $birthDate,
        ];

        $response = $this->client()
            ->withHeaders([
                'param' => '/api/provider/passport-birth-date-v2',
                'mtd' => 'POST',
            ])
            ->post($this->baseUrl . $this->proxyOsago, $payload);

        return $this->normalize($response);
    }

    public function fetchCadaster(string $cadasterNumber): array
    {
        $payload = ['cadasterNumber' => $cadasterNumber];

        $response = $this->client()
            ->withHeaders([
                'param' => '/api/provider/cadaster-info',
                'mtd' => 'POST',
            ])
            ->post($this->baseUrl . $this->proxyOsago, $payload);

        return $this->normalize($response);
    }

    public function initiateTransaction(array $data): array
    {
        $response = $this->client()->post($this->baseUrl . $this->initiateUrl, $data);
        return $this->normalize($response);
    }

    private function normalize($response): array
    {
        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
                'status' => $response->status(),
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('message') ?? $response->body() ?? 'API error',
            'status' => $response->status(),
            'data' => $response->json(),
        ];
    }
}


