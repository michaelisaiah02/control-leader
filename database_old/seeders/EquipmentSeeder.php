<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $equipmentList = [
            ['type_id' => 'TIM', 'name' => 'Timbangan'],
            ['type_id' => 'CAL', 'name' => 'Caliper'],
            ['type_id' => 'MIC', 'name' => 'Micrometer'],
        ];

        foreach ($equipmentList as $equipment) {
            Equipment::create($equipment);
        }
    }
}
