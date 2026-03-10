<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'provider_vehicle_type_id' => 1,
                'name_uz' => '1200 kub.sm gacha yengil avtomobil',
                'name_ru' => 'Легковые авто до 1200 куб. см.',
                'name_en' => 'Passenger car up to 1200 cc',
            ],
            [
                'provider_vehicle_type_id' => 6,
                'name_uz' => '10 tonnagacha yuk avtomobili',
                'name_ru' => 'Грузовые авто до 10 тонн',
                'name_en' => 'Truck up to 10 tons',
            ],
            [
                'provider_vehicle_type_id' => 9,
                'name_uz' => '20 o‘rindiqdan ortiq avtobus',
                'name_ru' => 'Автобусы с числом мест свыше 20',
                'name_en' => 'Bus with more than 20 seats',
            ],
            [
                'provider_vehicle_type_id' => 15,
                'name_uz' => 'Tramvay, mototsikl, traktor va boshqalar',
                'name_ru' => 'Трамваи, мотоциклы, тракторы и другие',
                'name_en' => 'Tram, motorcycle, tractor and others',
            ],
        ];


        foreach ($types as $type) {
            VehicleType::updateOrCreate(
                ['provider_vehicle_type_id' => $type['provider_vehicle_type_id']],
                array_merge($type, ['is_active' => true])
            );
        }
    }
}
