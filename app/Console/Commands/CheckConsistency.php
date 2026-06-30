<?php

namespace App\Console\Commands;

use App\Models\Checksheet;
use App\Models\ConsistencyProblem;
use App\Models\ScheduleDetail;
use App\Models\User; // 🔥 Jangan lupa import model User
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:consistency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek problem konsistensi Supervisor (Rentang Tanggal) & Miss';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $kemarin = Carbon::yesterday()->format('Y-m-d');
        $mingguLalu = Carbon::today()->subDays(7)->format('Y-m-d');

        $this->info("Gas! Ngecek Supervisor H+1 ({$kemarin}) & Miss H-7 ({$mingguLalu})...");

        // ==========================================
        // 1. CEK LATE/ADVANCED SUPERVISOR (RENTANG TANGGAL)
        // ==========================================
        $checksheetsKemarin = Checksheet::with('schedulePlan')->whereDate('created_at', $kemarin)->get();

        foreach ($checksheetsKemarin as $cs) {
            $plan = $cs->schedulePlan;
            $roleType = $plan->type === 'leader_checks_operator' ? 'leader' : 'supervisor';

            if ($roleType === 'leader') {
                continue;
            }

            $statusWaktu = $this->cekStatusSupervisor($kemarin, $plan->id, $cs->target);

            if ($statusWaktu === 'Late' || $statusWaktu === 'Advanced') {
                // 🔥 Tarik data target buat ngambil nama
                $targetUser = User::where('employeeID', $cs->target)->first();
                $targetName = $targetUser ? $targetUser->name : 'Unknown';

                $baseProblem = $statusWaktu === 'Late' ? 'Checksheet terlambat diisi' : 'Checksheet diisi lebih cepat dari schedule';

                ConsistencyProblem::updateOrCreate(
                    [
                        'schedule_detail_id' => $cs->schedule_detail_id,
                    ],
                    [
                        'user_id' => $plan->scheduler_id,
                        'inferior_id' => $cs->target,
                        'role_type' => 'supervisor',
                        'remark' => $statusWaktu,
                        'problem' => "{$baseProblem} - {$cs->target} - {$targetName}",
                        'status' => 'open', // Bakal mereset status ke open kalo ditimpa
                        'due_date' => Carbon::today()->addDays(2),
                    ]
                );
            }
        }

        // ==========================================
        // 2. CEK MISS (LEADER HARIAN vs SUPERVISOR RENTANG)
        // ==========================================
        $schedulesMingguLalu = ScheduleDetail::with('plan')->where('scheduled_date', $mingguLalu)->get();

        foreach ($schedulesMingguLalu as $sd) {
            $roleType = $sd->plan->type === 'leader_checks_operator' ? 'leader' : 'supervisor';
            $auditorId = $sd->plan->scheduler_id;
            $auditeeId = $sd->target_user_id;

            if ($roleType === 'leader') {
                $punyaChecksheet = Checksheet::whereHas('schedulePlan', fn($q) => $q->where('scheduler_id', $auditorId))
                    ->where('target', $auditeeId)
                    ->whereDate('created_at', $mingguLalu)
                    ->exists();

                if (! $punyaChecksheet) {
                    $this->buatProblemMiss($sd, $roleType, $auditorId, $auditeeId, 'Checksheet tidak diisi');
                }
            } else {
                $besok = Carbon::parse($mingguLalu)->addDay()->format('Y-m-d');
                $isEndOfBlock = ! ScheduleDetail::where('schedule_plan_id', $sd->schedule_plan_id)
                    ->where('target_user_id', $auditeeId)
                    ->where('scheduled_date', $besok)
                    ->exists();

                if ($isEndOfBlock) {
                    $startOfBlock = Carbon::parse($mingguLalu);
                    while (ScheduleDetail::where('schedule_plan_id', $sd->schedule_plan_id)
                        ->where('target_user_id', $auditeeId)
                        ->where('scheduled_date', $startOfBlock->copy()->subDay()->format('Y-m-d'))
                        ->exists()
                    ) {
                        $startOfBlock->subDay();
                    }

                    $punyaChecksheet = Checksheet::whereHas('schedulePlan', fn($q) => $q->where('scheduler_id', $auditorId))
                        ->where('target', $auditeeId)
                        ->whereBetween('created_at', [
                            $startOfBlock->startOfDay(),
                            Carbon::yesterday()->endOfDay(),
                        ])->exists();

                    if (! $punyaChecksheet) {
                        $this->buatProblemMiss($sd, $roleType, $auditorId, $auditeeId, 'Checksheet tidak diisi');
                    }
                }
            }
        }

        $this->info('Pengecekan konsistensi selesai! ✨');
    }

    // Helper nembak ke DB
    private function buatProblemMiss($sd, $roleType, $auditorId, $auditeeId, $pesanProblem)
    {
        // 🔥 Tarik data target buat ngambil nama
        $targetUser = User::where('employeeID', $auditeeId)->first();
        $targetName = $targetUser ? $targetUser->name : 'Unknown';

        ConsistencyProblem::updateOrCreate(
            [
                'schedule_detail_id' => $sd->id,
            ],
            [
                'user_id' => $auditorId,
                'inferior_id' => $auditeeId,
                'role_type' => $roleType,
                'remark' => 'Miss',
                'problem' => "{$pesanProblem} - {$auditeeId} - {$targetName}",
                'status' => 'open',
                'due_date' => Carbon::today()->addDays(2),
            ]
        );
    }

    // Algoritma Pencari Rentang (Block) Tanggal
    private function cekStatusSupervisor($tanggalIsi, $planId, $targetId)
    {
        $jadwals = ScheduleDetail::where('schedule_plan_id', $planId)
            ->where('target_user_id', $targetId)
            ->orderBy('scheduled_date')
            ->pluck('scheduled_date')
            ->map(fn($d) => Carbon::parse($d)->startOfDay())
            ->toArray();

        if (empty($jadwals)) {
            return 'Normal';
        }

        $blocks = [];
        $currentBlock = [];

        foreach ($jadwals as $date) {
            if (empty($currentBlock)) {
                $currentBlock[] = $date;
            } else {
                $lastDate = end($currentBlock);
                if ($date->diffInDays($lastDate) == 1) {
                    $currentBlock[] = $date;
                } else {
                    $blocks[] = $currentBlock;
                    $currentBlock = [$date];
                }
            }
        }
        if (! empty($currentBlock)) {
            $blocks[] = $currentBlock;
        }

        $isiDate = Carbon::parse($tanggalIsi)->startOfDay();
        $minDistance = PHP_INT_MAX;
        $status = 'Normal';

        foreach ($blocks as $block) {
            $start = $block[0];
            $end = end($block);

            if ($isiDate->between($start, $end)) {
                return 'Normal';
            }

            $distStart = $isiDate->diffInDays($start);
            $distEnd = $isiDate->diffInDays($end);
            $dist = min($distStart, $distEnd);

            if ($dist < $minDistance) {
                $minDistance = $dist;
                if ($isiDate->lessThan($start)) {
                    $status = 'Advanced';
                } elseif ($isiDate->greaterThan($end)) {
                    $status = 'Late';
                }
            }
        }

        return $status;
    }
}
