<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name_uz'    => 'OSAGO',
                'name_ru'    => 'ОСАГО',
                'name_en'    => 'OSAGO',
                'desc_uz'    => 'Transport vositalari egalarining fuqarolik javobgarligini majburiy sug\'urta qilish',
                'desc_ru'    => 'Обязательное страхование гражданской ответственности владельцев транспортных средств',
                'desc_en'    => 'Compulsory civil liability insurance for vehicle owners',
                'route'      => 'osago',
                'icon'       => 'bi bi-car-front-fill',
                'icon_color' => '#2563eb',
                'icon_bg'    => '#dbeafe',
                'sort_order' => 1,
            ],
            [
                'name_uz'    => 'Baxtsiz hodisa',
                'name_ru'    => 'Несчастный случай',
                'name_en'    => 'Accident',
                'desc_uz'    => 'Baxtsiz hodisalar natijasida yuzaga keladigan jarohat va nogironlikdan himoya',
                'desc_ru'    => 'Страхование медицинской помощи при аварийных ситуациях',
                'desc_en'    => 'Medical assistance coverage in case of accidents',
                'route'      => 'accident',
                'icon'       => 'bi bi-heart-pulse-fill',
                'icon_color' => '#dc2626',
                'icon_bg'    => '#fee2e2',
                'sort_order' => 2,
            ],
            [
                'name_uz'    => 'Mol-mulk sug\'urtasi',
                'name_ru'    => 'Страхование имущества',
                'name_en'    => 'Property Insurance',
                'desc_uz'    => 'Uy-joy va mol-mulkingiz uchun keng qamrovli sug\'urta himoyasi',
                'desc_ru'    => 'Комплексное страхование защиты вашего дома и имущества',
                'desc_en'    => 'Comprehensive insurance protection for your home and property',
                'route'      => 'property',
                'icon'       => 'bi bi-house-fill',
                'icon_color' => '#16a34a',
                'icon_bg'    => '#dcfce7',
                'sort_order' => 3,
            ],
            [
                'name_uz'    => 'Gaz balon sug\'urtasi',
                'name_ru'    => 'Страхование газового баллона',
                'name_en'    => 'Gas Balloon Insurance',
                'desc_uz'    => 'Gaz ballon uskunalari egalari uchun majburiy sug\'urta',
                'desc_ru'    => 'Обязательное страхование для владельцев газобаллонного оборудования',
                'desc_en'    => 'Mandatory insurance for gas cylinder equipment owners',
                'route'      => 'gas',
                'icon'       => 'bi bi-fire',
                'icon_color' => '#ea580c',
                'icon_bg'    => '#ffedd5',
                'sort_order' => 4,
            ],
            [
                'name_uz'    => 'OSGOR',
                'name_ru'    => 'ОСГОР',
                'name_en'    => 'OSGOR',
                'desc_uz'    => 'Xodimlarni sug\'urta qilish',
                'desc_ru'    => 'Страхование работников от НС',
                'desc_en'    => 'Employment liability insurance',
                'route'      => 'osgor',
                'icon'       => 'bi bi-person-gear',
                'icon_color' => '#7c3aed',
                'icon_bg'    => '#ede9fe',
                'sort_order' => 5,
            ],
            [
                'name_uz'    => 'OSGOP',
                'name_ru'    => 'ОСГОП',
                'name_en'    => 'OSGOP',
                'desc_uz'    => 'Yo\'lovchi tashuvchilar uchun majburiy javobgarlik sug\'urta',
                'desc_ru'    => 'Обязательное страхование ответственности перевозчика пассажиров',
                'desc_en'    => 'Mandatory insurance for passenger transport operators',
                'route'      => 'osgop',
                'icon'       => 'bi bi-bus-front-fill',
                'icon_color' => '#0d9488',
                'icon_bg'    => '#ccfbf1',
                'sort_order' => 6,
            ],
            [
                'name_uz'    => 'Turistlar sug\'urtasi',
                'name_ru'    => 'Страхование туристов',
                'name_en'    => 'Tourist Insurance',
                'desc_uz'    => 'Mehmonxonada yashovchi turistlarni baxtsiz hodisadan sug\'urtalash',
                'desc_ru'    => 'Страхование туристов в гостиницах от несчастных случаев',
                'desc_en'    => 'Accident insurance for tourists staying in hotels',
                'route'      => 'tourist',
                'icon'       => 'bi bi-luggage-fill',
                'icon_color' => '#9333ea',
                'icon_bg'    => '#f3e8ff',
                'sort_order' => 7,
            ],
            [
                'name_uz'    => 'KASKO',
                'name_ru'    => 'КАСКО',
                'name_en'    => 'KASKO',
                'desc_uz'    => 'Transport vositasini yo\'l-transport hodisasi, o\'g\'irlik va boshqa zararlardan sug\'urta qilish',
                'desc_ru'    => 'Страхование автомобиля от ДТП, угона и прочих ущербов',
                'desc_en'    => 'Comprehensive vehicle insurance against accidents, theft and other damages',
                'route'      => 'kasko',
                'icon'       => 'bi bi-shield-fill-check',
                'icon_color' => '#0284c7',
                'icon_bg'    => '#e0f2fe',
                'sort_order' => 8,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['route' => $product['route']], $product);
        }
    }
}
