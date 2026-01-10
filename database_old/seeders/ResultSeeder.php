<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultSeeder extends Seeder
{
    public function run()
    {
        // Fetch all master lists
        $masterLists = DB::table('master_lists')->get();

        foreach ($masterLists as $masterList) {
            DB::table('results')->insert([
                'id_num' => $masterList->id_num,
                'calibration_date' => now(),
                'calibrator_equipment' => null, // Assuming no calibrator equipment initially
                'param_01' => rand(1, 100) / 10 ?: 0.1,
                'param_02' => rand(1, 100) / 10 ?: 0.1,
                'param_03' => rand(1, 100) / 10 ?: 0.1,
                'param_04' => rand(1, 100) / 10 ?: 0.1,
                'param_05' => rand(1, 100) / 10 ?: 0.1,
                'param_06' => rand(1, 100) / 10 ?: 0.1,
                'param_07' => rand(1, 100) / 10 ?: 0.1,
                'param_08' => rand(1, 100) / 10 ?: 0.1,
                'param_09' => rand(1, 100) / 10 ?: 0.1,
                'param_10' => rand(1, 100) / 10 ?: 0.1,
                'judgement' => ['OK', 'NG', 'Disposal'][array_rand(['OK', 'NG', 'Disposal'])],
                'created_by' => DB::table('users')->inRandomOrder()->value('employeeID'),
                'certificate' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
