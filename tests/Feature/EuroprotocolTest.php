<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Services\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EuroprotocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_links_to_our_copy_of_the_form(): void
    {
        $this->assertFileExists(public_path('assets/docs/europrotocol.pdf'));
        $this->assertStringStartsWith('%PDF', file_get_contents(public_path('assets/docs/europrotocol.pdf'), false, null, 0, 4));

        $this->get('/uz')->assertOk()
            ->assertSee('href="' . SiteSettings::EUROPROTOCOL_DEFAULT . '"', false)
            ->assertDontSee('xalqsugurta.uz/uploads/files/xalq.pdf', false);
    }

    public function test_uploaded_form_replaces_the_shipped_one(): void
    {
        AppSetting::create(['key' => 'site.europrotocol', 'value' => 'documents/new.pdf']);
        Cache::flush();

        $this->assertSame('/storage/documents/new.pdf', SiteSettings::europrotocolUrl());
        $this->get('/ru')->assertOk()->assertSee('href="/storage/documents/new.pdf"', false);
    }
}
