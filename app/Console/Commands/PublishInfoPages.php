<?php

namespace App\Console\Commands;

use App\Models\InfoPage;
use Illuminate\Console\Command;

/** Switches on the imported company pages that have text, so the menu opens them instead of the old site */
final class PublishInfoPages extends Command
{
    protected $signature = 'site:publish-pages {--only= : Comma-separated page keys (default: all)}';

    protected $description = 'Publish the imported company pages that have text';

    public function handle(): int
    {
        $query = InfoPage::query()->where('is_published', false);
        if ($this->option('only')) {
            $query->whereIn('key', array_map('trim', explode(',', $this->option('only'))));
        }

        $published = [];
        foreach ($query->get() as $page) {
            if (blank($page->body_uz) && blank($page->body_ru) && blank($page->body_en)) {
                $this->warn("  {$page->key}: matn yo'q, yopiq qoldi");
                continue;
            }
            $page->update(['is_published' => true]);   // save() clears the published-keys cache
            $published[] = $page->key;
        }

        $this->info($published === [] ? 'Yangi ochilgan sahifa yo\'q.' : 'Saytda ochildi: ' . implode(', ', $published));

        return self::SUCCESS;
    }
}
