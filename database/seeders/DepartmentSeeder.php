<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ControlLeader\Division;
use App\Models\ControlLeader\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $hose = Department::updateOrCreate(['department_name' => 'Hose']);
        $extrude = Department::updateOrCreate(['department_name' => 'Extrude']);

        Division::updateOrCreate(['department_id' => $hose->id, 'division_name' => 'Finishing']);
        Division::updateOrCreate(['department_id' => $hose->id, 'division_name' => 'Packing']);

        Division::updateOrCreate(['department_id' => $extrude->id, 'division_name' => 'Milling']);
        Division::updateOrCreate(['department_id' => $extrude->id, 'division_name' => 'Prepping']);
    }
}
