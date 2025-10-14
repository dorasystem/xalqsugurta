<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait NappApi
{
    /**
     * NAPP API base URL
     */
    protected string $nappApiUrl = 'https://napp.uz/api/v1';

    /**
     * Get person info by passport
     *
     * @param string $passportSeria
     * @param string $passportNumber
     * @return array
     */
    public function getPersonByPassport(string $passportSeria, string $passportNumber): array
    {
        try {
            $response = Http::timeout(30)->get("{$this->nappApiUrl}/person-info", [
                'passport_seria' => $passportSeria,
                'passport_number' => $passportNumber,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'API error',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('NAPP API Error (getPersonByPassport): ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get person info by birth date
     *
     * @param string $passportSeria
     * @param string $passportNumber
     * @param string $birthDate
     * @return array
     */
    public function getPersonByBirthDate(string $passportSeria, string $passportNumber, string $birthDate): array
    {
        try {
            $response = Http::timeout(30)->get("{$this->nappApiUrl}/person-info", [
                'passport_seria' => $passportSeria,
                'passport_number' => $passportNumber,
                'birth_date' => $birthDate,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'API error',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('NAPP API Error (getPersonByBirthDate): ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get person info by PINFL
     *
     * @param string $pinfl
     * @return array
     */
    public function getPersonByPinfl(string $pinfl): array
    {
        try {
            $response = Http::timeout(30)->get("{$this->nappApiUrl}/person-info", [
                'pinfl' => $pinfl,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'API error',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('NAPP API Error (getPersonByPinfl): ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get vehicle info
     *
     * @param string $govNumber
     * @param string $techPassportSeria
     * @param string $techPassportNumber
     * @return array
     */
    public function getVehicleInfo(string $govNumber, string $techPassportSeria, string $techPassportNumber): array
    {
        try {
            $response = Http::timeout(30)->get("{$this->nappApiUrl}/vehicle-info", [
                'gov_number' => $govNumber,
                'tech_passport_seria' => $techPassportSeria,
                'tech_passport_number' => $techPassportNumber,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'API error',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('NAPP API Error (getVehicleInfo): ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Make a generic GET request to NAPP API
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    protected function nappApiGet(string $endpoint, array $params = []): array
    {
        try {
            $url = "{$this->nappApiUrl}/{$endpoint}";
            $response = Http::timeout(30)->get($url, $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'API error',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error("NAPP API Error ({$endpoint}): " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Make a generic POST request to NAPP API
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    protected function nappApiPost(string $endpoint, array $data = []): array
    {
        try {
            $url = "{$this->nappApiUrl}/{$endpoint}";
            $response = Http::timeout(30)->post($url, $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'API error',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error("NAPP API Error ({$endpoint}): " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Set custom API URL
     *
     * @param string $url
     * @return $this
     */
    public function setApiUrl(string $url): self
    {
        $this->nappApiUrl = rtrim($url, '/');
        return $this;
    }

    /**
     * Get current API URL
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->nappApiUrl;
    }
}







