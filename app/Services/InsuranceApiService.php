<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class InsuranceApiService
{
    private string $endpoint;
    private string $productType;
    private array $headers;
    private int $timeout;
    private int $retries;

    /**
     * @param string $endpoint API endpoint URL
     * @param string $productType Product type (OSAGO, etc.)
     * @param array $headers Optional HTTP headers
     * @param int $timeout Request timeout in seconds
     * @param int $retries Number of retry attempts
     */
    public function __construct(
        ?string $endpoint = null,
        ?string $productType = null,
        array $headers = [],
        ?int $timeout = null,
        ?int $retries = null
    ) {
        $this->endpoint = $endpoint ?? (string) config('services.insurance.osago.endpoint', 'http://online.xalqsugurta.uz/xs/ins/doraosago/create');
        $this->productType = strtoupper($productType ?? 'OSAGO');
        $this->headers = $headers;
        $this->timeout = $timeout ?? (int) config('services.insurance.osago.timeout', 10);
        $this->retries = $retries ?? (int) config('services.insurance.osago.retries', 3);
    }

    /**
     * Send application data to insurance API
     *
     * @param array $data Application data in API format
     * @return array{success:bool,data:array|null,uuid:?string,error:mixed}
     */
    public function sendApplication(array $data): array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < max(1, $this->retries)) {
            $attempt++;
            $start = microtime(true);

            try {
                $request = Http::timeout($this->timeout);

                if ($this->productType === 'OSAGO') {
                    $username = config('services.insurance.osago.username');
                    $password = config('services.insurance.osago.password');
                    if ($username && $password) {
                        $request = $request->withBasicAuth($username, $password);
                    }
                }

                if (!empty($this->headers)) {
                    $request = $request->withHeaders($this->headers);
                }

                $response = $request->post($this->endpoint, $data);
                $result = $this->standardizeResponse($response->json(), $response->status());

                if ($result['success']) {
                    return $result;
                }

                $lastError = $result['error'];

                if ($response->clientError() && $response->status() !== 429) {
                    break;
                }
            } catch (RequestException | ConnectionException $e) {
                $lastError = $e->getMessage();
            } catch (\Throwable $e) {
                Log::error("{$this->productType} API error", [
                    'endpoint' => $this->endpoint,
                    'attempt' => $attempt,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
                $lastError = $e->getMessage();
            }

            if ($attempt < $this->retries) {
                usleep((int) (200 * (2 ** ($attempt - 1))) * 1000);
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
     * Convert raw API response to standardized format and apply success rules.
     *
     * Success when:
     * - status === 200
     * - response.error === 0
     * - response.result.uuid exists
     *
     * @param array|null $json
     * @param int $httpStatus
     * @return array{success:bool,data:array|null,uuid:?string,error:mixed}
     */
    private function standardizeResponse(?array $json, int $httpStatus): array
    {
        $json = $json ?? [];
        $statusOk = $httpStatus === 200;
        $apiError = data_get($json, 'response.error');
        $uuid = null;

        // Resolve UUID based on product type
        if (in_array($this->productType, ['ACCIDENT', 'PROPERTY'], true)) {
            // Try to extract from first policy or contractUuid
            $uuid = data_get($json, 'response.result.policies.0.uuid') ?? data_get($json, 'response.result.contractUuid');
            if ($statusOk && $apiError === 0 && !empty($uuid)) {
                return [
                    'success' => true,
                    'data' => $json,
                    'uuid' => $uuid,
                    'error' => null,
                ];
            }
        } else {
            // OSAGO path - new API format
            // Success when: result === 0 and UUID exists
            $result = $json['result'] ?? null;
            $uuid = $json['UUID'] ?? data_get($json, 'response.result.uuid') ?? data_get($json, 'response.result.contractUuid');

            if ($result === 0 && !empty($uuid)) {
                return [
                    'success' => true,
                    'data' => $json,
                    'uuid' => $uuid,
                    'error' => null,
                ];
            }
        }

        // Try different error message locations
        $errorMessage = $json['result_message'] ?? data_get($json, 'response.error_message');
        $errorsArray = data_get($json, 'response.result');
        $errorOut = $errorsArray ?? $errorMessage ?? "Insurance API returned an error (HTTP {$httpStatus}).";

        return [
            'success' => false,
            'data' => null,
            'uuid' => null,
            'error' => $errorOut,
        ];
    }

    /**
     * Set endpoint
     *
     * @param string $endpoint
     * @return self
     */
    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * Set product type
     *
     * @param string $productType
     * @return self
     */
    public function setProductType(string $productType): self
    {
        $this->productType = strtoupper($productType);
        return $this;
    }

    /**
     * Set headers
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Add single header
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Set timeout
     *
     * @param int $timeout
     * @return self
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Set retries
     *
     * @param int $retries
     * @return self
     */
    public function setRetries(int $retries): self
    {
        $this->retries = $retries;
        return $this;
    }
}
