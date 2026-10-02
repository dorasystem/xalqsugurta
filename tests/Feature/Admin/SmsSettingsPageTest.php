<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Pages\SmsSettingsPage;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\SmsSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SmsSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.eskiz.enabled' => false, 'services.eskiz.email' => null, 'services.eskiz.password' => null]);
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_saving_enables_sms_and_encrypts_the_password(): void
    {
        $this->get(SmsSettingsPage::getUrl())->assertOk()->assertSee('Eskiz');

        Livewire::test(SmsSettingsPage::class)
            ->fillForm([
                'eskiz.enabled'  => true,
                'eskiz.email'    => 'sms@example.test',
                'eskiz.password' => 'eskiz-secret',
                'eskiz.from'     => '4546',
                'eskiz.template' => 'Kod: {code}',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saqlandi');

        $this->assertTrue(SmsSettings::ready());
        $this->assertSame('eskiz-secret', config('services.eskiz.password'));
        $this->assertStringNotContainsString('eskiz-secret', AppSetting::find('eskiz.password')->value);

        Livewire::test(SmsSettingsPage::class)->assertFormSet(['eskiz.password' => null])->assertDontSee('eskiz-secret');
    }

    public function test_template_must_contain_the_code(): void
    {
        Livewire::test(SmsSettingsPage::class)
            ->fillForm(['eskiz.template' => 'Kod yo\'q'])
            ->call('save')
            ->assertHasFormErrors(['eskiz.template']);
    }

    public function test_test_sms_uses_the_template(): void
    {
        config(['services.eskiz' => ['enabled' => true, 'base_url' => 'https://notify.eskiz.uz/api', 'email' => 'a@b.c',
            'password' => 'p', 'from' => '4546', 'template' => 'Kod: {code}']]);
        Http::fake([
            '*/auth/login'       => Http::response(['data' => ['token' => 't']]),
            '*/message/sms/send' => Http::response(['status' => 'waiting']),
        ]);

        Livewire::test(SmsSettingsPage::class)
            ->callAction('test', ['phone' => '998901234567'])
            ->assertNotified('SMS yuborildi');

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/message/sms/send') && $r['message'] === 'Kod: 123456');
    }
}
