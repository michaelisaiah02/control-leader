<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ControlLeaderUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = '00000';
        $conn = 'mysql_control_leader';

        // Helper buat bikin ID 5 digit
        $makeID = fn($n) => str_pad($n, 5, '0', STR_PAD_LEFT);

        // Ambil daftar department_id yang ada
        $departmentIds = DB::connection($conn)->table('departments')->pluck('id')->toArray();

        if (empty($departmentIds)) {
            throw new \Exception('No departments found. Please run DepartmentSeeder first.');
        }

        $users = [
            [
                'employeeID' => $makeID(1),
                'name' => 'Admin System',
                'role' => 'admin',
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'can_login' => true,
                'password' => Hash::make($password),
            ],
            [
                'employeeID' => $makeID(2),
                'name' => 'Guest Viewer',
                'role' => 'guest',
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'can_login' => true,
                'password' => Hash::make($password),
            ],
            [
                'employeeID' => $makeID(3),
                'name' => 'Supervisor Deni',
                'role' => 'supervisor',
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'can_login' => true,
                'password' => Hash::make($password),
            ],
            [
                'employeeID' => 24556,
                'name' => 'Leader Rina',
                'role' => 'leader',
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'can_login' => true,
                'password' => Hash::make($password),
            ],
            [
                'employeeID' => 12025,
                'name' => 'Leader Fajar',
                'role' => 'leader',
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'can_login' => true,
                'password' => Hash::make($password),
            ],
            // === Operator List ===
            [
                'employeeID' => $makeID(100),
                'name' => 'Operator Budi',
                'role' => 'operator',
                'department_id' => null,
                'can_login' => false,
                'password' => Hash::make(Str::random(10)), // dummy
            ],
            [
                'employeeID' => $makeID(101),
                'name' => 'Operator Siti',
                'role' => 'operator',
                'department_id' => null,
                'can_login' => false,
                'password' => Hash::make(Str::random(10)),
            ],
            [
                'employeeID' => $makeID(102),
                'name' => 'Operator Andi',
                'role' => 'operator',
                'department_id' => null,
                'can_login' => false,
                'password' => Hash::make(Str::random(10)),
            ],
            [
                'employeeID' => $makeID(103),
                'name' => 'Operator Lina',
                'role' => 'operator',
                'department_id' => null,
                'can_login' => false,
                'password' => Hash::make(Str::random(10)),
            ],
            [
                'employeeID' => $makeID(104),
                'name' => 'Operator Rizky',
                'role' => 'operator',
                'department_id' => null,
                'can_login' => false,
                'password' => Hash::make(Str::random(10)),
            ],
        ];

        foreach ($users as $u) {
            DB::connection($conn)->table('users')->insert([
                'name' => $u['name'],
                'employeeID' => $u['employeeID'],
                'department_id' => $u['department_id'],
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
