<?php

namespace Database\Seeders;

use App\Models\InsuranceTerm;
use Illuminate\Database\Seeder;

class InsuranceTermSeeder extends Seeder
{
    public function run(): void
    {
        $terms = [
            // ── OSGOP ──────────────────────────────────────────────────────
            [
                'provider_term_id' => 7,
                'name_uz'          => '3 oy',
                'name_ru'          => '3 месяца',
                'name_en'          => '3 months',
                'months'           => 3,
            ],
            [
                'provider_term_id' => 3,
                'name_uz'          => '6 oy',
                'name_ru'          => '6 месяцев',
                'name_en'          => '6 months',
                'months'           => 6,
            ],
            [
                'provider_term_id' => 8,
                'name_uz'          => '9 oy',
                'name_ru'          => '9 месяцев',
                'name_en'          => '9 months',
                'months'           => 9,
            ],
            [
                'provider_term_id' => 4,
                'name_uz'          => '12 oy (1 yil)',
                'name_ru'          => '12 месяцев (1 год)',
                'name_en'          => '12 months (1 year)',
                'months'           => 12,
            ],
        ];

        foreach ($terms as $term) {
            InsuranceTerm::updateOrCreate(
                [
                    'provider_term_id' => $term['provider_term_id'],
                ],
                array_merge($term, ['is_active' => true])
            );
        }
    }
}
