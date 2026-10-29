<?php

namespace App\Services\Admin;

use App\Models\CommandRun;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

/**
 * Artisan commands staff may start from the admin panel. Only these: a free-form shell would hand
 * the server (and the orders' passport data) to anyone who gets an admin password.
 * Runs go one at a time, after the HTTP response (prod has no queue worker), output kept in command_runs.
 */
final class ServerCommands
{
    /** key => [label, help, group, steps: list of [artisan command, parameters]] */
    public const COMMANDS = [
        'migrate' => [
            'Bazani yangilash (migratsiya)',
            'Yangi jadvallarni yaratadi (vakansiyalar, yangiliklar…). Deploy ham buni bajaradi.',
            'Baza',
            [['migrate', ['--force' => true]]],
        ],
        'migrate-status' => [
            'Migratsiyalar holati',
            'Qaysi migratsiyalar bajarilgan, qaysilari kutmoqda.',
            'Baza',
            [['migrate:status', []]],
        ],
        'import-pages' => [
            'Kompaniya sahifalarini import qilish',
            'Eski saytdagi sahifalar (Kompaniya haqida, Litsenziyalar, Moliyaviy hisobot…) va ularning PDF hujjatlari. Mavjudlari qayta yozilmaydi.',
            'Eski saytdan',
            [['site:import-old-pages', ['--download' => true]]],
        ],
        'import-pages-force' => [
            'Kompaniya sahifalarini qayta import qilish',
            'Barcha sahifalarni eski saytdan qaytadan oladi — admin paneldagi tahrirlar yo\'qoladi.',
            'Eski saytdan',
            [['site:import-old-pages', ['--download' => true, '--force' => true]]],
        ],
        'publish-pages' => [
            'Kompaniya sahifalarini saytda ochish',
            'Matni bor barcha import qilingan sahifalarni "Saytda ko\'rsatilsin" qiladi: menyu eski sayt o\'rniga ularni ochadi.',
            'Eski saytdan',
            [['site:publish-pages', []]],
        ],
        'import-news' => [
            'Yangiliklarni import qilish',
            'Eski saytdagi yangiliklar rasmlari bilan. Mavjudlari qayta yozilmaydi.',
            'Eski saytdan',
            [['site:import-old-news', []]],
        ],
        'seed-tenders' => [
            'Oktyabr tenderlarini qo\'shish',
            'TenderSeeder: ikki tender, qayta bajarilsa takrorlanmaydi.',
            'Ma\'lumotlar',
            [['db:seed', ['--class' => 'Database\\Seeders\\TenderSeeder', '--force' => true]]],
        ],
        'seed-branches' => [
            'Filiallarni qo\'shish',
            'BranchSeeder: eski saytdagi 16 ta filial (rahbar, telefonlar, manzil, xaritadagi nuqta). Mavjudlari o\'zgartirilmaydi.',
            'Ma\'lumotlar',
            [['db:seed', ['--class' => 'Database\\Seeders\\BranchSeeder', '--force' => true]]],
        ],
        'travel-sync' => [
            'VZR polislarini tekshirish',
            'To\'langan "Chet elga chiquvchilar" polislarini sug\'urtachidan so\'raydi (har 10 daqiqada o\'zi ham bajariladi).',
            'Ma\'lumotlar',
            [['travel:sync-policies', []]],
        ],
        'storage-link' => [
            'Fayllar havolasini tiklash',
            'public/storage havolasi: yuklangan rasm va PDF\'lar ochilmasa.',
            'Texnik',
            [['storage:link', []]],
        ],
        'cache' => [
            'Keshni yangilash',
            'Konfiguratsiya, marshrutlar va ko\'rinishlar keshini tozalab qayta yig\'adi.',
            'Texnik',
            [['optimize:clear', []], ['optimize', []], ['filament:optimize', []]],
        ],
    ];

    /** A run older than this is treated as dead (the PHP process was killed) */
    public const STALE_MINUTES = 20;

    /** command_runs key of the GitHub deploy (App\Services\Admin\DeployTrigger), kept for the history */
    public const DEPLOY = 'deploy';

    public static function label(string $key): string
    {
        return $key === self::DEPLOY ? 'Deploy (GitHub Actions)' : (self::COMMANDS[$key][0] ?? $key);
    }

    public static function busy(): ?CommandRun
    {
        return CommandRun::where('status', CommandRun::STATUS_RUNNING)
            ->where('started_at', '>', now()->subMinutes(self::STALE_MINUTES))
            ->latest('id')
            ->first();
    }

    /** Runs the steps of a started run; called after the response, so it may take minutes */
    public static function run(CommandRun $run): void
    {
        $steps = self::COMMANDS[$run->command][3] ?? null;
        if ($steps === null) {
            $run->update(['status' => CommandRun::STATUS_FAILED, 'output' => 'Noma\'lum buyruq', 'finished_at' => now()]);

            return;
        }

        @set_time_limit(900);
        $log = '';
        $status = CommandRun::STATUS_DONE;

        foreach ($steps as [$command, $parameters]) {
            $log .= '$ php artisan ' . $command . self::flags($parameters) . "\n";
            $output = new BufferedOutput();

            try {
                $code = Artisan::call($command, $parameters, $output);
            } catch (Throwable $e) {
                $code = 1;
                $output->writeln(get_class($e) . ': ' . $e->getMessage());
                Log::error('Admin server command failed', ['command' => $command, 'error' => $e->getMessage()]);
            }

            $log .= rtrim($output->fetch()) . "\n";
            $run->update(['output' => $log]);

            if ($code !== 0) {
                $log .= "→ chiqish kodi {$code}\n";
                $status = CommandRun::STATUS_FAILED;
                break;
            }
        }

        $run->update(['status' => $status, 'output' => $log, 'finished_at' => now()]);
    }

    private static function flags(array $parameters): string
    {
        return collect($parameters)->map(fn ($value, string $name) => $value === true ? " {$name}" : " {$name}={$value}")->join('');
    }
}
