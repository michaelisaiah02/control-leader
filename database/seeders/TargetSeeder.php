<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('targets')->insert([
            ['report' => 'consistency_supervisor', 'value' => 100.00, 'created_at' => now(), 'updated_at' => now()],
            ['report' => 'consistency_leader', 'value' => 100.00, 'created_at' => now(), 'updated_at' => now()],
            ['report' => 'score_supervisor', 'value' => 100.00, 'created_at' => now(), 'updated_at' => now()],
            ['report' => 'score_leader', 'value' => 100.00, 'created_at' => now(), 'updated_at' => now()],
            ['report' => 'score_operator', 'value' => 100.00, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
