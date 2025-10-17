<?php

namespace Database\Seeders;

use App\Models\ControlLeader\ScheduleDetail;
use App\Models\ControlLeader\SchedulePlan;
use App\Models\ControlLeader\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        // Ambil semua leader dan supervisor
        $leaders = User::where('role', 'leader')->get();
        $supervisor = User::where('role', 'supervisor')->orderBy('id')->first();

        if ($leaders->isEmpty() || !$supervisor) {
            $this->command->warn('⚠️ Tidak menemukan user Leader/Supervisor. Pastikan ControlLeaderUserSeeder jalan.');

            return;
        }

        // ========== Plans ==========
        // Buat plan LCO untuk setiap leader
        $planLCOs = [];
        foreach ($leaders as $leader) {
            $planLCOs[] = SchedulePlan::firstOrCreate(
                [
                    'type' => 'leader_checks_operator',
                    'scheduler_id' => $leader->id,
                    'month' => $today->format('m'),
                    'year' => $today->format('Y'),
                ]
            );
        }

        $planSCL = SchedulePlan::firstOrCreate(
            ['type' => 'supervisor_checks_leader'],
            [
                'scheduler_id' => $supervisor->id,
                'month' => $today->format('m'),
                'year' => $today->format('Y'),
            ]
        );

        // ========== Details (hari ini) ==========
        // LCO: evaluator = Leader, target = random operator
        $operators = User::where('role', 'operator')->inRandomOrder()->take(2)->get();

        // Use the first plan from the array
        $firstPlanLCO = $planLCOs[0] ?? null;

        if ($firstPlanLCO) {
            foreach ($operators as $operator) {
                ScheduleDetail::firstOrCreate(
                    [
                        'schedule_plan_id' => $firstPlanLCO->id,
                        'scheduled_date' => $today->toDateString(),
                        'target_user_id' => $operator->id,
                    ],
                    [
                        'division' => 'Finishing',
                    ]
                );
            }
        }

        // SCL: evaluator = Supervisor, target = random leader
        $randomLeader = User::where('role', 'leader')->inRandomOrder()->first();

        if ($randomLeader) {
            ScheduleDetail::firstOrCreate(
                [
                    'schedule_plan_id' => $planSCL->id,
                    'scheduled_date' => $today->toDateString(),
                    'target_user_id' => $randomLeader->id,
                ]
            );
        }

        $this->command->info('✅ ScheduleSeeder selesai: plan & detail untuk hari ini siap.');
    }
}
