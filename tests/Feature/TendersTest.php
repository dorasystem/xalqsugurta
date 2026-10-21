<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\TenderResource;
use App\Models\Tender;
use App\Models\User;
use Database\Seeders\TenderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TendersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-21 10:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_seeder_publishes_both_tenders_in_three_languages_and_can_run_again(): void
    {
        $this->seed(TenderSeeder::class);
        $this->seed(TenderSeeder::class);

        $this->assertSame(2, Tender::count());
        foreach (Tender::all() as $tender) {
            foreach (['uz', 'ru', 'en'] as $l) {
                $this->assertNotEmpty($tender->{'title_' . $l});
                $this->assertStringContainsString('info@xalqsugurta.uz', $tender->{'body_' . $l});
            }
        }
    }

    public function test_list_splits_open_and_closed_tenders(): void
    {
        $this->seed(TenderSeeder::class);

        // On 21 October the audit tender (until 23.10) is open, the recourse one (until 20.10) is closed
        $this->get('/uz/tenders')->assertOk()
            ->assertSeeInOrder(['Takliflar qabul qilinmoqda', 'auditorlik tashkilotini', 'Yakunlangan tanlovlar', 'Regress tartibidagi'])
            ->assertSee(route('tenders.show', ['locale' => 'uz', 'slug' => 'audit-2026-financial-statements']), false);

        $this->get('/ru/tenders')->assertOk()->assertSee('Конкурс по выбору аудиторской организации');
        $this->get('/en/tenders')->assertOk()->assertSee('Tender to select an audit firm');
    }

    public function test_detail_page_shows_text_dates_and_email(): void
    {
        $this->seed(TenderSeeder::class);

        $this->get('/ru/tenders/audit-2026-financial-statements')->assertOk()
            ->assertSee('НСБУ')
            ->assertSee('23.10.2026')
            ->assertSee('mailto:info@xalqsugurta.uz', false)
            ->assertSee('Приём открыт');

        $this->get('/uz/tenders/recourse-receivables-card-collection')->assertOk()->assertSee('Qabul yakunlangan');
    }

    public function test_unpublished_future_and_unknown_tenders_are_hidden(): void
    {
        Tender::create(['slug' => 'draft', 'title_uz' => 'Qoralama tender', 'published_at' => '2026-10-01', 'is_published' => false]);
        Tender::create(['slug' => 'later', 'title_uz' => 'Keyingi tender', 'published_at' => '2026-11-01', 'is_published' => true]);

        $this->get('/uz/tenders')->assertOk()->assertDontSee('Qoralama tender')->assertDontSee('Keyingi tender')->assertSee('Hozircha ochiq tanlovlar yo');
        $this->get('/uz/tenders/draft')->assertNotFound();
        $this->get('/uz/tenders/later')->assertNotFound();
        $this->get('/uz/tenders/nope')->assertNotFound();
    }

    public function test_header_sitemap_and_search_link_to_tenders(): void
    {
        $this->seed(TenderSeeder::class);

        $this->get('/uz/tenders')->assertSee(route('tenders.index', ['locale' => 'uz']), false)->assertSee('Tenderlar');
        $this->get('/sitemap.xml')->assertOk()->assertSee('/uz/tenders/audit-2026-financial-statements', false);
        $this->get('/uz/search?q=tender')->assertOk()->assertSee(route('tenders.index', ['locale' => 'uz']), false);
    }

    public function test_admin_lists_tenders(): void
    {
        $this->seed(TenderSeeder::class);

        $this->actingAs(User::factory()->create())
            ->get(TenderResource::getUrl('index'))
            ->assertOk()
            ->assertSee('auditorlik tashkilotini');
        $this->get(TenderResource::getUrl('create'))->assertOk();
    }
}
