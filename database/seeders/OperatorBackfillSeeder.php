<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperatorBackfillSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('mysql_control_leader')->transaction(function () {
            // Ambil distinct operator lama
            $ops = DB::connection('mysql_control_leader')->table('schedule_details')
                ->whereNotNull('target_operator_id')
                ->whereNotNull('target_operator_name')
                ->select('target_operator_id', 'target_operator_name')
                ->distinct()->get();

            foreach ($ops as $op) {
                $exists = DB::connection('mysql_control_leader')->table('users')
                    ->where('employeeID', $op->target_operator_id)->first();
                if ($exists)
                    continue;

                DB::connection('mysql_control_leader')->table('users')->insert([
                    'name' => $op->target_operator_name,
                    'employeeID' => $op->target_operator_id,
                    'role' => 'Operator',
                    'can_login' => false,
                    'is_active' => true,
                    'password' => bcrypt(Str::random(16)), // dummy
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
