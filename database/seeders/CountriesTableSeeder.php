<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CountriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $response = Http::get('https://impexonline.uz/ords/ins/travel/countries');

        if ($response->successful()) {
            $countries = $response->json()['items'];

            foreach ($countries as $country) {
                DB::table('countries')->updateOrInsert(
                    ['id' => $country['id']],
                    [
                        'name_ru' => $country['name1'],
                        'name_uz' => $country['name3'],
                        'name_en' => $country['name_en'] ?? $country['ename'] ?? $country['name_ru'] ?? 'Unknown',
                        'code'    => $country['id'],
                        'active'  => $country['active'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}
