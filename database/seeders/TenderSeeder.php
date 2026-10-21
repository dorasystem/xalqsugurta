<?php

namespace Database\Seeders;

use App\Models\Tender;
use Illuminate\Database\Seeder;

/**
 * The first tenders on /{locale}/tenders (October 2026). Safe to run again: rows are
 * matched by slug and their texts refreshed. php artisan db:seed --class=TenderSeeder
 */
class TenderSeeder extends Seeder
{
    public function run(): void
    {
        $mail = '<a href="mailto:info@xalqsugurta.uz">info@xalqsugurta.uz</a>';

        $tenders = [
            [
                'slug'         => 'audit-2026-financial-statements',
                'published_at' => '2026-10-15',
                'deadline_at'  => '2026-10-23',
                'title_uz'     => '2026-yil moliyaviy hisobotlarini (BHMS va MHXS) audit qilish uchun auditorlik tashkilotini tanlash bo‘yicha tanlov',
                'title_ru'     => 'Конкурс по выбору аудиторской организации для аудита финансовой отчётности за 2026 год (НСБУ и МСФО)',
                'title_en'     => 'Tender to select an audit firm for the 2026 financial statements (NAS and IFRS)',
                'body_uz'      => '<p>“Xalq sug‘urta” aksiyadorlik jamiyati 2026-yil uchun Buxgalteriya hisobining milliy standartlari (BHMS) va Moliyaviy hisobotning xalqaro standartlari (MHXS) bo‘yicha tuzilgan moliyaviy hisobotlarini auditdan o‘tkazish uchun auditorlik tashkilotini aniqlash maqsadida tanlov e’lon qiladi.</p>'
                    . "<p>Xizmat narxi ko‘rsatilgan tijorat takliflaringizni <strong>2026-yil 23-oktabrgacha</strong> {$mail} elektron pochta manziliga yuborishingizni so‘raymiz.</p>",
                'body_ru'      => '<p>АО «Xalq sug‘urta» объявляет конкурс по выбору аудиторской организации для проведения аудита финансовой отчётности за 2026 год, составленной по национальным стандартам бухгалтерского учёта (НСБУ) и международным стандартам финансовой отчётности (МСФО).</p>'
                    . "<p>Просим направить коммерческие предложения с указанием стоимости услуг на электронную почту {$mail} <strong>до 23 октября 2026 года</strong>.</p>",
                'body_en'      => '<p>Xalq Sug‘urta JSC announces a tender to select an audit firm to audit its 2026 financial statements prepared under the National Accounting Standards of Uzbekistan (NAS) and International Financial Reporting Standards (IFRS).</p>'
                    . "<p>Please send your commercial proposal, including the price of the services, to {$mail} <strong>by 23 October 2026</strong>.</p>",
            ],
            [
                'slug'         => 'recourse-receivables-card-collection',
                'published_at' => '2026-10-14',
                'deadline_at'  => '2026-10-20',
                'title_uz'     => 'Regress tartibidagi debitorlik qarzlarini jismoniy shaxslarning plastik kartalaridan avtomatik undirish xizmati bo‘yicha tanlov',
                'title_ru'     => 'Конкурс на услугу автоматического взыскания регрессной дебиторской задолженности с пластиковых карт физических лиц',
                'title_en'     => 'Tender for automatic collection of recourse receivables from individuals’ bank cards',
                'body_uz'      => '<p>“Xalq sug‘urta” aksiyadorlik jamiyati regress tartibida yuzaga kelgan debitorlik qarzlarini jismoniy shaxslarning plastik kartalaridan Jamiyat foydasiga avtomatik ravishda undirish xizmatini ko‘rsatuvchi tashkilotni aniqlash maqsadida tanlov e’lon qiladi.</p>'
                    . "<p>Xizmat narxi ko‘rsatilgan tijorat takliflaringizni <strong>2026-yil 20-oktabrgacha</strong> {$mail} elektron pochta manziliga yuborishingizni so‘raymiz.</p>",
                'body_ru'      => '<p>АО «Xalq sug‘urta» объявляет конкурс по выбору организации для автоматического взыскания в пользу Общества дебиторской задолженности, возникшей в порядке регресса, с пластиковых карт физических лиц.</p>'
                    . "<p>Просим направить коммерческие предложения с указанием стоимости услуг на электронную почту {$mail} <strong>до 20 октября 2026 года</strong>.</p>",
                'body_en'      => '<p>Xalq Sug‘urta JSC announces a tender to select a company to automatically collect, in the Company’s favour, receivables arising from recourse (subrogation) claims from individuals’ bank cards.</p>'
                    . "<p>Please send your commercial proposal, including the price of the services, to {$mail} <strong>by 20 October 2026</strong>.</p>",
            ],
        ];

        foreach ($tenders as $tender) {
            Tender::updateOrCreate(
                ['slug' => $tender['slug']],
                $tender + ['email' => 'info@xalqsugurta.uz', 'is_published' => true],
            );
        }
    }
}
