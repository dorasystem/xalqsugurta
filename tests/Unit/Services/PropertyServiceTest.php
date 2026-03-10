<?php

namespace Tests\Unit\Services;

use App\Services\PropertyService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PropertyServiceTest extends TestCase
{
    private PropertyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PropertyService();
    }

    // ─── fetchPropertyByCadaster ──────────────────────────────────────────────

    public function test_fetch_property_by_cadaster_returns_success_on_valid_response(): void
    {
        $cadasterData = [
            'cadasterNumber' => '12:34:5678901:1',
            'shortAddress'   => 'Toshkent sh.',
            'objectArea'     => '65.5',
        ];

        Http::fake([
            '*' => Http::response(['error' => 0, 'result' => $cadasterData], 200),
        ]);

        $result = $this->service->fetchPropertyByCadaster('12:34:5678901:1');

        $this->assertTrue($result['success']);
        $this->assertEquals($cadasterData, $result['result']);
    }

    public function test_fetch_property_by_cadaster_returns_failure_on_http_error(): void
    {
        Http::fake([
            '*' => Http::response(['error_message' => 'Server error'], 500),
        ]);

        $result = $this->service->fetchPropertyByCadaster('invalid');

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_fetch_property_by_cadaster_returns_failure_on_business_error(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 1, 'error_message' => 'Kadastr topilmadi'], 200),
        ]);

        $result = $this->service->fetchPropertyByCadaster('00:00:000:0');

        $this->assertFalse($result['success']);
        $this->assertEquals('Kadastr topilmadi', $result['error']);
    }

    public function test_fetch_property_by_cadaster_returns_failure_on_exception(): void
    {
        Http::fake(function () {
            throw new \Exception('Connection refused');
        });

        $result = $this->service->fetchPropertyByCadaster('12:34');

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Connection refused', $result['message']);
    }

    public function test_fetch_property_by_cadaster_handles_null_result_gracefully(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 0], 200),
        ]);

        $result = $this->service->fetchPropertyByCadaster('12:34:5678901:2');

        $this->assertTrue($result['success']);
        $this->assertNull($result['result']);
    }

    // ─── sendPropertyInsuranceRequest ────────────────────────────────────────

    public function test_send_property_insurance_request_returns_success(): void
    {
        $responseData = ['polis_sery' => 'XS', 'polis_number' => '12345'];

        Http::fake([
            '*' => Http::response($responseData, 200),
        ]);

        $result = $this->service->sendPropertyInsuranceRequest(['customer' => [], 'loan_info' => []]);

        $this->assertTrue($result['success']);
        $this->assertEquals($responseData, $result['data']);
    }

    public function test_send_property_insurance_request_returns_failure_on_http_error(): void
    {
        Http::fake([
            '*' => Http::response(['error_message' => 'Validation error'], 422),
        ]);

        $result = $this->service->sendPropertyInsuranceRequest([]);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_send_property_insurance_request_returns_failure_on_exception(): void
    {
        Http::fake(function () {
            throw new \Exception('Timeout');
        });

        $result = $this->service->sendPropertyInsuranceRequest([]);

        $this->assertFalse($result['success']);
        $this->assertEquals('Timeout', $result['message']);
    }
}
