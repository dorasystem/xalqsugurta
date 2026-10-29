<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Services\Site\OldSitePage;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Copies the press centre of the old xalqsugurta.uz into `news`. The old site serves the list as
 * /{n}/news/json (0 = ru, 1 = uz, 2 = en) in the same order in every language, so the n-th item
 * of each list is the same news. Items are published (they are public on the old site already);
 * existing ones are skipped unless --force.
 */
final class ImportOldSiteNews extends Command
{
    protected $signature = 'site:import-old-news {--force : Overwrite news that already exist}';

    protected $description = 'Import the news of the old xalqsugurta.uz site';

    private const LISTS = ['ru' => 0, 'uz' => 1, 'en' => 2];

    private const MAX_IMAGE_BYTES = 10 * 1024 * 1024;

    public function handle(): int
    {
        $lists = [];
        foreach (self::LISTS as $locale => $n) {
            $lists[$locale] = $this->json(OldSitePage::BASE_URL . '/' . $n . '/news/json') ?? [];
        }

        if ($lists['uz'] === [] && $lists['ru'] === []) {
            $this->error('Eski saytdan yangiliklar ro\'yxati olinmadi.');

            return self::FAILURE;
        }

        $seen = [];
        $count = max(array_map('count', $lists));
        for ($i = 0; $i < $count; $i++) {
            $items = array_filter(array_map(fn (array $list) => $list[$i] ?? null, $lists), fn ($item) => is_array($item) && self::slugOf($item) !== null);
            if ($items === []) {
                continue;
            }

            // The key is the first locale's link: the old list repeats some news
            $source = OldSitePage::BASE_URL . ($items['uz']['link'] ?? $items['ru']['link'] ?? reset($items)['link']);
            if (isset($seen[$source])) {
                continue;
            }
            $seen[$source] = true;

            $news = News::firstWhere('source_url', $source);
            if ($news && !$this->option('force')) {
                $this->line('  ' . self::slugOf(reset($items)) . ': bor, o\'tkazib yuborildi');
                continue;
            }

            $data = [];
            foreach ($items as $locale => $item) {
                $page = $this->page(OldSitePage::BASE_URL . $item['link']);
                $data['title_' . $locale] = Str::limit(trim(html_entity_decode((string) $item['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8')), 490);
                $data['body_' . $locale]  = $page?->body;
            }

            // The old site has items with an empty page (the 2026 audit tender is in our Tenders section)
            if (array_filter(array_map(fn (string $l) => $data['body_' . $l] ?? null, array_keys($items))) === []) {
                $this->warn('  ' . self::slugOf(reset($items)) . ': matn yo\'q, o\'tkazib yuborildi');
                continue;
            }

            $first = $items['ru'] ?? $items['en'] ?? reset($items);   // the uz list has wrong dates on its repeats
            $slug  = Str::limit(Str::slug(self::slugOf($items['uz'] ?? $items['en'] ?? reset($items))), 100, '') ?: 'news-' . ($i + 1);

            $news ??= new News(['source_url' => $source, 'slug' => $this->uniqueSlug($slug)]);
            $news->fill($data + [
                'published_at' => self::date($first['date'] ?? null),
                'is_published' => true,
                'image'        => $this->image($first['img'] ?? null) ?? $news->image,
            ])->save();

            $this->info('  ' . $news->slug . ': ' . implode(', ', array_keys($items)));
        }

        $this->newLine();
        $this->line('Yangiliklar admin panelda: Katalog → Yangiliklar.');

        return self::SUCCESS;
    }

    private function json(string $url): ?array
    {
        try {
            $response = Http::timeout(20)->retry(2, 500, throw: false)->get($url);
        } catch (ConnectionException) {
            return null;
        }

        return $response->successful() && is_array($response->json()) ? $response->json() : null;
    }

    private function page(string $url): ?OldSitePage
    {
        try {
            $response = Http::timeout(20)->retry(2, 500, throw: false)->get($url);
        } catch (ConnectionException) {
            return null;
        }

        return $response->successful() ? OldSitePage::parse($response->body()) : null;
    }

    /** Copies the picture to the public disk; the list gives a thumbnail, the original is tried first */
    private function image(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $candidates = [];
        if (preg_match('#^(.*)/__thumbs/([^/]+)/[^/]+$#', $path, $m)) {
            $candidates[] = $m[1] . '/' . $m[2];
        }
        $candidates[] = $path;

        foreach ($candidates as $candidate) {
            $url = OldSitePage::BASE_URL . $candidate;
            try {
                $response = Http::timeout(60)->get($url);
            } catch (ConnectionException) {
                continue;
            }
            if (!$response->successful() || strlen($response->body()) > self::MAX_IMAGE_BYTES || !str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                continue;
            }

            $name   = preg_replace('/[^\pL\pN._-]+/u', '-', rawurldecode(basename($candidate))) ?: 'image';
            $stored = 'news/' . substr(sha1($url), 0, 8) . '-' . $name;
            Storage::disk('public')->put($stored, $response->body());

            return $stored;
        }

        return null;
    }

    private function uniqueSlug(string $slug): string
    {
        $candidate = $slug;
        for ($n = 2; News::where('slug', $candidate)->exists(); $n++) {
            $candidate = $slug . '-' . $n;
        }

        return $candidate;
    }

    private static function slugOf(array $item): ?string
    {
        $slug = trim(rawurldecode(basename((string) ($item['link'] ?? ''))));

        return $slug === '' || $slug === 'news' ? null : $slug;
    }

    private static function date(?string $date): Carbon
    {
        return $date && preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date) ? Carbon::createFromFormat('d.m.Y', $date)->startOfDay() : now();
    }
}
