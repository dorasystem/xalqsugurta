<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name_uz' => 'Gaz ballon', 'name_ru' => 'Газ баллон', 'name_en' => 'Gas balloon',
            'route'   => 'gas',
            'is_active' => true,
        ], $attributes));
    }

    private function withApplicant(): static
    {
        return $this->withSession(['gas.applicant' => [
            'lastname' => 'ALIYEV', 'firstname' => 'VALI', 'middlename' => '',
            'pinfl' => '31501991234567', 'passport_seria' => 'AB', 'passport_number' => '1234567',
            'birth_date' => '1999-01-15', 'gender' => '1', 'address' => 'Toshkent', 'phone' => '998901234567',
        ]]);
    }

    // ─── History ──────────────────────────────────────────────────────────────

    public function test_changing_settings_writes_history_rows(): void
    {
        $user    = User::factory()->create();
        $product = $this->product(['settings' => ['rate' => 0.4]]);

        $this->actingAs($user);
        $product->update(['settings' => ['rate' => 0.5, 'max' => 300_000_000], 'is_active' => false]);

        $rows = $product->settingChanges()->get()->keyBy('field');

        $this->assertCount(3, $rows);
        $this->assertSame('0,4%', $rows['rate']->old_value);
        $this->assertSame('0,5%', $rows['rate']->new_value);
        $this->assertSame('standart', $rows['max']->old_value);
        $this->assertSame('O\'chirilgan', $rows['is_active']->new_value);
        $this->assertSame($user->id, $rows['rate']->user_id);
    }

    public function test_unrelated_updates_write_no_history(): void
    {
        $product = $this->product();

        $product->update(['sort_order' => 5, 'name_uz' => 'Gaz ballon sug\'urtasi']);

        $this->assertSame(0, $product->settingChanges()->count());
    }

    // ─── Site ─────────────────────────────────────────────────────────────────

    public function test_product_off_sale_redirects_to_home(): void
    {
        $this->product(['is_active' => false]);

        $this->get('/uz/gas')->assertRedirect(route('home', ['locale' => 'uz']));
    }

    public function test_product_without_a_row_stays_open(): void
    {
        $this->get('/uz/gas')->assertOk();
    }

    public function test_step_two_uses_the_admin_limits_and_rate(): void
    {
        $this->product(['settings' => ['rate' => 1, 'min' => 20_000_000]]);

        $this->withApplicant()
            ->post('/uz/gas/property', [
                'cadaster_number'    => '10:01:01:01:01:0001',
                'insurance_amount'   => 10_000_000,
                'payment_start_date' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('insurance_amount');

        $this->withApplicant()
            ->post('/uz/gas/property', [
                'cadaster_number'    => '10:01:01:01:01:0001',
                'insurance_amount'   => 50_000_000,
                'payment_start_date' => now()->format('Y-m-d'),
            ])
            ->assertRedirect(route('gas.getConfirm', ['locale' => 'uz']));

        $this->assertSame(500_000, session('gas.calculation.insurance_premium'));
    }

    public function test_step_pages_render_the_admin_values(): void
    {
        $this->product(['settings' => [
            'rate' => 0.8, 'min_premium' => 120_000, 'term_months' => 6, 'max_start_days' => 30, 'presets' => [15_000_000],
        ]]);

        $this->withApplicant()->get('/uz/gas/property')
            ->assertOk()
            ->assertSee('var RATE        = 0.008;', false)
            ->assertSee('var MIN_PREMIUM = 120000;', false)
            ->assertSee('var TERM_MONTHS = 6;', false)
            ->assertSee('data-amount="15000000"', false)
            ->assertSee('max="' . now()->addDays(30)->format('Y-m-d') . '"', false);
    }

    public function test_kasko_and_accident_pages_render_with_default_settings(): void
    {
        $applicant = ['lastname' => 'ALIYEV', 'firstname' => 'VALI', 'middlename' => '', 'pinfl' => '31501991234567',
            'passport_seria' => 'AB', 'passport_number' => '1234567', 'phone' => '998901234567'];

        $this->withSession(['kasko.applicant' => $applicant])->get('/uz/kasko/vehicle')->assertOk();
        $this->withSession(['accident.applicant' => $applicant])->get('/uz/accident/persons')->assertOk();
        $this->withSession([
            'accident.applicant' => $applicant,
            'accident.persons'   => [$applicant + ['sum_insured' => 500_000, 'insurance_premium' => 1_500]],
        ])->get('/uz/accident/calculator')
            ->assertOk()
            ->assertSee('var TERM_MONTHS = 12;', false);
    }

    public function test_term_and_start_window_come_from_the_settings(): void
    {
        $this->product(['settings' => ['term_months' => 6, 'start_offset' => 1]]);

        $this->withApplicant()
            ->post('/uz/gas/property', [
                'cadaster_number'    => '10:01:01:01:01:0001',
                'insurance_amount'   => 50_000_000,
                'payment_start_date' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('payment_start_date');

        $start = now()->addDay();

        $this->withApplicant()
            ->post('/uz/gas/property', [
                'cadaster_number'    => '10:01:01:01:01:0001',
                'insurance_amount'   => 50_000_000,
                'payment_start_date' => $start->format('Y-m-d'),
            ])
            ->assertRedirect(route('gas.getConfirm', ['locale' => 'uz']));

        $this->assertSame(
            $start->copy()->addMonths(6)->subDay()->format('Y-m-d'),
            session('gas.calculation.payment_end_date'),
        );
    }
}
