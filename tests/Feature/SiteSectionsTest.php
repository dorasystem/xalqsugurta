<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\JobApplicationResource;
use App\Filament\Admin\Resources\VacancyResource;
use App\Models\CallbackRequest;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\Vacancy;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SiteSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-29 10:00');
        $this->seed(ProductSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function vacancy(array $attributes = []): Vacancy
    {
        return Vacancy::create($attributes + [
            'slug'         => 'underwriter',
            'title_uz'     => 'Anderrayter',
            'title_ru'     => 'Андеррайтер',
            'body_uz'      => '<p>Talablar</p><script>alert(1)</script>',
            'location'     => 'Toshkent',
            'employment'   => 'full',
            'published_at' => '2026-10-20',
            'deadline_at'  => '2026-10-31',
            'is_published' => true,
        ]);
    }

    // ─── Header: section switch and menus ──────────────────────────────────

    public function test_header_shows_sections_and_the_individuals_menu_on_the_home_page(): void
    {
        $this->get('/uz')->assertOk()
            ->assertSee('site-sections', false)
            ->assertSeeInOrder(['Jismoniy shaxslar', 'Yuridik shaxslar', 'Karyera'])
            // our KASKO / property pages while on sale, the old site for products we don't sell
            ->assertSee('«Mening uyim» mulkini sug‘urtalash')
            ->assertSee('https://xalqsugurta.uz/strahovanie-moyasemya', false)
            ->assertSee('Sug’urta atamasi')
            ->assertDontSee('Dividendlar');
    }

    public function test_business_pages_show_the_business_menu(): void
    {
        $this->get('/ru/business')->assertOk()
            ->assertSee('Страхование имущества от всех рисков')
            ->assertSee('Дивиденды')
            ->assertSee('Тендеры');

        // OSGOR's application belongs to the business section too
        $this->get('/uz/osgor')->assertOk()->assertSee('Dividendlar')->assertSee('IFJMS');
    }

    public function test_career_menu_is_short(): void
    {
        $this->get('/uz/career')->assertOk()
            ->assertSee('Kompaniyasining tuzilishi')
            ->assertDontSee('Dividendlar')
            ->assertDontSee('IFJMS');
    }

    // ─── Business page ─────────────────────────────────────────────────────

    public function test_business_page_lists_online_products_and_documents(): void
    {
        $this->get('/uz/business')->assertOk()
            ->assertSee('href="' . route('osgor.index', ['locale' => 'uz'], false) . '"', false)
            ->assertSee(route('tenders.index', ['locale' => 'uz']), false)
            ->assertSee('Taklif so‘rash');
    }

    public function test_offer_request_is_stored_for_staff(): void
    {
        $this->post('/uz/business/request', [
            'company' => 'Test MChJ',
            'inn'     => '123456789',
            'product' => 'all_risks',
            'name'    => 'Ali Valiyev',
            'phone'   => '90 123 45 67',
        ])->assertRedirect(route('business', ['locale' => 'uz']) . '#offer');

        $request = CallbackRequest::sole();
        $this->assertSame(CallbackRequest::TOPIC_CORPORATE, $request->topic);
        $this->assertSame('998901234567', $request->phone);
        $this->assertStringContainsString('Test MChJ', $request->message);
        $this->assertStringContainsString('Mulkni barcha xavflardan', $request->message);
    }

    public function test_offer_request_validates_and_ignores_bots(): void
    {
        $this->post('/uz/business/request', ['product' => 'nope', 'phone' => '1'])
            ->assertSessionHasErrors(['company', 'product', 'name', 'phone']);

        $this->post('/uz/business/request', ['website' => 'x', 'company' => 'Bot'])->assertRedirect();
        $this->assertSame(0, CallbackRequest::count());
    }

    // ─── Career ────────────────────────────────────────────────────────────

    public function test_career_lists_open_vacancies_only(): void
    {
        $this->vacancy();
        $this->vacancy(['slug' => 'closed', 'title_uz' => 'Yopilgan', 'deadline_at' => '2026-10-28']);
        $this->vacancy(['slug' => 'later', 'title_uz' => 'Keyinroq', 'published_at' => '2026-11-05']);
        $this->vacancy(['slug' => 'draft', 'title_uz' => 'Qoralama', 'is_published' => false]);

        $this->get('/uz/career')->assertOk()
            ->assertSee('Anderrayter')
            ->assertSee('Toshkent · To‘liq stavka · 31.10.2026 gacha')
            ->assertDontSee('Yopilgan')->assertDontSee('Keyinroq')->assertDontSee('Qoralama');

        $this->get('/uz/career/closed')->assertNotFound();
        $this->get('/ru/career/underwriter')->assertOk()->assertSee('Андеррайтер')->assertSee('Talablar')->assertDontSee('alert(1)', false);
    }

    public function test_empty_career_page_still_takes_a_cv(): void
    {
        $this->get('/en/career')->assertOk()->assertSee('There are no open vacancies')->assertSee('Send your CV');
    }

    public function test_cv_is_stored_privately_for_the_vacancy(): void
    {
        Storage::fake('local');
        $vacancy = $this->vacancy();

        $this->post('/uz/career/apply', [
            'vacancy_id' => $vacancy->id,
            'name'       => 'Ali Valiyev',
            'phone'      => '+998 90 123 45 67',
            'resume'     => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
        ])->assertRedirect(route('career.show', ['locale' => 'uz', 'slug' => 'underwriter']) . '#apply');

        $application = JobApplication::sole();
        $this->assertSame($vacancy->id, $application->vacancy_id);
        $this->assertSame('998901234567', $application->phone);
        $this->assertSame('cv.pdf', $application->file_name);
        Storage::disk('local')->assertExists($application->file_path);
        $this->assertStringStartsWith('resumes/' . $application->id . '/', $application->file_path);
    }

    public function test_cv_for_a_closed_vacancy_becomes_a_general_cv(): void
    {
        Storage::fake('local');
        $closed = $this->vacancy(['deadline_at' => '2026-10-01']);

        $this->post('/uz/career/apply', [
            'vacancy_id' => $closed->id,
            'name'       => 'Ali',
            'phone'      => '998901234567',
            'resume'     => UploadedFile::fake()->create('cv.docx', 10),
        ])->assertRedirect(route('career.index', ['locale' => 'uz']) . '#apply');

        $this->assertNull(JobApplication::sole()->vacancy_id);
    }

    public function test_cv_file_type_and_size_are_checked(): void
    {
        Storage::fake('local');

        $this->post('/uz/career/apply', [
            'name'   => 'Ali',
            'phone'  => '998901234567',
            'resume' => UploadedFile::fake()->create('cv.exe', 10),
        ])->assertSessionHasErrors('resume');

        $this->post('/uz/career/apply', [
            'name'   => 'Ali',
            'phone'  => '998901234567',
            'resume' => UploadedFile::fake()->create('cv.pdf', 6000, 'application/pdf'),
        ])->assertSessionHasErrors('resume');

        $this->assertSame(0, JobApplication::count());
    }

    public function test_cv_file_needs_a_signed_link_and_an_admin(): void
    {
        Storage::fake('local');
        $this->post('/uz/career/apply', [
            'name'   => 'Ali',
            'phone'  => '998901234567',
            'resume' => UploadedFile::fake()->create('cv.pdf', 20, 'application/pdf'),
        ]);
        $application = JobApplication::sole();
        $signed = URL::temporarySignedRoute('career.resume', now()->addMinutes(30), ['application' => $application->id]);

        $this->get(route('career.resume', ['application' => $application->id]))->assertForbidden();
        $this->get($signed)->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->get($signed)->assertOk()->assertDownload('cv.pdf');
    }

    public function test_admin_pages_open(): void
    {
        $this->vacancy();
        $this->actingAs(User::factory()->create());

        $this->get(VacancyResource::getUrl('index'))->assertOk()->assertSee('Anderrayter');
        $this->get(VacancyResource::getUrl('create'))->assertOk();
        $this->get(JobApplicationResource::getUrl('index'))->assertOk();
    }

    public function test_sitemap_lists_the_new_sections(): void
    {
        $this->vacancy();

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee(route('business', ['locale' => 'uz']), false)
            ->assertSee(route('career.index', ['locale' => 'ru']), false)
            ->assertSee(route('career.show', ['locale' => 'en', 'slug' => 'underwriter']), false);
    }
}
