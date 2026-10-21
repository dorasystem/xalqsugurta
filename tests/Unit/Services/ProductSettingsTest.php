<?php

namespace Tests\Unit\Services;

use App\Http\Controllers\Insurence\AccidentController;
use App\Http\Controllers\Insurence\GasBallonController;
use App\Services\ProductSettings;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductSettingsTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ─── merge ────────────────────────────────────────────────────────────────

    public function test_merge_without_settings_keeps_flow_values(): void
    {
        $flow = ProductSettings::merge(GasBallonController::FLOW, null);

        $this->assertSame(0.5, $flow['rate']);
        $this->assertSame('0,5', $flow['rateLabel']);
        $this->assertSame(5_000_000, $flow['min']);
        $this->assertSame(500_000_000, $flow['max']);
        $this->assertSame(50_000_000, $flow['default']);
        $this->assertSame(5_000_000, $flow['step']);
        $this->assertSame([10_000_000, 50_000_000, 100_000_000, 250_000_000], $flow['presets']);
        $this->assertSame(12, $flow['term_months']);
        $this->assertSame('gas', $flow['key']);
    }

    public function test_merge_applies_overrides_and_ignores_empty_fields(): void
    {
        $flow = ProductSettings::merge(GasBallonController::FLOW, [
            'rate'    => '0.75',
            'min'     => 10_000_000,
            'max'     => '',
            'presets' => ['20000000', '40000000'],
            'unknown' => 'x',
        ]);

        $this->assertSame(0.75, $flow['rate']);
        $this->assertSame('0,75', $flow['rateLabel']);
        $this->assertSame(10_000_000, $flow['min']);
        $this->assertSame(500_000_000, $flow['max']);
        $this->assertSame([20_000_000, 40_000_000], $flow['presets']);
        $this->assertArrayNotHasKey('unknown', $flow);
    }

    public function test_merge_falls_back_to_flow_when_settings_are_broken(): void
    {
        $flow = ProductSettings::merge(GasBallonController::FLOW, ['min' => 900_000_000]);

        $this->assertSame(5_000_000, $flow['min']);
        $this->assertSame(500_000_000, $flow['max']);
    }

    public function test_merge_keeps_default_and_presets_inside_the_range_and_on_the_step(): void
    {
        $flow = ProductSettings::merge(GasBallonController::FLOW, [
            'min'     => 20_000_000,
            'max'     => 200_000_000,
            'default' => 300_000_000,
            'step'    => 20_000_000,
            'presets' => [10_000_000, 50_000_000, 250_000_000],
        ]);

        $this->assertSame(200_000_000, $flow['default']);
        $this->assertSame([50_000_000], $flow['presets']);
        // 50 mln − 20 mln = 30 mln is not a multiple of 20 mln, so the step shrinks to 10 mln
        $this->assertSame(10_000_000, $flow['step']);
    }

    public function test_merge_computes_the_start_window(): void
    {
        Carbon::setTestNow('2026-09-28 10:00:00');

        $flow = ProductSettings::merge(GasBallonController::FLOW, ['start_offset' => 1, 'max_start_days' => 30]);

        $this->assertSame('2026-09-29', $flow['start_min']);
        $this->assertSame('2026-10-28', $flow['start_max']);
        $this->assertNull(ProductSettings::merge(GasBallonController::FLOW, null)['start_max']);
    }

    // ─── premium / dates ──────────────────────────────────────────────────────

    public function test_premium_uses_rate_and_minimum(): void
    {
        $flow = ProductSettings::merge(GasBallonController::FLOW, ['min_premium' => 100_000]);

        $this->assertSame(250_000, ProductSettings::premium($flow, 50_000_000));
        $this->assertSame(100_000, ProductSettings::premium($flow, 10_000_000));
    }

    public function test_end_date_follows_the_term(): void
    {
        $twelve = ProductSettings::merge(GasBallonController::FLOW, null);
        $six    = ProductSettings::merge(GasBallonController::FLOW, ['term_months' => 6]);

        $this->assertSame('2027-09-30', ProductSettings::endDate($twelve, '2026-10-01'));
        $this->assertSame('2027-03-31', ProductSettings::endDate($six, '2026-10-01'));
    }

    // ─── clean ────────────────────────────────────────────────────────────────

    public function test_clean_normalizes_input(): void
    {
        $clean = ProductSettings::clean('gas', [
            'rate'        => '0,6',
            'min'         => '10 000 000',
            'max'         => null,
            'presets'     => ['50000000', '20000000', '20000000'],
            'term_months' => '',
        ]);

        $this->assertSame(['rate' => 0.6, 'min' => 10_000_000, 'presets' => [20_000_000, 50_000_000]], $clean);
    }

    public function test_clean_drops_rate_fields_for_api_priced_products(): void
    {
        $clean = ProductSettings::clean('accident', ['rate' => '1', 'max' => '2000000']);

        $this->assertSame(['max' => 2_000_000], $clean);
        $this->assertArrayNotHasKey('rate', AccidentController::FLOW);
    }

    public function test_clean_returns_nothing_for_unsupported_products(): void
    {
        $this->assertSame([], ProductSettings::clean('osgor', ['rate' => '1']));
    }

    public function test_clean_rejects_a_step_that_misses_the_default(): void
    {
        $errors = $this->cleanErrors('gas', ['step' => '7000000']);

        $this->assertArrayHasKey('data.settings.step', $errors);
    }

    public function test_clean_rejects_min_above_max(): void
    {
        $errors = $this->cleanErrors('gas', ['min' => '600000000']);

        $this->assertArrayHasKey('data.settings.max', $errors);
    }

    public function test_clean_rejects_presets_outside_the_range(): void
    {
        $errors = $this->cleanErrors('property', ['presets' => ['1000000']]);

        $this->assertArrayHasKey('data.settings.presets', $errors);
    }

    public function test_clean_rejects_invalid_numbers(): void
    {
        $errors = $this->cleanErrors('kasko', ['rate' => '0', 'term_months' => '100']);

        $this->assertArrayHasKey('data.settings.rate', $errors);
        $this->assertArrayHasKey('data.settings.term_months', $errors);
    }

    // ─── display ──────────────────────────────────────────────────────────────

    public function test_display_formats_values_for_the_history(): void
    {
        $this->assertSame('0,5%', ProductSettings::display('rate', 0.5));
        $this->assertSame('10 mln, 2,5 mln', ProductSettings::display('presets', [10_000_000, 2_500_000]));
        $this->assertSame('12 oy', ProductSettings::display('term_months', 12));
        $this->assertSame('Ertadan', ProductSettings::display('start_offset', 1));
        $this->assertSame('standart', ProductSettings::display('min', null));
        $this->assertSame('O\'chirilgan', ProductSettings::display('is_active', false));
    }

    private function cleanErrors(string $route, array $input): array
    {
        try {
            ProductSettings::clean($route, $input, 'data.settings.');
        } catch (ValidationException $e) {
            return $e->errors();
        }

        $this->fail('Expected a ValidationException');
    }
}
