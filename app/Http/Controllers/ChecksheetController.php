<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\ChecksheetAnswer;
use App\Models\ChecksheetDraft;
use App\Models\Question;
use App\Models\ScheduleDetail;
use App\Models\SchedulePlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // WAJIB TAMBAH INI
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
            'awal_shift'        => 'awal_shift',
            'saat_bekerja'      => 'saat_bekerja',
            'setelah_istirahat' => 'setelah_istirahat',
            'akhir_shift'       => 'akhir_shift',
            default             => 'awal_shift'
        };
    }

    private function directionFor($me)
    {
        return $me->role === 'Supervisor' ? 'supervisor_checks_leader' : 'leader_checks_operator';
    }

    private function userLabel(string $uid): string
    {
        $u = User::where('employeeID', $uid)->first();
        if (!$u) return '';
        return $u->employeeID ?: ($u->role === 'leader' ? 'LDR' . $u->id : 'OP' . $u->id);
    }

    // ==========================================
    // 2. DRAFT & TIMER LOGIC
    // ==========================================

    public function startDraft(Request $r)
    {
        $me = auth()->user();
        $data = $r->validate([
            'schedule_plan_id' => 'required|exists:schedule_plans,id',
            'phase' => 'required|string',
        ]);

        ChecksheetDraft::where('user_id', $me->id)
            ->where('schedule_plan_id', $data['schedule_plan_id'])
            ->where('phase', $data['phase'])
            ->where('is_active', false)
            ->delete();

        $draft = ChecksheetDraft::updateOrCreate(
            ['user_id' => $me->id, 'schedule_plan_id' => $data['schedule_plan_id'], 'phase' => $data['phase']],
            ['session_id' => session()->getId(), 'is_active' => true, 'last_ping' => now()]
        );

        if (!$draft->started_at) {
            $draft->update(['started_at' => now()]);
        }

        return response()->json(['ok' => true, 'started_at_ms' => $draft->started_at->getTimestampMs()]);
    }

    public function heartbeat()
    {
        ChecksheetDraft::where('user_id', auth()->id())
            ->where('session_id', session()->getId())
            ->update(['last_ping' => now()]);

        return response()->json(['ok' => true]);
    }

    // ==========================================
    // 3. PAGE RENDERING (PART A & B)
    // ==========================================

    public function createPartA(Request $req)
    {
        $me = auth()->user();
        $phase = $req->query('type', 'awal_shift');
        $direction = $this->directionFor($me);

        $plan = SchedulePlan::where('scheduler_id', $me->employeeID)
            ->where('type', $direction)->where('year', date('Y'))->where('month', date('m'))
            ->orderByDesc('year')->orderByDesc('month')->first();

        if (!$plan) return back()->with('error', 'Belum ada Schedule Plan untuk Anda.');

        if ($direction === 'supervisor_checks_leader') {
            $leaders = User::where('role', 'leader')->where('is_active', true)->orderBy('name')->get();
            $targetLabel = 'ID & Nama Leader';
            $options = $leaders->map(fn($u) => [
                'value' => 'U::' . $u->employeeID, // Fix: Use employeeID instead of ID to match DB safely
                'label' => ($u->employeeID ?? "LDR{$u->id}") . ' - ' . $u->name,
            ])->all();
        } else {
            $scheduleDetails = ScheduleDetail::where('schedule_plan_id', $plan->id)
                ->whereNotNull('target_user_id')
                ->with('targetUser')
                ->get();
            // dd($plan);
            $targetLabel = 'ID & Nama Operator';
            $options = $scheduleDetails->map(fn($d) => [
                'value' => "{$d->id}::{$d->target_user_id}::{$d->division}",
                'label' => ($d->targetUser->employeeID ?: "OP{$d->target_user_id}") . ' - ' . $d->targetUser->name,
            ])->all();
        }

        $draft = ChecksheetDraft::where('user_id', $me->id)
            ->where('schedule_plan_id', $plan->id)
            ->where('phase', $phase)->first();

        if ($draft && $draft->last_ping && $draft->last_ping->diffInSeconds(now()) > 45) {
            $draft->delete();
            $draft = false;
        }

        if (!$draft) {
            $draft = ChecksheetDraft::create([
                'user_id' => $me->id,
                'schedule_plan_id' => $plan->id,
                'phase' => $phase,
                'session_id' => session()->getId(),
                'started_at' => now(),
                'last_ping' => now(),
                'is_active' => true,
            ]);
        } else {
            $draft->update(['session_id' => session()->getId(), 'is_active' => true]);
        }

        return view('checksheets.part-a', [
            'phase' => $phase,
            'plan' => $plan,
            'startedAtMs' => $draft->started_at?->getTimestampMs() ?? now()->getTimestampMs(),
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

        $draft = ChecksheetDraft::where('user_id', $me->id)
            ->where('schedule_plan_id', $plan->id)
            ->where('phase', $phase)->first();

        return view('checksheets.part-b', [
            'phase' => $phase,
            'plan' => $plan,
            'questions' => $questions,
            'startedAtMs' => $draft->started_at?->getTimestampMs() ?? now()->getTimestampMs(),
        ]);
    }

    // ==========================================
    // 4. MAIN STORE LOGIC (REFACTORED)
    // ==========================================

    public function store(Request $req)
    {
        $me = auth()->user();
        $phase = $req->query('type');

        $data = $req->validate([
            'schedule_plan_id' => 'required|exists:schedule_plans,id',
            'part_a.shift' => 'required|in:1,2,3',
            'part_a.target' => 'required|string',
            'part_a.division' => 'nullable|string', // Bikin nullable aja biar aman
            'part_a.attendance' => 'required|in:0,1',
            'part_a.has_replacement' => 'sometimes|boolean',
            'part_a.kondisi' => 'nullable|string',
            'part_a.nama_pengganti' => 'nullable|string',
            'part_a.bagian_pengganti' => 'nullable|string',
            'part_a.kondisi_pengganti' => 'nullable|string',
            'answers' => 'array',
            'problems' => 'array',
            'countermeasures' => 'array',
        ]);

        // 1. Dapatkan Durasi Draft
        $draft = ChecksheetDraft::where('user_id', $me->id)
            ->where('schedule_plan_id', $data['schedule_plan_id'])
            ->where('phase', $phase)->where('is_active', true)->first();

        $duration = $draft?->started_at ? $draft->started_at->diffInSeconds(now()) : 0;

        // 2. Parsing Target Secara Aman (Anti Error Undefined Key)
        $targetParts = array_pad(explode('::', $data['part_a']['target']), 3, null);
        [$detailId, $uid, $division] = $targetParts;

        $scheduledLabel = $this->userLabel($uid);
        $detail = ScheduleDetail::find($detailId);
        $divisionFromDetail = $detail?->division ?? $division ?? $data['part_a']['division'];

        // 3. Identifikasi Status
        $isPresent = (int) $data['part_a']['attendance'] === 1;
        $hasReplacement = in_array($data['part_a']['has_replacement'] ?? 0, [1, '1', true, 'true'], true);

        // 4. BUNGKUS DENGAN DATABASE TRANSACTION (SUPER PENTING!)
        try {
            DB::beginTransaction();

            if ($isPresent) {
                // CASE 1: Hadir (Simpan 1 Checksheet)
                $this->savePresentCase($data, $phase, $duration, $scheduledLabel, $divisionFromDetail);
            } elseif (!$isPresent && !$hasReplacement) {
                // CASE 2: Absen Tanpa Pengganti (Hanya Data Part A)
                $this->saveAbsentNoReplacementCase($data, $phase, $scheduledLabel, $divisionFromDetail);
            } else {
                // CASE 3: Absen Dengan Pengganti (Parent & Child)
                $this->saveAbsentWithReplacementCase($data, $phase, $duration, $scheduledLabel, $divisionFromDetail, $req);
            }

            // Matikan Draft jika sukses
            ChecksheetDraft::where('user_id', $me->id)
                ->where('schedule_plan_id', $data['schedule_plan_id'])
                ->where('phase', $phase)
                ->update(['is_active' => false]);

            DB::commit();
            return redirect()->route('dashboard')->with('success', 'Checksheet berhasil tersimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error biar lo tau masalahnya dimana kalau gagal
            Log::error('Checksheet Store Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data! ' . $e->getMessage());
        }
    }

    // ==========================================
    // 5. PRIVATE SAVING ACTIONS
    // ==========================================

    private function savePresentCase($data, $phase, $duration, $scheduledLabel, $division)
    {
        $cs = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'phase' => $phase,
            'stopwatch_duration' => $duration,
            'score' => 0,
            'scheduled_target' => $scheduledLabel,
            'shift' => $data['part_a']['shift'],
            'target' => $scheduledLabel,
            'division' => $division,
            'attendance' => '1',
            'condition' => $data['part_a']['kondisi'],
            'replacement' => false,
            'replacement_of_id' => null,
        ]);

        $this->processAnswers($cs, $data);
    }

    private function saveAbsentNoReplacementCase($data, $phase, $scheduledLabel, $division)
    {
        Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'phase' => $phase,
            'stopwatch_duration' => null, // Gak ada pengerjaan Part B
            'score' => 0,
            'scheduled_target' => $scheduledLabel,
            'shift' => $data['part_a']['shift'],
            'target' => $scheduledLabel,
            'division' => $division,
            'attendance' => '0',
            'condition' => null,
            'replacement' => false,
            'replacement_of_id' => null,
        ]);
    }

    private function saveAbsentWithReplacementCase($data, $phase, $duration, $scheduledLabel, $division, $req)
    {
        // 1. Buat Parent (Orang yang aslinya jadwal tapi Absen)
        $parent = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'phase' => $phase,
            'stopwatch_duration' => null,
            'score' => 0,
            'scheduled_target' => $scheduledLabel,
            'shift' => $data['part_a']['shift'],
            'target' => $scheduledLabel,
            'division' => $division,
            'attendance' => '0',
            'condition' => null,
            'replacement' => false,
            'replacement_of_id' => null,
            'replacement_name' => $data['part_a']['nama_pengganti'],
            'replacement_division' => $data['part_a']['bagian_pengganti'] ?: $division,
            'replacement_condition' => $data['part_a']['kondisi_pengganti'],
        ]);

        // Parsing Label Evaluated Pengganti
        $penggantiId = explode('::', $data['part_a']['nama_pengganti'])[1] ?? '';
        $evaluatedLabel = trim(($penggantiId) . ' - ' . explode('::', $data['part_a']['nama_pengganti'])[0] ?? 'Pengganti');

        // 2. Buat Child (Orang pengganti yang dinilai form Part B nya)
        $child = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'phase' => $phase,
            'stopwatch_duration' => $duration,
            'score' => 0,
            'scheduled_target' => $scheduledLabel,
            'shift' => $data['part_a']['shift'],
            'target' => $evaluatedLabel,
            'division' => $division,
            'attendance' => '1',
            'condition' => $data['part_a']['kondisi_pengganti'],
            'replacement' => true,
            'replacement_of_id' => $parent->id,
        ]);

        $this->processAnswers($child, $data);
    }

    // ==========================================
    // 6. PROCESS ANSWERS (N+1 FIXED)
    // ==========================================

    private function processAnswers(Checksheet $checksheet, array $data)
    {
        $answers = $data['answers'] ?? [];
        if (empty($answers)) return;

        $probs = $data['problems'] ?? [];
        $cms = $data['countermeasures'] ?? [];

        // FIXED N+1 QUERY: Ambil semua pertanyaan sekaligus dalam 1 Query!
        $questions = Question::whereIn('id', array_keys($answers))->get()->keyBy('id');

        $totalScore = 0;
        $insertData = [];

        foreach ($answers as $qid => $val) {
            $question = $questions->get($qid);
            if (!$question) continue;

            $insertData[] = [
                'checksheet_id' => $checksheet->id,
                'question_text' => $question->question_text,
                'choices' => json_encode($question->choices), // Ubah array ke JSON kalau pake insert batch
                'answer_value' => (string) $val,
                'problem' => $probs[$qid] ?? null,
                'countermeasure' => $cms[$qid] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Kalkulasi Skor
            $choicesCount = is_array($question->choices) ? count($question->choices) : 0;
            $answerValue = (int) $val;

            if ($choicesCount == 2) {
                $totalScore += ($answerValue == 0 ? 0 : 2); // 0 -> 0, 1 -> 2
            } elseif ($choicesCount == 3) {
                $totalScore += $answerValue; // 0 -> 0, 1 -> 1, 2 -> 2
            }
        }

        // Batch Insert Answers (Jauh lebih cepat dari create satu-satu)
        ChecksheetAnswer::insert($insertData);

        // Update total skor checksheet
        $checksheet->update(['score' => $totalScore]);
    }
}
