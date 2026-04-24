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

    // ==========================================
    // 2. PAGE RENDERING (PART A & B)
    // ==========================================

    public function createPartA(Request $req)
    {
        $me = auth()->user();
        $phase = $req->query('type', 'awal_shift');
        $direction = $this->directionFor($me);

        $todayDate = now()->startOfDay();

        $plan = SchedulePlan::where('scheduler_id', $me->employeeID)
            ->where('type', $direction)
            ->where('year', date('Y'))
            ->where('month', date('m'))
            ->first();

        if (!$plan) {
            return back()->with('error', 'Belum ada Schedule Plan untuk Anda bulan ini.');
        }

        $completedDetailIds = Checksheet::where('schedule_plan_id', $plan->id)
            ->where('phase', $phase)
            ->where('replacement', false)
            ->whereNotNull('schedule_detail_id')
            ->pluck('schedule_detail_id')
            ->toArray();

        $currentShift = session('shift') ?? 1;

        // Bikin base query-nya dulu
        $detailsQuery = ScheduleDetail::where('schedule_plan_id', $plan->id)
            ->whereNotNull('target_user_id')
            ->whereHas('targetUser', function ($query) use ($me) {
                $query->where('superior_id', $me->employeeID);
            })
            ->with('targetUser.division') // 🔥 Eager load division biar ga kosong
            ->orderBy('target_user_id')
            ->orderBy('scheduled_date');

        // 🔥 MAGIC FIX: Pisahin logic pencarian Shift!
        if ($direction === 'leader_checks_operator') {
            // Leader wajib sesuai shift yang dipilih
            $detailsQuery->where('shift', $currentShift);
        } else {
            // Supervisor shift-nya null, jadi filter khusus null
            $detailsQuery->whereNull('shift');
        }

        // Baru dieksekusi get()
        $details = $detailsQuery->get();

        $grouped = $details->groupBy('target_user_id');
        $options = [];
        $targetLabel = $direction === 'supervisor_checks_leader' ? 'ID & Nama Leader' : 'ID & Nama Operator';

        foreach ($grouped as $userId => $userDates) {
            if ($direction === 'supervisor_checks_leader') {
                $weeklyBlocks = [];

                // 1. KELOMPOKKAN BERDASARKAN MINGGU (1-5)
                foreach ($userDates as $d) {
                    $day = Carbon::parse($d->scheduled_date)->day;
                    $weekNum = (int) ceil($day / 7);
                    if ($weekNum > 5) $weekNum = 5; // Jaga-jaga tgl 29-31 masuk week 5

                    $weeklyBlocks[$weekNum][] = $d;
                }

                // 2. FORMAT TIAP MINGGU JADI OPSI DROPDOWN
                foreach ($weeklyBlocks as $weekNum => $block) {
                    $firstDetailId = $block[0]->id;
                    $firstDate = Carbon::parse($block[0]->scheduled_date);
                    $startDate = $firstDate->copy()->startOfDay();

                    // Cek apakah checksheet minggu ini udah pernah dikerjain
                    $isCompleted = false;
                    foreach ($block as $b) {
                        if (in_array($b->id, $completedDetailIds)) {
                            $isCompleted = true;
                            break;
                        }
                    }

                    // Syarat cuma "Belum dikerjain", Bebas waktu (Bisa Advance) khusus Supervisor!
                    if (!$isCompleted) {
                        $targetUser = $block[0]->targetUser;
                        $div = $targetUser?->division?->name ?? 'Tanpa Divisi';

                        $monthStr = $firstDate->format('M'); // cth: Apr
                        $daysInMonth = $firstDate->daysInMonth;

                        // Bikin label teks paten sesuai Kanban Board
                        $rangeLabel = match ($weekNum) {
                            1 => "(01 $monthStr - 07 $monthStr)",
                            2 => "(08 $monthStr - 14 $monthStr)",
                            3 => "(15 $monthStr - 21 $monthStr)",
                            4 => "(22 $monthStr - 28 $monthStr)",
                            5 => "(29 $monthStr - " . str_pad($daysInMonth, 2, '0', STR_PAD_LEFT) . " $monthStr)",
                            default => ""
                        };

                        $options[] = [
                            'value' => "{$firstDetailId}::{$userId}::{$div}",
                            'label' => ($targetUser->employeeID ?: "LDR{$userId}") . ' - ' . $targetUser->name . " " . $rangeLabel,
                        ];
                    }
                }
            } else {
                foreach ($userDates as $d) {
                    $scheduleDate = Carbon::parse($d->scheduled_date)->startOfDay();

                    // Syarat buat Leader: Belum dikerjain & HARUS HARI INI ATAU TELAT (Gak boleh Advance)
                    if (!in_array($d->id, $completedDetailIds) && $scheduleDate->lte($todayDate)) {
                        $targetUser = $d->targetUser;
                        if (!$targetUser) continue;

                        $fmtDate = $scheduleDate->format('d M');
                        $isLate = $scheduleDate->lessThan($todayDate) ? " (Telat: $fmtDate)" : " (Hari ini)";

                        // 🔥 Ambil nama divisi dari relasi
                        $div = $targetUser?->division?->name ?? 'Tanpa Divisi';

                        $options[] = [
                            // 🔥 FIX: Pake $d->id (Schedule Detail ID), bukan $targetUser->id
                            'value' => "{$d->id}::{$userId}::{$div}",
                            'label' => ($targetUser->employeeID ?: "OP{$userId}") . ' - ' . $targetUser->name . $isLate,
                        ];
                    }
                }
            }
        }

        if (empty($options)) {
            return back()->with('info', 'Semua jadwal Anda untuk saat ini sudah dikerjakan! 🎉');
        }

        $sessionKey = "cs_timer_{$plan->id}_{$phase}";
        if (!session()->has($sessionKey)) {
            session()->put($sessionKey, now()->timestamp);
            session()->put('active_checksheet', [
                'plan_id' => $plan->id,
                'phase' => $phase,
            ]);
        }
        $startedAtSeconds = session()->get($sessionKey);

        // AMBIL DAFTAR PENGGANTI (Bebas jadwal, 1 Supervisor) 🔥
        $penggantiOptions = [];
        if ($phase !== 'leader') {
            // 1. Cari semua Leader sejawat (Atasan SPV-nya sama)
            $siblingLeaderIds = User::where('superior_id', $me->superior_id)->pluck('employeeID');

            // 2. Tarik semua operator di bawah naungan leader-leader tersebut
            $penggantiUsers = User::with('division')
                ->whereIn('superior_id', $siblingLeaderIds)
                ->where('role', 'operator')
                ->orderBy('employeeID')
                ->get();

            // 3. Format datanya biar selaras sama Javascript Frontend
            foreach ($penggantiUsers as $pu) {
                $div = $pu->division ? $pu->division->name : '-';
                $penggantiOptions[] = [
                    'value' => "{$pu->employeeID}::{$pu->employeeID}::{$div}",
                    'label' => "{$pu->employeeID} - {$pu->name}"
                ];
            }
        }

        return view('checksheets.part-a', [
            'phase' => $phase,
            'plan' => $plan,
            'startedAtMs' => $startedAtSeconds * 1000,
            'targetLabel' => $targetLabel,
            'options' => $options,
            'penggantiOptions' => $penggantiOptions,
        ]);
    }

    public function showPartB(Request $req)
    {
        $me = auth()->user();
        $phase = $req->query('type', 'awal_shift');
        $plan = SchedulePlan::findOrFail((int) $req->query('plan'));

        $package = $this->packageFor($phase, $this->directionFor($me));
        $questions = Question::where('package', $package)->where('is_active', true)->orderBy('display_order')->get();

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
        $isSpv = $phase === 'leader';

        $rules = [
            'schedule_plan_id' => 'required|exists:schedule_plans,id',
            'part_a.target' => 'required|string',
            'part_a.division' => 'nullable|string',
            'part_a.kondisi' => 'nullable|string',
            'answers' => 'array',
            'problems' => 'array',
            'countermeasures' => 'array',
        ];

        if ($isSpv) {
            $rules['part_a.shift'] = 'nullable';
            $rules['part_a.attendance'] = 'nullable';
            $rules['part_a.has_replacement'] = 'nullable';
        } else {
            $rules['part_a.shift'] = 'required|in:1,2,3';
            $rules['part_a.attendance'] = 'required|in:0,1';
            $rules['part_a.has_replacement'] = 'sometimes|boolean';
            $rules['part_a.nama_pengganti'] = 'nullable';
            $rules['part_a.bagian_pengganti'] = 'nullable';
            $rules['part_a.kondisi_pengganti'] = 'nullable';
        }

        $data = $req->validate($rules);

        $sessionKey = "cs_timer_{$data['schedule_plan_id']}_{$phase}";
        $startedAtSeconds = session()->get($sessionKey);
        $duration = $startedAtSeconds ? (now()->timestamp - $startedAtSeconds) : 0;

        $targetParts = array_pad(explode('::', $data['part_a']['target']), 3, null);
        [$detailId, $uid, $division] = $targetParts;

        $detail = ScheduleDetail::find($detailId);

        // 🔥 SMART LOGIC 1: Ambil Tanggal Shift dari Jadwal (Biar kalau telat ngisi, tetep masuk ke hari yang bener)
        $shiftDate = $detail ? Carbon::parse($detail->scheduled_date)->format('Y-m-d') : now()->format('Y-m-d');

        // 🔥 SMART LOGIC 2: Ambil Identitas & Divisi Target Murni Dari Database
        $scheduledUser = User::with('division')->where('employeeID', $uid)->first();
        $scheduledTargetId = $scheduledUser ? $scheduledUser->employeeID : $uid;
        $scheduledTargetName = $scheduledUser ? $scheduledUser->name : 'Unknown';

        // Format murni "ID - Nama"
        $scheduledTargetFull = $scheduledTargetId . ' - ' . $scheduledTargetName;
        // Format murni nama divisi
        $divisionName = $division ?? 'Tanpa Divisi';

        $isPresent = $isSpv ? true : ((int) ($data['part_a']['attendance'] ?? 0) === 1);
        $hasReplacement = $isSpv ? false : in_array($data['part_a']['has_replacement'] ?? 0, [1, '1', true, 'true'], true);

        try {
            DB::beginTransaction();

            if ($isPresent) {
                $this->savePresentCase($data, $phase, $duration, $scheduledTargetId, $scheduledTargetFull, $divisionName, $detailId, $shiftDate);
            } elseif (! $isPresent && ! $hasReplacement) {
                $this->saveAbsentNoReplacementCase($data, $phase, $scheduledTargetId, $scheduledTargetFull, $divisionName, $detailId, $shiftDate);
            } else {
                $this->saveAbsentWithReplacementCase($data, $phase, $duration, $scheduledTargetId, $scheduledTargetFull, $divisionName, $detailId, $shiftDate);
            }

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

    private function savePresentCase($data, $phase, $duration, $targetId, $targetFull, $division, $detailId, $shiftDate)
    {
        $cs = Checksheet::create([
            'shift_date' => $shiftDate, // 🔥 Input Tanggal Shift
            'schedule_plan_id' => $data['schedule_plan_id'],
            'schedule_detail_id' => $detailId,
            'phase' => $phase,
            'stopwatch_duration' => $duration,
            'score' => 0,
            'scheduled_target' => $targetFull, // Format 00001 - Steven
            'shift' => $data['part_a']['shift'],
            'target' => $targetId,
            'division' => $division, // Format String Nama Divisi
            'attendance' => '1',
            'condition' => $data['part_a']['kondisi'] ?? null,
            'replacement' => false,
            'replacement_of_id' => null,
        ]);
        $this->processAnswers($cs, $data);
    }

    private function saveAbsentNoReplacementCase($data, $phase, $targetId, $targetFull, $division, $detailId, $shiftDate)
    {
        Checksheet::create([
            'shift_date' => $shiftDate, // 🔥 Input Tanggal Shift
            'schedule_plan_id' => $data['schedule_plan_id'],
            'schedule_detail_id' => $detailId,
            'phase' => $phase,
            'stopwatch_duration' => null,
            'score' => 0,
            'scheduled_target' => $targetFull,
            'shift' => $data['part_a']['shift'],
            'target' => $targetId,
            'division' => $division,
            'attendance' => '0',
            'condition' => null,
            'replacement' => false,
            'replacement_of_id' => null,
        ]);
    }

    private function saveAbsentWithReplacementCase($data, $phase, $duration, $targetId, $targetFull, $division, $detailId, $shiftDate)
    {
        // 🔥 Ambil Data Pengganti Dari Database Biar Rapi
        $penggantiParts = array_pad(explode('::', $data['part_a']['nama_pengganti']), 3, null);
        $penggantiUid = $penggantiParts[1] ?? $data['part_a']['nama_pengganti'];

        $penggantiUser = User::with('division')->where('employeeID', $penggantiUid)->first();
        $penggantiTargetId = $penggantiUser ? $penggantiUser->employeeID : $penggantiUid;
        $penggantiName = $penggantiUser ? $penggantiUser->name : $penggantiUid;
        $penggantiDivision = $penggantiUser?->division?->name ?? 'Tanpa Divisi';

        $parent = Checksheet::create([
            'shift_date' => $shiftDate, // 🔥 Input Tanggal Shift
            'schedule_plan_id' => $data['schedule_plan_id'],
            'schedule_detail_id' => $detailId,
            'phase' => $phase,
            'stopwatch_duration' => null,
            'score' => 0,
            'scheduled_target' => $targetFull,
            'shift' => $data['part_a']['shift'],
            'target' => $targetId,
            'division' => $division,
            'attendance' => '0',
            'condition' => null,
            'has_replacement' => true,
            'replacement' => false,
            'replacement_of_id' => null,
            'replacement_name' => $penggantiName,
            'replacement_division' => $penggantiDivision, // Divisi Asli si Pengganti
            'replacement_condition' => $data['part_a']['kondisi_pengganti'],
        ]);

        $child = Checksheet::create([
            'shift_date' => $shiftDate, // 🔥 Input Tanggal Shift
            'schedule_plan_id' => $data['schedule_plan_id'],
            'schedule_detail_id' => $detailId,
            'phase' => $phase,
            'stopwatch_duration' => $duration,
            'score' => 0,
            'scheduled_target' => $targetFull,
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
        if (empty($answers)) return;

        $probs = $data['problems'] ?? [];
        $cms = $data['countermeasures'] ?? [];

        $questions = Question::whereIn('id', array_keys($answers))->get()->keyBy('id');
        $totalScore = 0;

        foreach ($answers as $qid => $val) {
            $question = $questions->get($qid);
            if (! $question) continue;

            ChecksheetAnswer::create([
                'checksheet_id' => $checksheet->id,
                'question_text' => $question->question_text,
                'choices' => json_encode($question->choices),
                'answer_value' => (string) $val,
                'problem' => $probs[$qid] ?? null,
                'countermeasure' => $cms[$qid] ?? null,
            ]);

            $choicesCount = is_array($question->choices) ? count($question->choices) : 0;
            $answerValue = (int) $val;

            if ($choicesCount == 2) {
                $totalScore += ($answerValue == 0 ? 0 : 2);
            } elseif ($choicesCount == 3) {
                $totalScore += $answerValue;
            }
        }

        $checksheet->update(['score' => $totalScore]);
    }

    public function cancel(Request $req)
    {
        $planId = $req->input('plan_id');
        $phase = $req->input('phase');

        session()->forget(['active_checksheet', "cs_timer_{$planId}_{$phase}"]);
        return response()->json(['success' => true]);
    }
}
