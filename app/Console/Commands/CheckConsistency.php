<?php

namespace App\Console\Commands;

use App\Models\Checksheet;
use App\Models\ConsistencyProblem;
use App\Models\ScheduleDetail;
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
        // Ambil checksheet Supervisor yang disubmit kemarin
        $checksheetsKemarin = Checksheet::with('schedulePlan')->whereDate('created_at', $kemarin)->get();

        foreach ($checksheetsKemarin as $cs) {
            $plan = $cs->schedulePlan;
            $roleType = $plan->type === 'leader_checks_operator' ? 'leader' : 'supervisor';

            // Leader di-skip karena udah dihukum real-time kemaren pas ngisi form
            if ($roleType === 'leader') {
                continue;
            }

            // ⚡ Logic Rentang Tanggal Supervisor ⚡
            $statusWaktu = $this->cekStatusSupervisor($kemarin, $plan->id, $cs->target);

            if ($statusWaktu === 'Late' || $statusWaktu === 'Advanced') {
                ConsistencyProblem::create([
                    'user_id' => $plan->scheduler_id,
                    'inferior_id' => $cs->target,
                    'role_type' => 'supervisor',
                    'remark' => $statusWaktu,
                    // 'problem'     => "Pengisian checksheet pada " . Carbon::parse($kemarin)->format('d M Y') . " berada di luar rentang jadwal aktif (Status: {$statusWaktu}).",
                    'problem' => $statusWaktu === 'Late' ? 'Checksheet terlambat diisi' : 'Checksheet diisi lebih cepat dari schedule',
                    'status' => 'open',
                    'due_date' => Carbon::today()->addDays(2), // Otomatis H+2
                ]);
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
                // LEADER: Cek strict apakah dia ngisi di hari itu?
                $punyaChecksheet = Checksheet::whereHas('schedulePlan', fn ($q) => $q->where('scheduler_id', $auditorId))
                    ->where('target', $auditeeId)
                    ->whereDate('created_at', $mingguLalu)
                    ->exists();

                if (! $punyaChecksheet) {
                    $this->buatProblemMiss($sd, $roleType, $auditorId, $auditeeId, 'Tidak mengisi checksheet');
                }
            } else {
                // SUPERVISOR: Cek per Rentang (Block)
                // Pastikan H-7 ini adalah HARI TERAKHIR dari rentang jadwalnya (biar nggak di-generate dobel tiap hari)
                $besok = Carbon::parse($mingguLalu)->addDay()->format('Y-m-d');
                $isEndOfBlock = ! ScheduleDetail::where('schedule_plan_id', $sd->schedule_plan_id)
                    ->where('target_user_id', $auditeeId)
                    ->where('scheduled_date', $besok)
                    ->exists();

                if ($isEndOfBlock) {
                    // Tarik mundur tanggalnya buat nyari awal rentang
                    $startOfBlock = Carbon::parse($mingguLalu);
                    while (ScheduleDetail::where('schedule_plan_id', $sd->schedule_plan_id)
                        ->where('target_user_id', $auditeeId)
                        ->where('scheduled_date', $startOfBlock->copy()->subDay()->format('Y-m-d'))
                        ->exists()
                    ) {
                        $startOfBlock->subDay();
                    }

                    // Cek apakah ada pengisian di dalam rentang tersebut?
                    $punyaChecksheet = Checksheet::whereHas('schedulePlan', fn ($q) => $q->where('scheduler_id', $auditorId))
                        ->where('target', $auditeeId)
                        ->whereBetween('created_at', [
                            $startOfBlock->startOfDay(),
                            Carbon::yesterday()->endOfDay(),
                        ])->exists();

                    if (! $punyaChecksheet) {
                        $this->buatProblemMiss($sd, $roleType, $auditorId, $auditeeId, 'Tidak mengisi checksheet');
                    }
                }
            }
        }

        $this->info('Pengecekan konsistensi selesai! ✨');
    }

    // Helper nembak ke DB biar kode di atas rapi
    private function buatProblemMiss($sd, $roleType, $auditorId, $auditeeId, $pesanProblem)
    {
        ConsistencyProblem::create([
            'user_id' => $auditorId,
            'inferior_id' => $auditeeId,
            'role_type' => $roleType,
            'remark' => 'Miss',
            'schedule_detail_id' => $sd->id,
            'problem' => $pesanProblem,
            'status' => 'open',
            'due_date' => Carbon::today()->addDays(2),
        ]);
    }

    // 🧠 OTAK UTAMA: Algoritma Pencari Rentang (Block) Tanggal
    private function cekStatusSupervisor($tanggalIsi, $planId, $targetId)
    {
        // 1. Ambil semua jadwal untuk plan & target ini
        $jadwals = ScheduleDetail::where('schedule_plan_id', $planId)
            ->where('target_user_id', $targetId)
            ->orderBy('scheduled_date')
            ->pluck('scheduled_date')
            ->map(fn ($d) => Carbon::parse($d)->startOfDay())
            ->toArray();

        if (empty($jadwals)) {
            return 'Normal';
        }

        // 2. Kelompokkan jadwal beruntun jadi "Block" (Misal: 1-4 dan 6-10 dipisah)
        $blocks = [];
        $currentBlock = [];

        foreach ($jadwals as $date) {
            if (empty($currentBlock)) {
                $currentBlock[] = $date;
            } else {
                $lastDate = end($currentBlock);
                if ($date->diffInDays($lastDate) == 1) { // Kalau harinya nyambung
                    $currentBlock[] = $date;
                } else { // Kalau ada jeda / gap
                    $blocks[] = $currentBlock;
                    $currentBlock = [$date];
                }
            }
        }
        if (! empty($currentBlock)) {
            $blocks[] = $currentBlock;
        }

        // 3. Tentukan tanggal isi masuk ke block mana
        $isiDate = Carbon::parse($tanggalIsi)->startOfDay();
        $minDistance = PHP_INT_MAX;
        $status = 'Normal';

        foreach ($blocks as $block) {
            $start = $block[0];
            $end = end($block);

            // Kalau ngisi tepat di dalam rentang = Normal
            if ($isiDate->between($start, $end)) {
                return 'Normal';
            }

            // Hitung mana block yang paling deket (biar sistem tau ini late dari jadwal sebelumnya, atau advanced dari jadwal berikutnya)
            $distStart = $isiDate->diffInDays($start);
            $distEnd = $isiDate->diffInDays($end);
            $dist = min($distStart, $distEnd);

            if ($dist < $minDistance) {
                $minDistance = $dist;
                if ($isiDate->lessThan($start)) {
                    $status = 'Advanced'; // Ngisi sebelum jadwal terdekat mulai
                } elseif ($isiDate->greaterThan($end)) {
                    $status = 'Late'; // Ngisi setelah jadwal terdekat lewat
                }
            }
        }

        return $status;
    }
}
