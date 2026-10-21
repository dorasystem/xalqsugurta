<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Pages\SiteSettingsPage;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_icons_are_hidden_until_a_link_is_saved(): void
    {
        $html = $this->get('/uz')->assertOk()->getContent();
        $this->assertStringNotContainsString('social--fixed', $html);
        $this->assertStringNotContainsString('href="#" class="social', $html);

        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->get(SiteSettingsPage::getUrl())->assertOk();

        Livewire::test(SiteSettingsPage::class)
            ->fillForm(['site.social.telegram' => 'javascript:alert(1)'])
            ->call('save')
            ->assertHasFormErrors(['site.social.telegram']);

        Livewire::test(SiteSettingsPage::class)
            ->fillForm(['site.social.telegram' => 'https://t.me/example_channel'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saqlandi');

        $html = $this->get('/uz')->assertOk()->getContent();
        $this->assertStringContainsString('href="https://t.me/example_channel"', $html);
        $this->assertStringContainsString('#icon-telegram', $html);
        $this->assertStringNotContainsString('aria-label="Instagram"', $html);
    }
}
