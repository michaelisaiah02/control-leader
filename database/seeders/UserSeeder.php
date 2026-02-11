<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = '00000';

        // Helper buat bikin ID 5 digit
        $makeID = fn($n) => str_pad($n, 5, '0', STR_PAD_LEFT);

        // Ambil daftar department_id dan division_id yang ada
        $departmentIds = DB::table('departments')->pluck('id')->toArray();
        $divisionIds = DB::table('divisions')->pluck('id')->toArray();

        if (empty($departmentIds)) {
            throw new \Exception('No departments found. Please run DepartmentSeeder first.');
        }

        $users = [
            [
                'employeeID' => $makeID(1),
                'name' => 'Admin System',
                'role' => 'admin',
                'department_id' => null,
                'division_id' => null,
                'can_login' => true,
                'password' => Hash::make($password),
            ],
            [
                'employeeID' => $makeID(2),
                'name' => 'Guest Viewer',
                'role' => 'guest',
                'department_id' => null,
                'division_id' => null,
                'can_login' => true,
                'password' => Hash::make($password),
            ],
            [
                'employeeID' => $makeID(3),
                'name' => 'Supervisor Deni',
                'role' => 'supervisor',
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'division_id' => null,
                'can_login' => true,
                'password' => Hash::make($password),
            ],
            [
                'employeeID' => 24556,
                'name' => fake('id_ID')->name(),
                'role' => 'leader',
                'superior_id' => $makeID(3),
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'division_id' => null,
                'can_login' => true,
                'password' => Hash::make($password),
            ],
            [
                'employeeID' => 12025,
                'name' => fake('id_ID')->name(),
                'role' => 'leader',
                'superior_id' => $makeID(3),
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'division_id' => null,
                'can_login' => true,
                'password' => Hash::make($password),
            ],
            // === Operator List ===
            [
                'employeeID' => $makeID(100),
                'name' => fake('id_ID')->name(),
                'role' => 'operator',
                'department_id' => null,
                'division_id' => $divisionIds[array_rand($divisionIds)],
                'superior_id' => $makeID(24556), // Leader Rina
                'can_login' => false,
                'password' => Hash::make(Str::random(10)), // dummy
            ],
            [
                'employeeID' => $makeID(101),
                'name' => fake('id_ID')->name(),
                'role' => 'operator',
                'department_id' => null,
                'division_id' => $divisionIds[array_rand($divisionIds)],
                'superior_id' => $makeID(24556), // Leader Rina
                'can_login' => false,
                'password' => Hash::make(Str::random(10)),
            ],
            [
                'employeeID' => $makeID(102),
                'name' => fake('id_ID')->name(),
                'role' => 'operator',
                'department_id' => null,
                'division_id' => $divisionIds[array_rand($divisionIds)],
                'superior_id' => $makeID(24556), // Leader Rina
                'can_login' => false,
                'password' => Hash::make(Str::random(10)),
            ],
            [
                'employeeID' => $makeID(103),
                'name' => fake('id_ID')->name(),
                'role' => 'operator',
                'department_id' => null,
                'division_id' => $divisionIds[array_rand($divisionIds)],
                'superior_id' => $makeID(12025), // Leader Fajar
                'can_login' => false,
                'password' => Hash::make(Str::random(10)),
            ],
            [
                'employeeID' => $makeID(104),
                'name' => fake('id_ID')->name(),
                'role' => 'operator',
                'department_id' => null,
                'division_id' => $divisionIds[array_rand($divisionIds)],
                'superior_id' => $makeID(12025), // Leader Fajar
                'can_login' => false,
                'password' => Hash::make(Str::random(10)),
            ],
        ];

        foreach ($users as $u) {
            DB::table('users')->insert([
                'name' => $u['name'],
                'employeeID' => $u['employeeID'],
                'department_id' => $u['department_id'],
                'division_id' => $u['division_id'],
                'superior_id' => $u['superior_id'] ?? null,
                'password' => $u['password'],
                'role' => $u['role'],
                'can_login' => $u['can_login'],
                'is_active' => true,
                'control_session_id' => null,
                'cl_in_progress' => false,
                'cl_last_ping' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
