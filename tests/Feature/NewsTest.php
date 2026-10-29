<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\NewsResource;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-30 10:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function news(array $attributes = []): News
    {
        return News::create($attributes + [
            'slug'         => 'tanlov',
            'title_uz'     => 'Tanlov e’lon qilamiz',
            'title_ru'     => 'Объявляется конкурс',
            'body_uz'      => '<p>Takliflarni info@xalqsugurta.uz ga yuboring.</p><script>alert(1)</script>',
            'published_at' => '2026-10-01 09:00',
            'is_published' => true,
        ]);
    }

    public function test_list_shows_published_news_newest_first(): void
    {
        $this->news();
        $this->news(['slug' => 'newer', 'title_uz' => 'Yangiroq xabar', 'published_at' => '2026-10-20']);
        $this->news(['slug' => 'draft', 'title_uz' => 'Qoralama', 'is_published' => false]);
        $this->news(['slug' => 'later', 'title_uz' => 'Kelajakdagi', 'published_at' => '2026-11-05']);

        $this->get('/uz/news')->assertOk()
            ->assertSeeInOrder(['Yangiroq xabar', 'Tanlov e’lon qilamiz'])
            ->assertSee('Takliflarni info@xalqsugurta.uz ga yuboring.')
            ->assertDontSee('Qoralama')->assertDontSee('Kelajakdagi');

        $this->get('/uz/news/draft')->assertNotFound();
        $this->get('/uz/news/later')->assertNotFound();
    }

    public function test_detail_page_falls_back_to_uzbek_and_cleans_html(): void
    {
        $this->news();

        $this->get('/ru/news/tanlov')->assertOk()
            ->assertSee('Объявляется конкурс')
            ->assertSee('Takliflarni')
            ->assertDontSee('alert(1)', false);
    }

    public function test_empty_list(): void
    {
        $this->get('/en/news')->assertOk()->assertSee('No news yet.');
    }

    public function test_press_centre_links_point_to_our_page(): void
    {
        $this->get('/uz')->assertOk()
            ->assertSee(route('news.index', ['locale' => 'uz']), false)
            ->assertDontSee('https://xalqsugurta.uz/uz/news', false);
    }

    public function test_search_sitemap_and_admin(): void
    {
        $this->news();

        $this->get('/uz/search?q=tanlov')->assertOk()->assertSee(route('news.show', ['locale' => 'uz', 'slug' => 'tanlov']), false);
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('news.show', ['locale' => 'ru', 'slug' => 'tanlov']), false);

        $this->actingAs(User::factory()->create());
        $this->get(NewsResource::getUrl('index'))->assertOk()->assertSee('Tanlov e’lon qilamiz');
        $this->get(NewsResource::getUrl('create'))->assertOk();
    }

    public function test_import_matches_languages_by_position_and_skips_repeats_and_empty_pages(): void
    {
        Storage::fake('public');

        $item = fn (string $link, string $title, string $date, string $img = '') => compact('link', 'title', 'date', 'img') + ['id' => '', 'year' => substr($date, -4)];
        $page = fn (string $text) => '<h1 class="banner-light__title">x</h1><div class="template-content"><div class="template__text"><p>' . $text . '</p></div></div>';

        Http::fake([
            'xalqsugurta.uz/0/news/json' => Http::response([
                $item('/news/obuyavlyaetsya-konkurs', 'Объявляется конкурс', '27.03.2026', '/uploads/images/news/__thumbs/p.jpg/p__384x480.jpg'),
                $item('/news/', '', '14.09.2021'),
            ]),
            'xalqsugurta.uz/1/news/json' => Http::response([
                $item('/news/tanlov-elon-qilamiz', 'Tanlov e’lon qilamiz', '27.03.2026'),
                $item('/news/empty-page', 'Bo‘sh', '14.09.2021'),
                $item('/news/empty-page', 'Bo‘sh', '14.09.2021'),
            ]),
            'xalqsugurta.uz/2/news/json' => Http::response([
                $item('/news/Tender is announced', 'A tender is announced', '27.03.2026'),
            ]),
            'xalqsugurta.uz/news/obuyavlyaetsya-konkurs' => Http::response($page('Текст конкурса')),
            'xalqsugurta.uz/news/tanlov-elon-qilamiz'    => Http::response($page('Tanlov matni')),
            'xalqsugurta.uz/news/Tender%20is%20announced' => Http::response($page('Tender text')),
            'xalqsugurta.uz/news/empty-page'             => Http::response('<div class="template-content"><div class="template__text"></div></div>'),
            'xalqsugurta.uz/uploads/images/news/p.jpg'   => Http::response('jpeg', 200, ['Content-Type' => 'image/jpeg']),
            '*'                                          => Http::response('', 404),
        ]);

        $this->artisan('site:import-old-news')->assertSuccessful();

        $news = News::sole();
        $this->assertSame('tanlov-elon-qilamiz', $news->slug);
        $this->assertSame('Tanlov e’lon qilamiz', $news->title_uz);
        $this->assertSame('Объявляется конкурс', $news->title_ru);
        $this->assertSame('A tender is announced', $news->title_en);
        $this->assertStringContainsString('Текст конкурса', $news->body_ru);
        $this->assertStringContainsString('Tender text', $news->body_en);
        $this->assertSame('2026-03-27', $news->published_at->toDateString());
        $this->assertTrue($news->is_published);
        Storage::disk('public')->assertExists($news->image);

        // Running again keeps what is there
        $this->artisan('site:import-old-news')->assertSuccessful();
        $this->assertSame(1, News::count());
    }

    public function test_first_page_has_a_top_story_and_years_filter(): void
    {
        $this->news(['slug' => 'old', 'title_uz' => 'Eski xabar', 'published_at' => '2025-05-01']);
        $this->news(['slug' => 'top', 'title_uz' => 'Eng yangi xabar', 'published_at' => '2026-10-25']);
        $this->news();

        $this->get('/uz/news')->assertOk()
            ->assertSeeInOrder(['Asosiy', 'Eng yangi xabar', 'So‘nggi xabarlar', 'Tanlov e’lon qilamiz'])
            ->assertSee('?year=2025', false);

        $this->get('/uz/news?year=2025')->assertOk()->assertSee('Eski xabar')->assertDontSee('Eng yangi xabar');
    }
}
