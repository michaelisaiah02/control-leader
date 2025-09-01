<?php

namespace Database\Seeders;

use App\Models\ControlLeaderUser;
use App\Models\Department; // Tambahkan ini
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ControlLeaderUserSeeder extends Seeder
{
    public function run(): void
    {
        $hose = Department::where('department_name', 'Hose')->first();
        $extrude = Department::where('department_name', 'Extrude')->first();
        $password = '00000';

        // Admin (tanpa departemen)
        ControlLeaderUser::updateOrCreate(
            ['employeeID' => '10001'],
            ['name' => 'CL Admin', 'password' => Hash::make($password), 'role' => 'admin', 'department_id' => null]
        );
        // Supervisor Dept. Hose
        ControlLeaderUser::updateOrCreate(
            ['employeeID' => '20001'],
            ['name' => 'CL Supervisor Hose', 'password' => Hash::make($password), 'role' => 'supervisor', 'department_id' => $hose->id]
        );
        // Leader Dept. Extrude
        ControlLeaderUser::updateOrCreate(
            ['employeeID' => '12025'],
            ['name' => 'CL Leader Extrude', 'password' => Hash::make($password), 'role' => 'leader', 'department_id' => $extrude->id]
        );
        // Guest (tanpa departemen)
        ControlLeaderUser::updateOrCreate(
            ['employeeID' => '90001'],
            ['name' => 'CL Guest', 'password' => Hash::make($password), 'role' => 'guest', 'department_id' => null]
        );
    }
}
