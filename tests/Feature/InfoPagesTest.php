<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\InfoPageResource;
use App\Filament\Admin\Resources\InfoPageResource\Pages\CreateInfoPage;
use App\Models\InfoPage;
use App\Models\User;
use App\Services\Site\OldSitePage;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InfoPagesTest extends TestCase
{
    use RefreshDatabase;

    /** Shaped like the old site's pages; the people and documents are made up */
    private const OLD_DOCS = <<<'HTML'
        <html><body><header><a href="/news">Новости</a></header>
        <main class="main">
          <section class="banner-light"><div class="banner-light__wrapper"><h1 class="banner-light__title">Лицензии</h1></div></section>
          <section class="template"><div class="template__wrapper">
            <div class="template-content">
              <div class="financial-statements"><div class="financial-statements__block">
                <span class="financial-statements__year _animation"><span>2020</span></span>
                <div class="financial-statements__documents">
                  <li class="financial-statements__item"><a href="/uploads/files/test/doc%201.PDF"><svg><use xlink:href="#icon-pdf-file"></use></svg><span>Баланс</span></a></li>
                  <li class="financial-statements__item"><a href="" ><span>Пустая ссылка</span></a></li>
                </div>
              </div></div>
              <ul class="leaderships__list"><li><div class="leaderships__img"><img src="/uploads/x.jpg"></div>
                <span class="leaderships__title">Тестов Тест Тестович</span>
                <span class="leaderships__post">Директор</span>
                <a href="mailto: test@example.com"><span>test@example.com</span></a></li></ul>
              <script>alert(1)</script>
            </div>
            <aside class="template-bar"><a href="/management">Руководство</a></aside>
          </div></section>
        </main></body></html>
        HTML;

    // ─── Parsing ──────────────────────────────────────────────────────────────

    public function test_old_page_is_turned_into_plain_html(): void
    {
        $page = OldSitePage::parse(self::OLD_DOCS);

        $this->assertSame('Лицензии', $page->title);
        $this->assertStringContainsString('<h3>2020</h3>', $page->body);
        $this->assertStringContainsString('<ul>', $page->body);
        $this->assertStringContainsString('<li><a href="https://xalqsugurta.uz/uploads/files/test/doc%201.PDF">Баланс</a></li>', $page->body);
        $this->assertStringContainsString('<li>Пустая ссылка</li>', $page->body);
        $this->assertStringContainsString('<h3>Тестов Тест Тестович</h3>', $page->body);
        $this->assertStringContainsString('href="mailto:test@example.com"', $page->body);
        $this->assertSame(['https://xalqsugurta.uz/uploads/files/test/doc%201.PDF'], $page->files);

        foreach (['<svg', '<img', 'alert(1)', 'Руководство', 'Новости', 'class='] as $gone) {
            $this->assertStringNotContainsString($gone, $page->body);
        }
    }

    public function test_page_without_content_block_has_no_body(): void
    {
        $this->assertNull(OldSitePage::parse('<html><body><h1>Home</h1></body></html>')->body);
    }

    // ─── Import command ───────────────────────────────────────────────────────

    public function test_import_creates_unpublished_pages_and_can_copy_documents(): void
    {
        Storage::fake('public');
        Http::fake([
            'xalqsugurta.uz/ru/licenses'           => Http::response(self::OLD_DOCS),
            'xalqsugurta.uz/uz/licenses'           => Http::response(str_replace('Лицензии', 'Litsenziyalar', self::OLD_DOCS)),
            'xalqsugurta.uz/en/licenses'           => Http::response('<html><body>no content</body></html>'),
            'xalqsugurta.uz/uploads/files/test/*'  => Http::response('%PDF-1.4 test'),
        ]);

        $this->artisan('site:import-old-pages', ['--only' => 'licenses', '--download' => true])->assertSuccessful();

        $page = InfoPage::sole();
        $this->assertSame('licenses', $page->key);
        $this->assertSame('about', $page->section);
        $this->assertFalse($page->is_published);
        $this->assertNotNull($page->imported_at);
        $this->assertSame('Litsenziyalar', $page->title_uz);
        $this->assertNull($page->body_en);

        $files = Storage::disk('public')->files('info-pages');
        $this->assertCount(1, $files);
        $this->assertStringEndsWith('-doc-1.PDF', $files[0]);
        $this->assertStringContainsString('href="/storage/' . $files[0] . '"', $page->body_ru);
        $this->assertStringNotContainsString('xalqsugurta.uz/uploads', $page->body_ru);

        // A second run leaves the edited page alone unless forced
        $page->update(['title_ru' => 'Изменено']);
        $this->artisan('site:import-old-pages', ['--only' => 'licenses'])->assertSuccessful();
        $this->assertSame('Изменено', $page->fresh()->title_ru);

        $this->artisan('site:import-old-pages', ['--only' => 'licenses', '--force' => true])->assertSuccessful();
        $this->assertSame('Лицензии', $page->fresh()->title_ru);
    }

    public function test_import_rejects_unknown_keys(): void
    {
        Http::fake();

        $this->artisan('site:import-old-pages', ['--only' => 'nope'])->assertFailed();
        Http::assertNothingSent();
    }

    // ─── Site ─────────────────────────────────────────────────────────────────

    private function page(array $attrs = []): InfoPage
    {
        return InfoPage::create(array_merge([
            'key' => 'licenses', 'section' => 'about', 'is_published' => true,
            'title_ru' => 'Лицензии', 'title_uz' => 'Litsenziyalar',
            'body_ru' => '<p onclick="x()">Русский текст<script>alert(1)</script></p>',
        ], $attrs));
    }

    public function test_published_page_is_shown_with_russian_fallback(): void
    {
        $this->page();
        $this->page(['key' => 'audit', 'title_uz' => 'Audit xulosasi', 'sort_order' => 5]);

        $html = $this->get('/uz/info/licenses')
            ->assertOk()
            ->assertSee('Litsenziyalar')
            ->assertSee('<p>Русский текст</p>', false)
            ->assertSee('href="' . route('info.show', ['locale' => 'uz', 'key' => 'audit']) . '"', false)
            ->getContent();

        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    public function test_unpublished_and_unknown_pages_are_404(): void
    {
        $this->page(['is_published' => false]);

        $this->get('/uz/info/licenses')->assertNotFound();
        $this->get('/uz/info/nope')->assertNotFound();
    }

    public function test_menu_links_switch_to_our_page_once_published(): void
    {
        $this->assertSame('https://xalqsugurta.uz/uz/licenses', InfoPage::link('licenses', 'uz'));
        $this->assertSame('https://xalqsugurta.uz/ru/zakonodatelstvo-v-sfere-strahovaniya', InfoPage::link('legislation', 'uz'));

        $page = $this->page(['is_published' => false]);
        $this->assertStringStartsWith('https://xalqsugurta.uz/', InfoPage::link('licenses', 'uz'));

        $page->update(['is_published' => true]);
        $this->assertSame('/uz/info/licenses', InfoPage::link('licenses', 'uz'));

        $this->get('/uz')->assertOk()->assertSee('href="/uz/info/licenses"', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee(url('/ru/info/licenses'), false);
    }

    // ─── Admin ────────────────────────────────────────────────────────────────

    public function test_admin_lists_and_creates_pages(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->page();

        $this->get(InfoPageResource::getUrl('index'))->assertOk()->assertSee('Litsenziyalar');
        $this->get(InfoPageResource::getUrl('edit', ['record' => InfoPage::sole()]))->assertOk();

        Livewire::test(CreateInfoPage::class)
            ->fillForm(['key' => 'Bad Key', 'section' => 'about', 'title_ru' => 'X'])
            ->call('create')
            ->assertHasFormErrors(['key']);

        Livewire::test(CreateInfoPage::class)
            ->fillForm(['key' => 'branches', 'section' => 'about', 'title_ru' => 'Филиалы', 'is_published' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('/ru/info/branches', InfoPage::link('branches', 'ru'));
    }
}
