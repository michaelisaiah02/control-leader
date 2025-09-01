<?php

namespace Database\Seeders;

use App\Models\ControlLeaderUser;
use App\Models\ScheduleDetail;
use App\Models\SchedulePlan;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil contoh leader
        $leader = ControlLeaderUser::where('role', 'leader')->first();

        // 1. Buat Rencana Jadwal Induk
        $plan = SchedulePlan::create([
            'scheduler_id' => $leader->id,
            'month' => 8, // Agustus
            'year' => 2025,
            'type' => 'leader_checks_operator',
        ]);

        // 2. Siapkan data detail jadwal
        $schedules = [
            // Grup 1: Operator A & D
            ['operator_id' => '00001', 'dates' => [2, 6, 11, 19, 25]],
            ['operator_id' => '00009', 'dates' => [2, 6, 11, 19, 25]],
            // Grup 2: Operator C & E
            ['operator_id' => '00003', 'dates' => [1, 5, 8, 17, 29]],
            ['operator_id' => '00005', 'dates' => [1, 5, 8, 17, 29]],
            // Grup 3: Operator B
            ['operator_id' => '00002', 'dates' => [4, 12, 18, 23, 31]],
        ];

        // 3. Masukkan semua detail ke database
        foreach ($schedules as $schedule) {
            foreach ($schedule['dates'] as $day) {
                ScheduleDetail::create([
                    'schedule_plan_id' => $plan->id,
                    'target_operator_id' => $schedule['operator_id'],
                    'schedule_date' => "2025-08-{$day}",
                ]);
            }
        }
    }
}
