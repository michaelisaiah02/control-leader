<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unitList = [
            ['symbol' => 'Kg', 'name' => 'Kilogram'],
            ['symbol' => 'g', 'name' => 'Gram'],
            ['symbol' => 'm', 'name' => 'Meter'],
            ['symbol' => 'cm', 'name' => 'Centimeter'],
            ['symbol' => 'mm', 'name' => 'Millimeter'],
            ['symbol' => '°C', 'name' => 'Celsius'],
        ];

        foreach ($unitList as $unit) {
            Unit::create($unit);
        }
    }
}
