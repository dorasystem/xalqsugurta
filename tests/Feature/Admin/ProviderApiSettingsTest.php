<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Pages\ProviderApiSettings;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\ProviderSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProviderApiSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['provider.agency_id' => '546']);
        ProviderSettings::apply();
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_page_shows_the_env_value_as_fallback(): void
    {
        $this->get(ProviderApiSettings::getUrl())->assertOk()->assertSee('.env: 546');
    }

    public function test_saved_agency_overrides_env_and_clearing_restores_it(): void
    {
        Livewire::test(ProviderApiSettings::class)
            ->fillForm(['provider.agency_id' => '777'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saqlandi');

        $this->assertSame('777', config('provider.agency_id'));

        Livewire::test(ProviderApiSettings::class)
            ->assertFormSet(['provider.agency_id' => '777'])
            ->fillForm(['provider.agency_id' => ''])
            ->call('save');

        $this->assertSame('546', config('provider.agency_id'));
    }

    public function test_osago_legal_entities_switch(): void
    {
        $this->assertFalse(config('provider.osago.legal_entities'));

        Livewire::test(ProviderApiSettings::class)
            ->assertFormSet(['provider.osago_legal_entities' => false])
            ->fillForm(['provider.osago_legal_entities' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(config('provider.osago.legal_entities'));
        $this->assertSame('1', AppSetting::where('key', 'provider.osago_legal_entities')->value('value'));

        Livewire::test(ProviderApiSettings::class)
            ->assertFormSet(['provider.osago_legal_entities' => true])
            ->fillForm(['provider.osago_legal_entities' => false])
            ->call('save');

        // Saved as '0': "off" wins even over an .env "on"
        $this->assertSame('0', AppSetting::where('key', 'provider.osago_legal_entities')->value('value'));
        config(['provider.osago.legal_entities' => true]);
        ProviderSettings::apply();
        $this->assertFalse(config('provider.osago.legal_entities'));
    }
}
