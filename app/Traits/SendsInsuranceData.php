<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait SendsInsuranceData
{
    /**
     * Send insurance application data to external API
     *
     * @param array $requestData
     * @param string $endpoint
     * @param string $productType
     * @param array $headers
     * @param int $timeout
     * @param int $retries
     * @return array
     */
    protected function sendToInsuranceApi(
        array $requestData,
        string $endpoint,
        string $productType,
        array $headers = [],
        int $timeout = 10,
        int $retries = 3
    ): array {
        $attempt = 0;
        $lastError = null;

        while ($attempt < max(1, $retries)) {
            $attempt++;
            $start = microtime(true);

            try {
                Log::info("Sending {$productType} application to API", [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'timeout' => $timeout,
                    'payload' => $requestData,
                ]);

                $request = Http::timeout($timeout);
                if (!empty($headers)) {
                    $request = $request->withHeaders($headers);
                }

                $response = $request->post($endpoint, $requestData);
                $durationMs = (int) round((microtime(true) - $start) * 1000);

                $json = $response->json();

                Log::info("{$productType} API response", [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'status' => $response->status(),
                    'duration_ms' => $durationMs,
                    'response' => $json,
                ]);

                // Standardize response according to validation rules
                $statusOk = ($json['status'] ?? null) === 200;
                $apiError = data_get($json, 'response.error');
                $uuid = data_get($json, 'response.result.uuid');

                if ($statusOk && $apiError === 0 && !empty($uuid)) {
                    return [
                        'success' => true,
                        'data' => $json,
                        'uuid' => $uuid,
                        'error' => null,
                    ];
                }

                // Failure path - build error message/array
                $errorMessage = data_get($json, 'response.error_message');
                $errorsArray = data_get($json, 'response.result');
                $errorOut = $errorsArray ?? $errorMessage ?? 'Insurance API returned an error.';

                // If we got here and HTTP failed, include status
                if ($response->failed()) {
                    $lastError = $errorOut;
                } else {
                    // Even if HTTP succeeded, validation failed
                    $lastError = $errorOut;
                }

                // No retry on 4xx validation errors except 429
                if ($response->clientError() && $response->status() !== 429) {
                    break;
                }
            } catch (RequestException | ConnectionException $e) {
                $durationMs = (int) round((microtime(true) - $start) * 1000);
                Log::warning("{$productType} API network/request error", [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'duration_ms' => $durationMs,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
                $lastError = $e->getMessage();
            } catch (\Throwable $e) {
                $durationMs = (int) round((microtime(true) - $start) * 1000);
                Log::error("{$productType} API unexpected error", [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'duration_ms' => $durationMs,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
                $lastError = $e->getMessage();
            }

            // Exponential backoff before next attempt
            if ($attempt < $retries) {
                $backoffMs = (int) (200 * (2 ** ($attempt - 1))); // 200ms, 400ms, 800ms, ...
                Log::warning("{$productType} API retrying", [
                    'endpoint' => $endpoint,
                    'next_delay_ms' => $backoffMs,
                    'attempt' => $attempt + 1,
                ]);
                usleep($backoffMs * 1000);
            }
        }

        return [
            'success' => false,
            'data' => null,
            'uuid' => null,
            'error' => $lastError ?? 'Insurance API request failed.',
        ];
    }

    /**
     * Validate API response structure
     *
     * @param array $response
     * @param array $requiredKeys
     * @return bool
     */
    protected function validateApiResponse(array $response, array $requiredKeys = []): bool
    {
        if (!array_key_exists('success', $response)) {
            return false;
        }

        if ($response['success'] !== true) {
            return false;
        }

        if (!empty($requiredKeys)) {
            $data = $response['data'] ?? [];
            foreach ($requiredKeys as $key) {
                if (!isset($data[$key])) {
                    return false;
                }
            }
        }

        // Additionally ensure OSAGO success shape
        $statusOk = ($response['data']['status'] ?? null) === 200;
        $apiError = $response['data']['response']['error'] ?? null;
        $uuid = $response['data']['response']['result']['uuid'] ?? null;

        return $statusOk && $apiError === 0 && !empty($uuid);
    }
}
