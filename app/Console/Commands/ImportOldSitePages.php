<?php

namespace App\Console\Commands;

use App\Models\InfoPage;
use App\Services\Site\OldSitePage;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Copies the company / disclosure pages of the old xalqsugurta.uz into info_pages.
 * New pages are left unpublished: staff check them in the admin panel and switch them on.
 */
final class ImportOldSitePages extends Command
{
    protected $signature = 'site:import-old-pages
        {--only= : Comma-separated page keys (default: all)}
        {--download : Copy the linked documents (PDF…) to our storage}
        {--force : Overwrite pages that already exist}';

    protected $description = 'Import the company pages of the old xalqsugurta.uz site';

    private const LOCALES = ['uz', 'ru', 'en'];

    /** Files over this size are left on the old site */
    private const MAX_FILE_BYTES = 30 * 1024 * 1024;

    public function handle(): int
    {
        $keys = $this->option('only')
            ? array_intersect(array_map('trim', explode(',', $this->option('only'))), array_keys(InfoPage::SOURCES))
            : array_keys(InfoPage::SOURCES);

        if ($keys === []) {
            $this->error('Bunday sahifa yo\'q. Mavjudlari: ' . implode(', ', array_keys(InfoPage::SOURCES)));

            return self::FAILURE;
        }

        $sort = 0;
        foreach (InfoPage::SOURCES as $key => [$section]) {
            $sort += 10;
            if (!in_array($key, $keys, true)) {
                continue;
            }

            $page = InfoPage::firstWhere('key', $key);
            if ($page && !$this->option('force')) {
                $this->line("  {$key}: bor, o'tkazib yuborildi (--force bilan qayta yoziladi)");
                continue;
            }

            $data = [];
            foreach (self::LOCALES as $locale) {
                $path = InfoPage::oldPath($key, $locale);
                if ($path === null) {
                    continue;
                }

                $parsed = $this->fetch(OldSitePage::BASE_URL . '/' . $locale . '/' . $path);
                if ($parsed?->body === null) {
                    continue;
                }

                $data['title_' . $locale] = $parsed->title;
                $data['body_' . $locale]  = $this->option('download') ? $this->download($parsed) : $parsed->body;
            }

            if ($data === []) {
                $this->warn("  {$key}: matn topilmadi");
                continue;
            }

            $page ??= new InfoPage(['key' => $key, 'section' => $section, 'sort_order' => $sort, 'is_published' => false]);
            $page->fill($data + ['imported_at' => now()])->save();

            $locales = array_filter(self::LOCALES, fn (string $l): bool => isset($data['body_' . $l]));
            $this->info("  {$key}: " . implode(', ', $locales));
        }

        $this->newLine();
        $this->line('Sahifalar admin panelda: Katalog → Kompaniya sahifalari. Tekshirib, "Saytda ko\'rsatilsin"ni yoqing.');

        return self::SUCCESS;
    }

    private function fetch(string $url): ?OldSitePage
    {
        try {
            $response = Http::timeout(20)->retry(2, 500, throw: false)->get($url);
        } catch (ConnectionException) {
            $this->warn("  {$url}: ulanib bo'lmadi");

            return null;
        }

        return $response->successful() ? OldSitePage::parse($response->body()) : null;
    }

    /** Copies the page's documents to the public disk and points the links at them */
    private function download(OldSitePage $page): string
    {
        $body = $page->body;
        $disk = Storage::disk('public');

        foreach ($page->files as $url) {
            $name = rawurldecode(basename(parse_url($url, PHP_URL_PATH) ?? ''));
            $name = preg_replace('/[^\pL\pN._-]+/u', '-', $name) ?: 'file';
            $path = 'info-pages/' . substr(sha1($url), 0, 8) . '-' . $name;

            if (!$disk->exists($path)) {
                try {
                    $response = Http::timeout(60)->get($url);
                } catch (ConnectionException) {
                    $response = null;
                }
                if (!$response?->successful() || strlen($response->body()) > self::MAX_FILE_BYTES) {
                    $this->warn("    fayl olinmadi: {$url}");
                    continue;
                }
                $disk->put($path, $response->body());
            }

            $body = str_replace('href="' . e($url) . '"', 'href="/storage/' . e($path) . '"', $body);
        }

        return $body;
    }
}
