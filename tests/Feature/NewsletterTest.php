<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\NewsletterSubscriberResource;
use App\Filament\Admin\Resources\NewsletterSubscriberResource\Pages\ListNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_form_stores_the_email_once(): void
    {
        $this->get('/ru')->assertOk()->assertSee('action="' . route('newsletter.store', ['locale' => 'ru']) . '"', false);

        $this->from('/ru')->post('/ru/subscribe', ['email' => ' Test@Example.com '])
            ->assertRedirect('/ru')
            ->assertSessionHas('success');
        $this->from('/uz')->post('/uz/subscribe', ['email' => 'test@example.com'])->assertRedirect('/uz');

        $row = NewsletterSubscriber::sole();
        $this->assertSame('test@example.com', $row->email);
        $this->assertSame('ru', $row->locale);
    }

    public function test_bad_email_and_bots_are_refused(): void
    {
        $this->from('/uz')->post('/uz/subscribe', ['email' => 'not-an-email'])
            ->assertRedirect('/uz')
            ->assertSessionHasErrorsIn('newsletter', 'email');

        $this->from('/uz')->post('/uz/subscribe', ['email' => 'bot@example.com', 'website' => 'x'])->assertRedirect('/uz');

        $this->assertSame(0, NewsletterSubscriber::count());
    }

    public function test_admin_lists_and_exports_subscribers(): void
    {
        NewsletterSubscriber::create(['email' => 'one@example.com', 'locale' => 'uz']);
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->get(NewsletterSubscriberResource::getUrl('index'))->assertOk()->assertSee('one@example.com');

        Livewire::test(ListNewsletterSubscribers::class)->callAction('export')->assertFileDownloaded('obunachilar-' . now()->format('Y-m-d') . '.csv');

        $csv = NewsletterSubscriberResource::csv();
        ob_start();
        $csv->sendContent();
        $this->assertStringContainsString('one@example.com;uz;', ob_get_clean());
    }
}
