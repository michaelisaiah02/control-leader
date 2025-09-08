<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\ControlLeader\User;
use Illuminate\Support\Facades\DB;
use App\Models\ControlLeader\SchedulePlan;
use App\Models\ControlLeader\ScheduleDetail;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        // Ambil 1 supervisor & 1 leader dari seeder user yang sudah ada
        $leader = User::where('role', 'leader')->orderBy('id')->first();
        $supervisor = User::where('role', 'supervisor')->orderBy('id')->first();

        if (!$leader || !$supervisor) {
            $this->command->warn('⚠️ Tidak menemukan user Leader/Supervisor. Pastikan ControlLeaderUserSeeder jalan.');
            return;
        }

        // Ambil 1 department/division yang sudah ada
        // NOTE: PAKAI SALAH SATU baris di bawah sesuai kolom yang kamu punya
        $deptId = DB::connection('mysql_control_leader')->table('departments')->min('id');   // ← kalau tabelnya "departments"
        // $deptId = DB::table('divisions')->min('id');  // ← kalau tabelnya "divisions"

        // ========== Plans ==========
        // type: 'leader_checks_operator' | 'supervisor_checks_leader'
        $planLCO = SchedulePlan::firstOrCreate(
            ['type' => 'leader_checks_operator'],
            [
                'scheduler_id' => $leader->id, // biasanya supervisor yang buat plan LCO
                'month' => $today->format('m'),
                'year' => $today->format('Y')
            ]
        );

        $planSCL = SchedulePlan::firstOrCreate(
            ['type' => 'supervisor_checks_leader'],
            [
                'scheduler_id' => $supervisor->id,
                'month' => $today->format('m'),
                'year' => $today->format('Y')
            ]
        );

        // ========== Details (hari ini) ==========
        // LCO: evaluator = Leader, target operator (tidak punya akun) ⇒ target_user_id = null
        ScheduleDetail::firstOrCreate(
            [
                'schedule_plan_id' => $planLCO->id,
                'scheduled_date' => $today->toDateString(),
            ],
            [
                'target_operator_id' => '57259', // contoh ID operator (input manual
            ]
        );

        // SCL: evaluator = Supervisor, target = Leader (punya akun)
        ScheduleDetail::firstOrCreate(
            [
                'schedule_plan_id' => $planSCL->id,
                'scheduled_date' => $today->toDateString(),
            ],
            [
                'target_leader_id' => $leader->id,
            ]
        );

        $this->command->info('✅ ScheduleSeeder selesai: plan & detail untuk hari ini siap.');
    }
}
