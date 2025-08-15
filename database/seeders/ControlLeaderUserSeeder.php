<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\ControlLeaderUser; // <-- Gunakan model yang benar

class ControlLeaderUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data login password akan sama untuk semua user agar mudah diingat
        $password = '00000';

        // Buat user Admin
        ControlLeaderUser::updateOrCreate(
            ['employeeID' => '10001'], // Kunci unik untuk mencari/membuat
            [
                'name' => 'CL Admin',
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );

        // Buat user Supervisor
        ControlLeaderUser::updateOrCreate(
            ['employeeID' => '20001'],
            [
                'name' => 'CL Supervisor',
                'password' => Hash::make($password),
                'role' => 'supervisor',
            ]
        );

        // Buat user Leader
        ControlLeaderUser::updateOrCreate(
            ['employeeID' => '30001'],
            [
                'name' => 'CL Leader',
                'password' => Hash::make($password),
                'role' => 'leader',
            ]
        );

        // Buat user Guest
        ControlLeaderUser::updateOrCreate(
            ['employeeID' => '90001'],
            [
                'name' => 'CL Guest',
                'password' => Hash::make($password),
                'role' => 'guest',
            ]
        );
    }
}
