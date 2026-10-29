<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

/**
 * Branches as published on the old xalqsugurta.uz/filialy (October 2026): name, manager, phones,
 * address and map point. The old site had the addresses in Russian only; uz / en names are
 * translations of the branch names, addresses fall back to Russian until staff fill them in.
 * Safe to re-run: rows are matched by the Russian name and existing ones are left as edited.
 */
class BranchSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::BRANCHES as $i => $branch) {
            Branch::firstOrCreate(['name_ru' => $branch['name_ru']], $branch + ['sort_order' => ($i + 1) * 10, 'is_active' => true]);
        }
    }

    private const BRANCHES = [
            [
                'name_ru'    => 'Центральный аппарат',
                'name_uz'    => 'Markaziy apparat',
                'name_en'    => 'Head office',
                'address_ru' => '100000, г. Ташкент, Мирзо Улугбекский район, МСГ Лашкарбеги, проспект Хамида Алимджана, дом 13А',
                'manager'    => 'Мамасолиев Собир Курдашевич',
                'phones'     => ['(+998 71) 202-19-66'],
                'lat'        => 41.318486,
                'lng'        => 69.293589,
            ],
            [
                'name_ru'    => 'Филиал Республики Каракалпакстан',
                'name_uz'    => 'Qoraqalpog‘iston Respublikasi filiali',
                'name_en'    => 'Republic of Karakalpakstan branch',
                'address_ru' => '230100, Республика Каракалпакстан, город Нукус, улица Каракалпакстан, 28/2',
                'manager'    => 'Есенкулов Бахадыр Адылбаевич',
                'phones'     => ['(+998 61) 224-84-95', '(+998 90) 723-72-36', '(+998 61) 222-68-82'],
                'lat'        => 42.457414,
                'lng'        => 59.596149,
            ],
            [
                'name_ru'    => 'Андижанский областной филиал',
                'name_uz'    => 'Andijon viloyati filiali',
                'name_en'    => 'Andijan regional branch',
                'address_ru' => '170100 Андижанская область, г. Андижан, улица Мустакиллик, дом 3',
                'manager'    => 'Турсунов Расулжон Дилмурод ўғли',
                'phones'     => ['(+998 74) 225-83-87', '(+998 97) 444-32-61', '(+998 97) 999 04 40'],
                'lat'        => 40.691793,
                'lng'        => 71.938462,
            ],
            [
                'name_ru'    => 'Кашкадарьинский областной филиал',
                'name_uz'    => 'Qashqadaryo viloyati filiali',
                'name_en'    => 'Kashkadarya regional branch',
                'address_ru' => '180112, Кашкадарьинская область, г.Карши, ул. А.Темура, дом 44',
                'manager'    => 'Халилов Руслан Асадович',
                'phones'     => ['(+998 75) 225-07-91', '(+998 97) 444-32-65', '(+998 91) 322 55 05'],
                'lat'        => 38.819366,
                'lng'        => 65.773172,
            ],
            [
                'name_ru'    => 'Навоийский областной филиал',
                'name_uz'    => 'Navoiy viloyati filiali',
                'name_en'    => 'Navoi regional branch',
                'address_ru' => '210100, Навоийская область, Карманинский район, улица И.Каримова, дом 90',
                'manager'    => 'Тураев Азизбек Азаматович',
                'phones'     => ['(+998 36) 532-04-25', '(+998 90) 118 00 83'],
                'lat'        => 40.137450,
                'lng'        => 65.356505,
            ],
            [
                'name_ru'    => 'Наманганский областной филиал',
                'name_uz'    => 'Namangan viloyati filiali',
                'name_en'    => 'Namangan regional branch',
                'address_ru' => '116036, Наманганская область, город Наманган, улица Лутфи, дом 5А',
                'manager'    => 'Набижонов Абдулходи Эргашалиевич',
                'phones'     => ['(+998 69) 227-24-44', '(+998 97) 830 06 60', '(+998 33) 830 06 60'],
                'lat'        => 41.001544,
                'lng'        => 71.668949,
            ],
            [
                'name_ru'    => 'Сурхандарьинский областной филиал',
                'name_uz'    => 'Surxondaryo viloyati filiali',
                'name_en'    => 'Surkhandarya regional branch',
                'address_ru' => '190100, Сурхандарьинская область, город Термез, улица Маданият, дом 2',
                'manager'    => 'Сафаров Камолиддин Сохибжон угли',
                'phones'     => ['(+998 76) 224-20-60', '(+998 90) 010 44 47'],
                'lat'        => 37.232891,
                'lng'        => 67.289690,
            ],
            [
                'name_ru'    => 'Ферганский областной филиал',
                'name_uz'    => 'Farg‘ona viloyati filiali',
                'name_en'    => 'Fergana regional branch',
                'address_ru' => '151100 Ферганская область, город Фергана, улица Конституция, дом 45',
                'manager'    => 'Маматисаков Даврон Тошпулатович',
                'phones'     => ['(+998 73) 244 44 69', '(+998 97) 444-32-75', '(+998 90) 277 68 38'],
                'lat'        => 40.385244,
                'lng'        => 71.788758,
            ],
            [
                'name_ru'    => 'Сырдарьинский областной филиал',
                'name_uz'    => 'Sirdaryo viloyati filiali',
                'name_en'    => 'Syrdarya regional branch',
                'address_ru' => '120100, Сырдарьинская область, город Гулистан, улица Хандамир, дом 3',
                'manager'    => 'Чулиев Кодиржон Худойберди угли',
                'phones'     => ['(+998 67) 221-00-21', '(+998 67) 225 28 82', '(+998 97) 275 55 11'],
                'lat'        => 40.490651,
                'lng'        => 68.777606,
            ],
            [
                'name_ru'    => 'Ташкентский областной филиал',
                'name_uz'    => 'Toshkent viloyati filiali',
                'name_en'    => 'Tashkent regional branch',
                'address_ru' => '100000, г.Ташкент, Шайхантахурский район, МСГ Шодлик, массив Ибн Сина-2, дом 18, L блок, L3',
                'manager'    => 'Мамажонов Хуршидбек Халимжон угли',
                'phones'     => ['+998 93 602 64 44'],
                'lat'        => 41.338457,
                'lng'        => 69.167547,
            ],
            [
                'name_ru'    => 'Хорезмский областной филиал',
                'name_uz'    => 'Xorazm viloyati filiali',
                'name_en'    => 'Khorezm regional branch',
                'address_ru' => '220100, Хорезмская область, город Ургенч, улица Тинчлик, дом 2',
                'manager'    => 'Сабиров Санъатбек Рахимбоевич',
                'phones'     => ['(+998 62) 228-44-88', '(+998 97) 444-32-76', '(+998 99) 965 19 85'],
                'lat'        => 41.557739,
                'lng'        => 60.621319,
            ],
            [
                'name_ru'    => 'Самаркандский областной филиал',
                'name_uz'    => 'Samarqand viloyati filiali',
                'name_en'    => 'Samarkand regional branch',
                'address_ru' => '140057, Самаркандская область, город Самарканд, улица Амира Темура, 145',
                'manager'    => 'Хужамов Акбар Бахриддинович',
                'phones'     => ['(+998 66) 237 86 04', '(+998 97) 444-32-68', '(+998 77) 132 32 68'],
                'lat'        => 39.735349,
                'lng'        => 66.937056,
            ],
            [
                'name_ru'    => 'Ташкентский городской филиал',
                'name_uz'    => 'Toshkent shahar filiali',
                'name_en'    => 'Tashkent city branch',
                'address_ru' => '100000, г.Ташкент, Юнусабадский район, 13-квартал, дом 45, кв. 74',
                'manager'    => 'Рамазонов Элёр Шавкатович',
                'phones'     => ['(+998 93) 005-77-76'],
                'lat'        => 41.367757,
                'lng'        => 69.302635,
            ],
            [
                'name_ru'    => 'Джиззакский областной филиал',
                'name_uz'    => 'Jizzax viloyati filiali',
                'name_en'    => 'Jizzakh regional branch',
                'address_ru' => '130100, Джиззакская область, г. Джиззак, МСГ Бабура, улица К.Имомова, дом 81',
                'manager'    => 'Шоқосимов Салоҳиддин Абдусаломович',
                'phones'     => ['(+998 72) 222-42-99', '(+998 97) 444-32-64', '(+998 94) 572 00 32'],
                'lat'        => 40.109977,
                'lng'        => 67.846780,
            ],
            [
                'name_ru'    => 'Бухарский областной филиал',
                'name_uz'    => 'Buxoro viloyati filiali',
                'name_en'    => 'Bukhara regional branch',
                'address_ru' => '200100, Бухарская область, г. Бухара, ул. М.Икбол, дом 8',
                'manager'    => 'Рахимова Ситора Рахимовна',
                'phones'     => ['(+998 65) 223-77-19', '(+998 91) 310 98 35'],
                'lat'        => 39.855334,
                'lng'        => 64.450277,
            ],
            [
                'name_ru'    => 'Центр продажи',
                'name_uz'    => 'Savdo markazi',
                'name_en'    => 'Sales centre',
                'address_ru' => '100121, г. Ташкент, Яккасарайский район, улица Бабура, дом 17, квартира 30',
                'manager'    => 'Каршиев Шухрат Хурсанович',
                'phones'     => ['(+998 90) 963-54-70'],
                'lat'        => 41.294388,
                'lng'        => 69.25099,
            ],
    ];
}
