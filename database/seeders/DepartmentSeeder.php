<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Division;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $hose = Department::updateOrCreate(['name' => 'Hose']);
        $extrude = Department::updateOrCreate(['name' => 'Extrude']);

        Division::updateOrCreate(['department_id' => $hose->id, 'name' => 'Finishing']);
        Division::updateOrCreate(['department_id' => $hose->id, 'name' => 'Packing']);

        Division::updateOrCreate(['department_id' => $extrude->id, 'name' => 'Milling']);
        Division::updateOrCreate(['department_id' => $extrude->id, 'name' => 'Prepping']);
    }
}
