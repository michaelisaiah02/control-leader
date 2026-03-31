<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\ChecksheetAnswer;
use App\Models\Question;
use App\Models\ScheduleDetail;
use App\Models\SchedulePlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChecksheetController extends Controller
{
    // ==========================================
    // 1. UTILITIES & HELPERS
    // ==========================================

    private function packageFor(string $phase, string $direction): string
    {
        if ($direction === 'supervisor_checks_leader' || $phase === 'leader') {
            return 'leader';
        }

        return match ($phase) {
            'awal_shift' => 'awal_shift',
            'saat_bekerja' => 'saat_bekerja',
            'setelah_istirahat' => 'setelah_istirahat',
            'akhir_shift' => 'akhir_shift',
            default => 'awal_shift'
        };
    }

    private function directionFor($me)
    {
        return $me->role === 'supervisor' ? 'supervisor_checks_leader' : 'leader_checks_operator';
    }

    private function getEmployeeId(string $uid): string
    {
        // 1. Prioritaskan cari berdasarkan string employeeID yang mutlak ('00001' tidak akan jadi 1)
        $u = User::where('employeeID', $uid)->first();

        // 2. Kalau bener-bener nggak ketemu, baru kita coba cari berdasarkan Primary Key (id)
        if (! $u && is_numeric($uid)) {
            $u = User::find($uid);
        }

        if (! $u) {
            return $uid;
        }

        return $u->employeeID ?: ($u->role === 'leader' ? 'LDR' . $u->id : 'OP' . $u->id);
    }

    // ==========================================
    // 2. PAGE RENDERING (PART A & B)
    // ==========================================

    public function createPartA(Request $req)
    {
        $me = auth()->user();
        $phase = $req->query('type', 'awal_shift');
        $direction = $this->directionFor($me);

        // 🔥 KUNCI UTAMA: Pake format Carbon murni di jam 00:00:00
        $todayDate = now()->startOfDay();

        $plan = SchedulePlan::where('scheduler_id', $me->employeeID)
            ->where('type', $direction)
            ->where('year', date('Y'))
            ->where('month', date('m'))
            ->first();

        if (! $plan) {
            return back()->with('error', 'Belum ada Schedule Plan untuk Anda bulan ini.');
        }

        $completedDetailIds = Checksheet::where('schedule_plan_id', $plan->id)
            ->where('phase', $phase)
            ->where('replacement', false)
            ->whereNotNull('schedule_detail_id')
            ->pluck('schedule_detail_id')
            ->toArray();

        // 🔥 Balikin filter whereNotNull biar relasi aman
        $details = ScheduleDetail::where('schedule_plan_id', $plan->id)
            ->whereNotNull('target_user_id')
            ->whereHas('targetUser', function ($query) use ($me) {
                $query->where('superior_id', $me->employeeID);
            })
            ->with('targetUser')
            ->orderBy('target_user_id')
            ->orderBy('scheduled_date')
            ->get();

        $grouped = $details->groupBy('target_user_id');
        $options = [];
        $targetLabel = $direction === 'supervisor_checks_leader' ? 'ID & Nama Leader' : 'ID & Nama Operator';

        foreach ($grouped as $userId => $userDates) {
            if ($direction === 'supervisor_checks_leader') {
                // ==========================================
                // LOGIC SUPERVISOR (Cluster)
                // ==========================================
                $blocks = [];
                $currentBlock = [];

                foreach ($userDates as $d) {
                    if (empty($currentBlock)) {
                        $currentBlock[] = $d;
                    } else {
                        $lastIdx = count($currentBlock) - 1;
                        $lastDate = Carbon::parse($currentBlock[$lastIdx]->scheduled_date)->startOfDay();
                        $currDate = Carbon::parse($d->scheduled_date)->startOfDay();

                        if ($lastDate->diffInDays($currDate) == 1) {
                            $currentBlock[] = $d;
                        } else {
                            $blocks[] = $currentBlock;
                            $currentBlock = [$d];
                        }
                    }
                }
                if (!empty($currentBlock)) {
                    $blocks[] = $currentBlock;
                }

                foreach ($blocks as $block) {
                    $firstDetailId = $block[0]->id;
                    $startDate = Carbon::parse($block[0]->scheduled_date)->startOfDay();

                    // 🔥 Cek pake lte() (Less Than or Equal)
                    if (!in_array($firstDetailId, $completedDetailIds) && $startDate->lte($todayDate)) {
                        $endDate = $block[count($block) - 1]->scheduled_date;
                        $targetUser = $block[0]->targetUser;
                        $div = $block[0]->division;

                        $startFmt = Carbon::parse($block[0]->scheduled_date)->format('d M');
                        $endFmt = Carbon::parse($endDate)->format('d M');
                        $rangeLabel = $startFmt === $endFmt ? "($startFmt)" : "($startFmt - $endFmt)";

                        $options[] = [
                            'value' => "{$firstDetailId}::{$userId}::{$div}",
                            'label' => ($targetUser->employeeID ?: "LDR{$userId}") . ' - ' . $targetUser->name . " " . $rangeLabel,
                        ];
                    }
                }
            } else {
                // ==========================================
                // LOGIC LEADER (Harfiah / Per Hari)
                // ==========================================
                foreach ($userDates as $d) {
                    // 🔥 Jadikan object Carbon startOfDay()
                    $scheduleDate = Carbon::parse($d->scheduled_date)->startOfDay();

                    // 🔥 Pake lte() biar perbandingannya mutlak
                    if (!in_array($d->id, $completedDetailIds) && $scheduleDate->lte($todayDate)) {
                        $targetUser = $d->targetUser;

                        // Proteksi kalau usernya (operator) ternyata dihapus dari database
                        if (!$targetUser) continue;

                        $fmtDate = $scheduleDate->format('d M');

                        // Cek telat atau nggak pake lessThan()
                        $isLate = $scheduleDate->lessThan($todayDate) ? " (Telat: $fmtDate)" : " (Hari ini)";

                        $options[] = [
                            'value' => "{$d->id}::{$userId}::{$d->division}",
                            'label' => ($targetUser->employeeID ?: "OP{$userId}") . ' - ' . $targetUser->name . $isLate,
                        ];
                    }
                }
            }
        }

        // Kalau list beneran kosong, tampilkan alert ini
        if (empty($options)) {
            return back()->with('info', 'Semua jadwal Anda untuk saat ini sudah dikerjakan. Mantap! 🎉');
        }

        // --- SESSION TIMER LOGIC ---
        $sessionKey = "cs_timer_{$plan->id}_{$phase}";
        if (! session()->has($sessionKey)) {
            session()->put($sessionKey, now()->timestamp);
            session()->put('active_checksheet', [
                'plan_id' => $plan->id,
                'phase' => $phase,
            ]);
        }
        $startedAtSeconds = session()->get($sessionKey);

        return view('checksheets.part-a', [
            'phase' => $phase,
            'plan' => $plan,
            'startedAtMs' => $startedAtSeconds * 1000,
            'targetLabel' => $targetLabel,
            'options' => $options,
        ]);
    }

    public function showPartB(Request $req)
    {
        $me = auth()->user();
        $phase = $req->query('type', 'awal_shift');
        $plan = SchedulePlan::findOrFail((int) $req->query('plan'));

        $package = $this->packageFor($phase, $this->directionFor($me));
        $questions = Question::where('package', $package)->where('is_active', true)->orderBy('display_order')->get();

        // --- SESSION TIMER LOGIC ---
        $sessionKey = "cs_timer_{$plan->id}_{$phase}";
        $startedAtSeconds = session()->get($sessionKey, now()->timestamp);

        return view('checksheets.part-b', [
            'phase' => $phase,
            'plan' => $plan,
            'questions' => $questions,
            'startedAtMs' => $startedAtSeconds * 1000,
        ]);
    }

    // ==========================================
    // 3. MAIN STORE LOGIC
    // ==========================================

    public function store(Request $req)
    {
        $phase = $req->query('type');
        $isSpv = $phase === 'leader'; // Flag deteksi Supervisor

        // 1. Dinamis Validation
        $rules = [
            'schedule_plan_id' => 'required|exists:schedule_plans,id',
            'part_a.target' => 'required|string',
            'part_a.division' => 'nullable|string',
            'answers' => 'array',
            'problems' => 'array',
            'countermeasures' => 'array',
        ];

        if ($isSpv) {
            // SPV: Bebas hambatan, ga perlu shift & tetek bengek
            $rules['part_a.shift'] = 'nullable';
            $rules['part_a.attendance'] = 'nullable';
            $rules['part_a.has_replacement'] = 'nullable';
        } else {
            // Operator: Wajib isi lengkap
            $rules['part_a.shift'] = 'required|in:1,2,3';
            $rules['part_a.attendance'] = 'required|in:0,1';
            $rules['part_a.has_replacement'] = 'sometimes|boolean';
        }

        $data = $req->validate($rules);

        $sessionKey = "cs_timer_{$data['schedule_plan_id']}_{$phase}";
        $startedAtSeconds = session()->get($sessionKey);
        $duration = $startedAtSeconds ? (now()->timestamp - $startedAtSeconds) : 0;

        $targetParts = array_pad(explode('::', $data['part_a']['target']), 3, null);
        [$detailId, $uid, $division] = $targetParts;

        $scheduledTargetId = $this->getEmployeeId($uid);
        $detail = ScheduleDetail::find($detailId);
        $divisionFromDetail = $detail?->division ?? $division ?? $data['part_a']['division'];

        // 2. Logic Kehadiran & Pengganti yang Cerdas
        // Kalau SPV, otomatis Hadir (1) dan Gak Ada Pengganti (false)
        $isPresent = $isSpv ? true : ((int) ($data['part_a']['attendance'] ?? 0) === 1);
        $hasReplacement = $isSpv ? false : in_array($data['part_a']['has_replacement'] ?? 0, [1, '1', true, 'true'], true);

        try {
            DB::beginTransaction();

            if ($isPresent) {
                $this->savePresentCase($data, $phase, $duration, $scheduledTargetId, $divisionFromDetail, $detailId);
            } elseif (! $isPresent && ! $hasReplacement) {
                $this->saveAbsentNoReplacementCase($data, $phase, $scheduledTargetId, $divisionFromDetail, $detailId);
            } else {
                $this->saveAbsentWithReplacementCase($data, $phase, $duration, $scheduledTargetId, $divisionFromDetail, $detailId);
            }

            // BERSIHKAN SESSION KARENA UDAH SELESAI
            session()->forget($sessionKey);
            session()->forget([$sessionKey, 'active_checksheet']);
            DB::commit();

            if ($req->ajax() || $req->wantsJson()) {
                session()->flash('success', 'Checksheet berhasil tersimpan.');

                return response()->json(['success' => true, 'redirect' => route('dashboard')]);
            }

            return redirect()->route('dashboard')->with('success', 'Checksheet berhasil tersimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checksheet Store Error: ' . $e->getMessage());

            if ($req->ajax() || $req->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal menyimpan data! ' . $e->getMessage());
        }
    }

    // ==========================================
    // 4. PRIVATE SAVING ACTIONS
    // ==========================================

    private function savePresentCase($data, $phase, $duration, $scheduledTargetId, $division, $detailId)
    {
        $cs = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'schedule_detail_id' => $detailId,
            'phase' => $phase,
            'stopwatch_duration' => $duration,
            'score' => 0,
            'scheduled_target' => $scheduledTargetId,
            'shift' => $data['part_a']['shift'],
            'target' => $scheduledTargetId,
            'division' => $division,
            'attendance' => '1',
            'condition' => $data['part_a']['kondisi'] ?? null,
            'replacement' => false,
            'replacement_of_id' => null,
        ]);
        $this->processAnswers($cs, $data);
    }

    private function saveAbsentNoReplacementCase($data, $phase, $scheduledTargetId, $division, $detailId)
    {
        Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'schedule_detail_id' => $detailId,
            'phase' => $phase,
            'stopwatch_duration' => null,
            'score' => 0,
            'scheduled_target' => $scheduledTargetId,
            'shift' => $data['part_a']['shift'],
            'target' => $scheduledTargetId,
            'division' => $division,
            'attendance' => '0',
            'condition' => null,
            'replacement' => false,
            'replacement_of_id' => null,
        ]);
    }

    private function saveAbsentWithReplacementCase($data, $phase, $duration, $scheduledTargetId, $division, $detailId)
    {
        $penggantiParts = array_pad(explode('::', $data['part_a']['nama_pengganti']), 3, null);
        $penggantiUid = $penggantiParts[1] ?? $data['part_a']['nama_pengganti'];
        $penggantiTargetId = $this->getEmployeeId($penggantiUid);

        $penggantiUser = User::where('employeeID', $penggantiUid)->orWhere('id', $penggantiUid)->first();
        $penggantiName = $penggantiUser ? $penggantiUser->name : $penggantiUid;

        $parent = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'schedule_detail_id' => $detailId,
            'phase' => $phase,
            'stopwatch_duration' => null,
            'score' => 0,
            'scheduled_target' => $scheduledTargetId,
            'shift' => $data['part_a']['shift'],
            'target' => $scheduledTargetId,
            'division' => $division,
            'attendance' => '0',
            'condition' => null,
            'replacement' => false,
            'replacement_of_id' => null,
            'replacement_name' => $penggantiName,
            'replacement_division' => $data['part_a']['bagian_pengganti'] ?: $division,
            'replacement_condition' => $data['part_a']['kondisi_pengganti'],
        ]);

        $child = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'schedule_detail_id' => $detailId,
            'phase' => $phase,
            'stopwatch_duration' => $duration,
            'score' => 0,
            'scheduled_target' => $scheduledTargetId,
            'shift' => $data['part_a']['shift'],
            'target' => $penggantiTargetId,
            'division' => $division,
            'attendance' => '1',
            'condition' => $data['part_a']['kondisi_pengganti'],
            'replacement' => true,
            'replacement_of_id' => $parent->id,
        ]);

        $this->processAnswers($child, $data);
    }

    private function processAnswers(Checksheet $checksheet, array $data)
    {
        $answers = $data['answers'] ?? [];
        if (empty($answers)) {
            return;
        }

        $probs = $data['problems'] ?? [];
        $cms = $data['countermeasures'] ?? [];

        $questions = Question::whereIn('id', array_keys($answers))->get()->keyBy('id');

        $totalScore = 0;

        foreach ($answers as $qid => $val) {
            $question = $questions->get($qid);
            if (! $question) {
                continue;
            }

            // ✨ GANTI JADI CREATE DI SINI ✨
            // Ini bakal nge-trigger event 'created' di model ChecksheetAnswer
            ChecksheetAnswer::create([
                'checksheet_id' => $checksheet->id,
                'question_text' => $question->question_text,
                'choices' => json_encode($question->choices),
                'answer_value' => (string) $val,
                'problem' => $probs[$qid] ?? null,
                'countermeasure' => $cms[$qid] ?? null,
                // created_at dan updated_at otomatis diisi sama Eloquent
            ]);

            // Hitung Score
            $choicesCount = is_array($question->choices) ? count($question->choices) : 0;
            $answerValue = (int) $val;

            if ($choicesCount == 2) {
                $totalScore += ($answerValue == 0 ? 0 : 2);
            } elseif ($choicesCount == 3) {
                $totalScore += $answerValue;
            }
        }

        // Update total score di checksheet
        $checksheet->update(['score' => $totalScore]);
    }

    public function cancel(Request $req)
    {
        $planId = $req->input('plan_id');
        $phase = $req->input('phase');

        // BUKA GEMBOK: Bersihkan session timer dan flag active
        session()->forget([
            'active_checksheet',
            "cs_timer_{$planId}_{$phase}"
        ]);

        return response()->json(['success' => true]);
    }
}
