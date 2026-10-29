<?php

namespace Database\Seeders;

use App\Models\OfferProduct;
use Illuminate\Database\Seeder;

/**
 * The three catalogue products of the old xalqsugurta.uz we sell on application. Texts come from
 * the old product pages (uz was machine-translated and is corrected here, "collective" had no uz / en
 * page and is translated from ru); the insurer approves the final wording in the admin panel.
 * Safe to re-run: existing rows are left as edited.
 */
class OfferProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::PRODUCTS as $i => $product) {
            OfferProduct::firstOrCreate(['slug' => $product['slug']], $product + ['sort_order' => ($i + 1) * 10, 'is_active' => true]);
        }
    }

    private const PRODUCTS = [
        [
            'slug'          => 'family',
            'audience'      => 'private',
            'title_uz'      => '«Mening oilam»',
            'title_ru'      => '«Моя семья»',
            'title_en'      => '“My family”',
            'kicker_uz'     => 'Baxtsiz hodisalardan sug‘urta',
            'kicker_ru'     => 'Страхование от несчастных случаев',
            'kicker_en'     => 'Accident insurance',
            'lead_uz'       => 'Oilaning barcha sug‘urtalangan a’zolari sug‘urta davrida baxtsiz hodisalardan sutka davomida himoyalanadi.',
            'lead_ru'       => 'Все застрахованные члены семьи защищены от несчастных случаев 24 часа в сутки в течение периода страхования.',
            'lead_en'       => 'All insured family members are covered against accidents 24 hours a day during the insurance period.',
            'highlights_uz' => "24/7 himoya\nButun oila bitta polisda\nVafot etganda 100%",
            'highlights_ru' => "Защита 24/7\nВся семья в одном полисе\n100% при смерти",
            'highlights_en' => "24/7 cover\nThe whole family in one policy\n100% in case of death",
            'covers_uz'     => "Travmatik jarohatlar va sog‘liqning boshqa buzilishlari\nSug‘urtalangan shaxsning vafoti",
            'covers_ru'     => "Травматические повреждения и иные расстройства здоровья\nСмерть застрахованного лица",
            'covers_en'     => "Traumatic injuries and other health disorders\nDeath of the insured person",
            'terms_uz'      => "jadval | Jarohat yoki sog‘liq buzilishida — «Sug‘urta to‘lovlari jadvali»ga ko‘ra sug‘urta summasining foizi.\n100% | Sug‘urtalangan shaxs vafot etganda — sug‘urta summasining 100%.",
            'terms_ru'      => "таблица | При травме или ином расстройстве здоровья — процент от страховой суммы по «Таблице страховых выплат».\n100% | При смерти застрахованного лица — 100% персональной страховой суммы.",
            'terms_en'      => "table | Injury or other health disorder — a percentage of the sum insured under the “Table of insurance payments”.\n100% | Death of the insured person — 100% of the personal sum insured.",
            'note_uz'       => 'Bir shaxsga sug‘urta davridagi barcha to‘lovlar uning sug‘urta summasidan oshmaydi. To‘lovlar sug‘urtalangan shaxs (merosxo‘r yoki naf oluvchi)ning bank hisobiga o‘tkaziladi.',
            'note_ru'       => 'Общая сумма выплат одному лицу за период страхования не превышает его страховую сумму. Выплаты перечисляются на лицевой счёт застрахованного (наследника или выгодоприобретателя).',
            'note_en'       => 'Total payments to one person during the insurance period cannot exceed their sum insured. Payments go to the bank account of the insured (heir or beneficiary).',
        ],
        [
            'slug'          => 'all_risks',
            'audience'      => 'business',
            'title_uz'      => 'Mulkni barcha xavflardan sug‘urtalash',
            'title_ru'      => 'Страхование имущества от всех рисков',
            'title_en'      => 'Property insurance against all risks',
            'kicker_uz'     => 'Yuridik shaxslar uchun',
            'kicker_ru'     => 'Для юридических лиц',
            'kicker_en'     => 'For legal entities',
            'lead_uz'       => 'Korxona va tashkilotlarning mol-mulki — mulkchilik shaklidan qat’i nazar. Jismoniy yo‘qotish yoki shikastlanishdan ko‘rilgan to‘g‘ridan-to‘g‘ri zarar qoplanadi.',
            'lead_ru'       => 'Имущество предприятий и организаций независимо от формы собственности. Возмещаются прямые убытки от физической потери или повреждения.',
            'lead_en'       => 'Property of enterprises and organizations regardless of ownership. Direct losses from physical loss or damage are covered.',
            'highlights_uz' => "Binolar va inshootlar\nUskunalar, mebel, ofis jihozlari\nTabiiy ofatlar va o‘g‘irlik",
            'highlights_ru' => "Здания и сооружения\nОборудование, мебель, оргтехника\nСтихийные бедствия и кража",
            'highlights_en' => "Buildings and structures\nEquipment, furniture, office equipment\nNatural disasters and theft",
            'covers_uz'     => "Yong‘in, portlash, chaqmoq urishi\nBo‘ron, jala, do‘l, kuchli qor yoki sovuq\nZilzila, qulash, tuproq cho‘kishi, ko‘chki, sel, yer osti suvlari\nO‘g‘irlik va uchinchi shaxslarning qasddan qilingan noqonuniy harakatlari\nIsitish, suv va kanalizatsiya tarmoqlari avariyalari, ichki santexnika buzilishi\nSamolyot qulashi",
            'covers_ru'     => "Пожар, взрыв, удар молнии\nБуря, ураган, ливень, град, обильный снегопад или заморозки\nЗемлетрясение, обвал, просадка грунта, оползень, сель, действие подпочвенных вод\nКража со взломом и умышленные неправомерные действия третьих лиц\nАварии отопительной системы, водопроводных и канализационных сетей, прорыв внутренних сантехнических систем\nПадение летательных аппаратов",
            'covers_en'     => "Fire, explosion, lightning strike\nStorm, hurricane, downpour, hail, heavy snowfall or frost\nEarthquake, collapse, subsidence, landslide, mudflow, groundwater\nBurglary and intentional illegal actions of third parties\nAccidents of heating, water and sewer networks, break-through of internal plumbing\nFalling aircraft",
            'terms_uz'      => "obyekt | Binolar, inshootlar, uskunalar, mebel, ofis jihozlari va boshqa mol-mulk.\nzarar | Jismoniy yo‘qotish yoki shikastlanishdan ko‘rilgan to‘g‘ridan-to‘g‘ri zarar qoplanadi.",
            'terms_ru'      => "объект | Здания, сооружения, оборудование, мебель, оргтехника и другое имущество.\nущерб | Возмещаются прямые убытки от физической потери или повреждения имущества.",
            'terms_en'      => "object | Buildings, structures, equipment, furniture, office equipment and other property.\nloss | Direct losses from physical loss of or damage to the property are covered.",
            'note_uz'       => null,
            'note_ru'       => null,
            'note_en'       => null,
        ],
        [
            'slug'          => 'collective',
            'audience'      => 'business',
            'title_uz'      => 'Kollektiv baxtsiz hodisalardan sug‘urta',
            'title_ru'      => 'Коллективное страхование от несчастных случаев',
            'title_en'      => 'Collective accident insurance',
            'kicker_uz'     => 'Yuridik shaxslar uchun',
            'kicker_ru'     => 'Для юридических лиц',
            'kicker_en'     => 'For legal entities',
            'lead_uz'       => 'Xodimlaringiz sug‘urta davrida baxtsiz hodisalardan sutka davomida yoki faqat ish vaqtida himoyalanadi.',
            'lead_ru'       => 'Ваши работники защищены от несчастных случаев 24 часа в сутки или только в рабочее время в течение периода страхования.',
            'lead_en'       => 'Your employees are covered against accidents 24 hours a day or during working hours only, throughout the insurance period.',
            'highlights_uz' => "24/7 yoki ish vaqtida\nButun jamoa bitta shartnomada\nNogironlikda 60–100%",
            'highlights_ru' => "24/7 или в рабочее время\nВесь коллектив в одном договоре\n60–100% при инвалидности",
            'highlights_en' => "24/7 or working hours\nThe whole team in one contract\n60–100% for disability",
            'covers_uz'     => "Travmatik jarohatlar va sog‘liq buzilishlari\nMehnat qobiliyatini to‘liq yo‘qotish (nogironlik)\nXodimning vafoti",
            'covers_ru'     => "Травматические повреждения и иные расстройства здоровья\nПостоянная полная утрата трудоспособности (инвалидность)\nСмерть работника",
            'covers_en'     => "Traumatic injuries and other health disorders\nPermanent total loss of working capacity (disability)\nDeath of the employee",
            'terms_uz'      => "jadval | Jarohatda — «Sug‘urta to‘lovlari jadvali»ga ko‘ra sug‘urta summasining foizi.\n100% | Vafot etganda va 1-guruh nogironlikda.\n75% | 2-guruh nogironlikda.\n60% | 3-guruh nogironlikda.",
            'terms_ru'      => "таблица | При травме — процент от страховой суммы по «Таблице страховых выплат».\n100% | При смерти и инвалидности 1 группы.\n75% | При инвалидности 2 группы.\n60% | При инвалидности 3 группы.",
            'terms_en'      => "table | Injury — a percentage of the sum insured under the “Table of insurance payments”.\n100% | Death and group 1 disability.\n75% | Group 2 disability.\n60% | Group 3 disability.",
            'note_uz'       => 'Bir xodimga davr ichidagi to‘lovlar uning sug‘urta summasidan oshmaydi. To‘lovlar xodim (merosxo‘r yoki naf oluvchi)ning bank hisobiga o‘tkaziladi.',
            'note_ru'       => 'Общая сумма выплат одному работнику за период страхования не превышает его страховую сумму. Выплаты перечисляются на лицевой счёт застрахованного (наследника или выгодоприобретателя).',
            'note_en'       => 'Total payments to one employee during the insurance period cannot exceed their sum insured. Payments go to the bank account of the insured (heir or beneficiary).',
        ],
    ];
}
