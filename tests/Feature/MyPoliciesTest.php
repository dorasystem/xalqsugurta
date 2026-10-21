<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\PhoneVerification;
use App\Services\Sms\EskizClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MyPoliciesTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE = '998901234567';

    /** SMS texts Eskiz was asked to send */
    private array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.eskiz' => [
            'enabled' => true, 'base_url' => 'https://notify.eskiz.uz/api', 'email' => 'sms@example.test',
            'password' => 'secret', 'from' => '4546', 'template' => "Xalq Sug'urta: tasdiqlash kodi {code}",
        ]]);
        Cache::flush();

        Http::fake(function (Request $request) {
            if (str_ends_with($request->url(), '/auth/login')) {
                return Http::response(['message' => 'token_generated', 'data' => ['token' => 'tok-1']]);
            }
            if (str_ends_with($request->url(), '/message/sms/send')) {
                $this->sent[] = $request['message'];

                return Http::response(['id' => 'x', 'status' => 'waiting']);
            }

            return Http::response('unexpected', 500);
        });
    }

    private function order(array $attrs = []): Order
    {
        return Order::create(array_merge([
            'product_name' => 'Gaz ballon', 'insuranceProductName' => 'Gaz ballon sug\'urtasi', 'amount' => 250000,
            'insurance_id' => 'GAS-' . uniqid(), 'phone' => self::PHONE, 'status' => Order::STATUS_PAID,
            'insurances_response_data' => ['polis_sery' => 'XS', 'polis_number' => '0001', 'download_url' => 'https://insurer.example/p.pdf'],
        ], $attrs));
    }

    private function codeFromSms(): string
    {
        preg_match('/(\d{6})/', end($this->sent), $m);

        return $m[1];
    }

    private function signIn(): void
    {
        $this->post('/uz/my-policies/code', ['phone' => '+998 90 123 45 67'])->assertRedirect('/uz/my-policies');
        $this->post('/uz/my-policies/verify', ['code' => $this->codeFromSms()])->assertRedirect('/uz/my-policies');
    }

    public function test_full_sign_in_shows_only_this_phones_orders(): void
    {
        $mine  = $this->order();
        $other = $this->order(['phone' => '998977777777', 'insuranceProductName' => 'Begona polis']);

        $this->get('/uz/my-policies')->assertOk()->assertSee(__t('messages.my_policies.send_code'));

        $this->signIn();
        $this->assertSame("Xalq Sug'urta: tasdiqlash kodi " . $this->codeFromSms(), $this->sent[0]);

        $this->get('/uz/my-policies')
            ->assertOk()
            ->assertSee('№' . $mine->id)
            ->assertSee('XS 0001')
            ->assertSee('https://insurer.example/p.pdf', false)
            ->assertDontSee('Begona polis');

        // The verified phone opens the payment page details of its own orders only
        $this->get('/uz/payment/' . $mine->id)->assertSee('https://insurer.example/p.pdf', false);
        $this->assertFalse(app(OrderService::class)->canSeeDetails($other));

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/message/sms/send')
            && $r['mobile_phone'] === self::PHONE && $r['from'] === '4546' && $r->hasHeader('Authorization', 'Bearer tok-1'));
    }

    public function test_wrong_code_and_attempt_limit(): void
    {
        $this->post('/uz/my-policies/code', ['phone' => self::PHONE]);
        $right = $this->codeFromSms();
        $wrong = $right === '111111' ? '222222' : '111111';

        for ($i = 0; $i < PhoneVerification::MAX_ATTEMPTS; $i++) {
            $this->post('/uz/my-policies/verify', ['code' => $wrong])->assertSessionHasErrors('code');
        }

        // The code is dead after too many tries, even the right one
        $this->post('/uz/my-policies/verify', ['code' => $right])->assertSessionHasErrors('code');
        $this->assertNull(app(OrderService::class)->verifiedPhone());
    }

    public function test_resend_is_throttled_and_codes_expire(): void
    {
        $this->post('/uz/my-policies/code', ['phone' => self::PHONE]);
        $this->post('/uz/my-policies/code', ['phone' => self::PHONE])
            ->assertSessionHasErrors(['phone' => __t('messages.my_policies.error_too_soon')]);
        $this->assertCount(1, $this->sent);

        $code = $this->codeFromSms();
        $this->travel(PhoneVerification::CODE_TTL + 1)->seconds();
        $this->post('/uz/my-policies/verify', ['code' => $code])->assertSessionHasErrors('code');
    }

    public function test_session_expires_and_logout_works(): void
    {
        $this->order();
        $this->signIn();
        $this->get('/uz/my-policies')->assertSee(__t('messages.my_policies.sign_out'));

        $this->post('/uz/my-policies/logout')->assertRedirect('/uz/my-policies');
        $this->get('/uz/my-policies')->assertSee(__t('messages.my_policies.send_code'));

        $this->signIn();
        $this->travel(OrderService::PHONE_SESSION_MINUTES + 1)->minutes();
        $this->get('/uz/my-policies')->assertSee(__t('messages.my_policies.send_code'));
    }

    public function test_sms_off_or_failing_is_reported(): void
    {
        config(['services.eskiz.enabled' => false]);
        $this->get('/uz/my-policies')->assertSee(__t('messages.my_policies.error_unavailable'));
        $this->post('/uz/my-policies/code', ['phone' => self::PHONE])
            ->assertSessionHasErrors(['phone' => __t('messages.my_policies.error_unavailable')]);

        config(['services.eskiz.enabled' => true]);
        Http::swap(new \Illuminate\Http\Client\Factory($this->app['events']));
        Http::fake(['*/auth/login' => Http::response(['message' => 'Invalid credentials'], 401)]);

        $this->post('/uz/my-policies/code', ['phone' => self::PHONE])
            ->assertSessionHasErrors(['phone' => __t('messages.my_policies.error_unavailable')]);
    }

    public function test_expired_token_is_renewed_once(): void
    {
        Cache::put(EskizClient::TOKEN_CACHE_KEY, 'old-token');
        Http::swap(new \Illuminate\Http\Client\Factory($this->app['events']));
        Http::fake(function (Request $request) {
            if (str_ends_with($request->url(), '/auth/login')) {
                return Http::response(['data' => ['token' => 'new-token']]);
            }

            return $request->hasHeader('Authorization', 'Bearer old-token')
                ? Http::response(['message' => 'Expired'], 401)
                : Http::response(['status' => 'waiting']);
        });

        app(EskizClient::class)->send(self::PHONE, 'x');

        $this->assertSame('new-token', Cache::get(EskizClient::TOKEN_CACHE_KEY));
    }

    public function test_bad_phone_is_refused_before_any_sms(): void
    {
        $this->post('/uz/my-policies/code', ['phone' => '12345'])->assertSessionHasErrors('phone');
        $this->assertSame([], $this->sent);
    }
}
