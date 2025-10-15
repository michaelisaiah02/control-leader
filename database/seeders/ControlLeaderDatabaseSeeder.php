<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ControlLeaderDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            ControlLeaderUserSeeder::class,
            QuestionSeeder::class,
            ScheduleSeeder::class,
            OperatorBackfillSeeder::class,
            // Panggil seeder lain di sini jika ada, misal ScheduleSeeder
        ]);
    }
}
