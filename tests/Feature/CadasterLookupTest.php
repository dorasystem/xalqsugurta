<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CadasterLookupTest extends TestCase
{
    use RefreshDatabase;

    public static function numbers(): array
    {
        return [
            'colon blocks' => ['11:11:11:11:11:1111:2222:333', true],
            'slash block'  => ['11:11:11:11:11:1111/2222', true],
            'land only'    => ['11:11:11:11:11:1111', true],
            'no colons'    => ['111111111111112222333', false],
            'short head'   => ['11:11:11:11:1111', false],
            'letters'      => ['11:11:11:11:11:11AB', false],
            'dots'         => ['11.11.11.11.11.1111', false],
        ];
    }

    #[DataProvider('numbers')]
    public function test_the_server_checks_the_format(string $number, bool $valid): void
    {
        Http::fake(['*' => Http::response(['error' => 0, 'result' => ['cadasterNumber' => $number]])]);

        $response = $this->postJson('/uz/fetch-cadaster', ['cadasterNumber' => $number]);

        if ($valid) {
            $response->assertOk()->assertJsonPath('success', true);
        } else {
            $response->assertStatus(422)->assertJsonPath('message', __t('messages.flow.cadaster_format_error'));
            Http::assertNothingSent();
        }
    }

    public function test_not_found_and_unavailable_have_their_own_messages(): void
    {
        Http::fakeSequence()
            ->push(['error' => 1, 'error_message' => 'Not found'])
            ->push('Server error', 500);

        $this->postJson('/uz/fetch-cadaster', ['cadasterNumber' => '11:11:11:11:11:1111'])
            ->assertStatus(422)
            ->assertJsonPath('message', __t('messages.flow.cadaster_not_found'));

        $this->postJson('/uz/fetch-cadaster-gas', ['cadasterNumber' => '11:11:11:11:11:1111'])
            ->assertStatus(422)
            ->assertJsonPath('message', __t('messages.flow.cadaster_unavailable'));
    }

    public function test_step_two_rejects_a_malformed_number(): void
    {
        $this->withSession(['property.applicant' => ['lastname' => 'ALIYEV', 'firstname' => 'VALI']])
            ->post('/uz/property/property', [
                'cadaster_number'    => '12345',
                'insurance_amount'   => 100_000_000,
                'payment_start_date' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors(['cadaster_number' => __t('messages.flow.cadaster_format_error')]);
    }

    public function test_the_page_has_the_help_and_the_mask(): void
    {
        $this->withSession(['gas.applicant' => ['lastname' => 'ALIYEV', 'firstname' => 'VALI']])
            ->get('/uz/gas/property')
            ->assertOk()
            ->assertSee(__t('messages.flow.cadaster_where'))
            ->assertSee('function formatCadaster', false);
    }
}
