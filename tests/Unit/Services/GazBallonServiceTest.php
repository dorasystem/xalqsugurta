<?php

namespace Tests\Unit\Services;

use App\Services\GazBallonService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GazBallonServiceTest extends TestCase
{
    private GazBallonService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GazBallonService();
    }

    // ─── fetchPerson ──────────────────────────────────────────────────────────

    public function test_fetch_person_returns_success_on_200(): void
    {
        $personData = [
            'currentPinfl'   => '12345678901234',
            'firstNameLatin' => 'JOHN',
            'lastNameLatin'  => 'DOE',
        ];

        Http::fake(['*' => Http::response($personData, 200)]);

        $result = $this->service->fetchPerson('12345678901234', 'AA1234567', '1990-01-01');

        $this->assertTrue($result['success']);
        $this->assertEquals($personData, $result['data']);
        $this->assertEquals(200, $result['status']);
    }

    public function test_fetch_person_returns_failure_on_http_error(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Unauthorized'], 401)]);

        $result = $this->service->fetchPerson('00000000000000', 'AA0000000', '2000-01-01');

        $this->assertFalse($result['success']);
        $this->assertEquals(401, $result['status']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_fetch_person_returns_failure_on_500(): void
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $result = $this->service->fetchPerson('00000000000000', 'AA0000001', '1985-05-15');

        $this->assertFalse($result['success']);
        $this->assertEquals(500, $result['status']);
    }

    // ─── fetchCadaster ────────────────────────────────────────────────────────

    public function test_fetch_cadaster_returns_success_with_property_data(): void
    {
        $cadasterData = [
            'cadasterNumber' => '12:34:5678901:1',
            'shortAddress'   => 'Chilonzor t.',
            'objectArea'     => '45.0',
        ];

        Http::fake(['*' => Http::response($cadasterData, 200)]);

        $result = $this->service->fetchCadaster('12:34:5678901:1');

        $this->assertTrue($result['success']);
        $this->assertEquals($cadasterData, $result['data']);
    }

    public function test_fetch_cadaster_returns_failure_on_not_found(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Not found'], 404)]);

        $result = $this->service->fetchCadaster('00:00:0000000:0');

        $this->assertFalse($result['success']);
        $this->assertEquals(404, $result['status']);
    }

    // ─── initiateTransaction ──────────────────────────────────────────────────

    public function test_initiate_transaction_returns_success_on_result_0(): void
    {
        $apiResponse = [
            'result'       => 0,
            'contract_id'  => 2932948,
            'polis_sery'   => 'XS',
            'polis_number' => '00123',
        ];

        Http::fake(['*' => Http::response($apiResponse, 200)]);

        $result = $this->service->initiateTransaction([
            'customer'  => [],
            'loan_info' => ['loan_type' => '35'],
            'subject'   => 'P',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(2932948, $result['data']['contract_id']);
        $this->assertEquals('XS', $result['data']['polis_sery']);
    }

    public function test_initiate_transaction_returns_success_on_result_302(): void
    {
        Http::fake(['*' => Http::response(['result' => 302, 'contract_id' => 111], 200)]);

        $result = $this->service->initiateTransaction(['loan_info' => ['loan_type' => '36']]);

        $this->assertTrue($result['success']);
        $this->assertEquals(302, $result['data']['result']);
    }

    public function test_initiate_transaction_returns_failure_on_http_error(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Bad request'], 400)]);

        $result = $this->service->initiateTransaction([]);

        $this->assertFalse($result['success']);
        $this->assertEquals(400, $result['status']);
    }

    public function test_initiate_transaction_returns_failure_on_server_error(): void
    {
        Http::fake(['*' => Http::response(null, 503)]);

        $result = $this->service->initiateTransaction([]);

        $this->assertFalse($result['success']);
        $this->assertEquals(503, $result['status']);
    }

    // ─── normalize (via public methods) ──────────────────────────────────────

    public function test_normalize_includes_data_on_failure_response(): void
    {
        $errorBody = ['message' => 'Invalid credentials', 'code' => 401];

        Http::fake(['*' => Http::response($errorBody, 401)]);

        $result = $this->service->fetchCadaster('any');

        $this->assertFalse($result['success']);
        $this->assertEquals($errorBody, $result['data']);
        $this->assertEquals('Invalid credentials', $result['message']);
    }

    public function test_normalize_uses_body_as_message_when_no_message_key(): void
    {
        Http::fake(['*' => Http::response('plain error text', 500)]);

        $result = $this->service->fetchCadaster('any');

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['message']);
    }
}
