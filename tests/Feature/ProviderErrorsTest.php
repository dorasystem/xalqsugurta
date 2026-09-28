<?php

namespace Tests\Feature;

use App\Exceptions\ProviderException;
use App\Services\Provider\ProviderApiTrait;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** How ProviderApiTrait turns the insurer's failures into ProviderException codes */
class ProviderErrorsTest extends TestCase
{
    private object $api;

    protected function setUp(): void
    {
        parent::setUp();

        config(['provider.base_url' => 'http://online.xalqsugurta.uz/xs/ins/osago/proxy']);

        $this->api = new class {
            use ProviderApiTrait;

            public function post(string $url): array
            {
                return $this->insurerPost($url, ['a' => 1])->json();
            }
        };
    }

    private function exceptionFrom(callable $call): ProviderException
    {
        try {
            $call();
        } catch (ProviderException $e) {
            return $e;
        }

        $this->fail('No ProviderException');
    }

    public function test_registry_business_error_503_is_an_outage(): void
    {
        Http::fake(['*' => Http::response(['error' => 503, 'error_message' => 'Provider error'])]);

        $e = $this->exceptionFrom(fn () => $this->api->findPersonByPinfl('11111111111111', 'AA0000000'));
        $this->assertTrue($e->isUnavailable());
    }

    public function test_registry_not_found_is_not_an_outage(): void
    {
        Http::fake(['*' => Http::response(['error' => 1, 'error_message' => 'Not found'])]);

        $e = $this->exceptionFrom(fn () => $this->api->findPersonByPinfl('11111111111111', 'AA0000000'));
        $this->assertFalse($e->isUnavailable());
        $this->assertSame('Not found', $e->getMessage());
    }

    public function test_registry_unreachable_is_an_outage_not_a_crash(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $this->assertTrue($this->exceptionFrom(fn () => $this->api->findPersonByPinfl('11111111111111', 'AA0000000'))->isUnavailable());

        Http::fake(['*' => Http::response('', 500)]);
        $this->assertTrue($this->exceptionFrom(fn () => $this->api->findVehicle('AAF', '1234567', '01A123BC'))->isUnavailable());
    }

    public function test_insurer_post_codes(): void
    {
        Http::fake([
            'http://insurer.test/down'     => Http::failedConnection(),
            'http://insurer.test/broken'   => Http::response('oops', 503),
            'http://insurer.test/rejected' => Http::response(['result' => 1, 'result_message' => 'Sana noto\'g\'ri'], 422),
            'http://insurer.test/ok'       => Http::response(['result' => 0]),
        ]);

        $this->assertTrue($this->exceptionFrom(fn () => $this->api->post('http://insurer.test/down'))->isUnavailable());
        $this->assertTrue($this->exceptionFrom(fn () => $this->api->post('http://insurer.test/broken'))->isUnavailable());

        $rejected = $this->exceptionFrom(fn () => $this->api->post('http://insurer.test/rejected'));
        $this->assertFalse($rejected->isUnavailable());
        $this->assertSame('Sana noto\'g\'ri', $rejected->getMessage());

        $this->assertSame(['result' => 0], $this->api->post('http://insurer.test/ok'));
    }

    public function test_insurer_bodies_are_plain_utf8_like_the_postman_samples(): void
    {
        Http::fake(['*' => Http::response(['result' => 0])]);

        $api = new class {
            use ProviderApiTrait;

            public function send(array $body): void
            {
                $this->insurerPost('http://insurer.test/eshop/osgop', $body);
            }
        };
        $api->send(['representativeName' => 'ALIYEV VALI O‘G‘LI', 'position' => 'Директор', 'address' => 'Ko’cha 1/2', 'nested' => ['x' => 'Gʻ`ʼ', 'n' => 5]]);

        Http::assertSent(function ($request) {
            $raw = $request->body();

            return str_contains($raw, '"ALIYEV VALI O\'G\'LI"')
                && str_contains($raw, '"Ko\'cha 1/2"')
                && str_contains($raw, '"x":"G\'\'\'"')
                && str_contains($raw, '"n":5')
                && str_contains($raw, '"Директор"')
                && !str_contains($raw, '\\u')
                && $request->hasHeader('Content-Type', 'application/json')
                && $request['position'] === 'Директор';
        });
    }
}
