<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Travel insurance abroad (ВЗР): its products row, so it gets a home page card and the "Sotuvda" switch */
    public function up(): void
    {
        if (DB::table('products')->where('route', 'travel')->exists()) {
            return;
        }

        DB::table('products')->insert([
            'name_uz'    => 'Chet elga chiquvchilar sug\'urtasi',
            'name_ru'    => 'Страхование выезжающих за рубеж',
            'name_en'    => 'Travel Insurance',
            'desc_uz'    => 'Xorijga safar paytida tibbiy xarajatlarni qoplaydigan sug\'urta (VZR)',
            'desc_ru'    => 'Покрытие медицинских расходов во время поездки за границу (ВЗР)',
            'desc_en'    => 'Medical expenses cover while travelling abroad',
            'route'      => 'travel',
            'icon'       => 'bi bi-airplane-fill',
            'icon_color' => '#0891b2',
            'icon_bg'    => '#cffafe',
            'is_active'  => true,
            'sort_order' => (int) DB::table('products')->max('sort_order') + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('products')->where('route', 'travel')->delete();
    }
};
