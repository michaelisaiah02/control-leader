<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\ChecksheetAnswer;
use App\Models\Question;
use App\Models\ScheduleDetail;
use App\Models\SchedulePlan;
use App\Models\User;
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
        $u = User::where('employeeID', $uid)->orWhere('id', $uid)->first();
        if (! $u) {
            return $uid;
        }

        return $u->employeeID ?: ($u->role === 'leader' ? 'LDR'.$u->id : 'OP'.$u->id);
    }

    // ==========================================
    // 2. PAGE RENDERING (PART A & B)
    // ==========================================

    public function createPartA(Request $req)
    {
        $me = auth()->user();
        $phase = $req->query('type', 'awal_shift');
        $direction = $this->directionFor($me);

        $plan = SchedulePlan::where('scheduler_id', $me->employeeID)
            ->where('type', $direction)->where('year', date('Y'))->where('month', date('m'))
            ->orderByDesc('year')->orderByDesc('month')->first();

        if (! $plan) {
            return back()->with('error', 'Belum ada Schedule Plan untuk Anda.');
        }

        if ($direction === 'supervisor_checks_leader') {
            $leaders = User::where('role', 'leader')->where('is_active', true)->orderBy('name')->get();
            $targetLabel = 'ID & Nama Leader';
            $options = $leaders->map(fn ($u) => [
                'value' => 'U::'.$u->employeeID,
                'label' => ($u->employeeID ?? "LDR{$u->id}").' - '.$u->name,
            ])->all();
        } else {
            $scheduleDetails = ScheduleDetail::where('schedule_plan_id', $plan->id)
                ->whereNotNull('target_user_id')
                ->with('targetUser')
                ->get();
            $targetLabel = 'ID & Nama Operator';
            $options = $scheduleDetails->map(fn ($d) => [
                'value' => "{$d->id}::{$d->target_user_id}::{$d->division}",
                'label' => ($d->targetUser->employeeID ?: "OP{$d->target_user_id}").' - '.$d->targetUser->name,
            ])->all();
        }

        // --- SESSION TIMER LOGIC ---
        $sessionKey = "cs_timer_{$plan->id}_{$phase}";
        if (! session()->has($sessionKey)) {
            session()->put($sessionKey, now()->timestamp);

            // PASANG GEMBOK: Simpan flag bahwa user lagi ngerjain ini
            session()->put('active_checksheet', [
                'plan_id' => $plan->id,
                'phase' => $phase,
            ]);
        }
        $startedAtSeconds = session()->get($sessionKey);
        $startedAtSeconds = session()->get($sessionKey);

        return view('checksheets.part-a', [
            'phase' => $phase,
            'plan' => $plan,
            'startedAtMs' => $startedAtSeconds * 1000, // Convert ke MS buat JS
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

        $data = $req->validate([
            'schedule_plan_id' => 'required|exists:schedule_plans,id',
            'part_a.shift' => 'required|in:1,2,3',
            'part_a.target' => 'required|string',
            'part_a.division' => 'nullable|string',
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

        // Hitung durasi dari session
        $sessionKey = "cs_timer_{$data['schedule_plan_id']}_{$phase}";
        $startedAtSeconds = session()->get($sessionKey);
        $duration = $startedAtSeconds ? (now()->timestamp - $startedAtSeconds) : 0;

        // Parsing Target
        $targetParts = array_pad(explode('::', $data['part_a']['target']), 3, null);
        [$detailId, $uid, $division] = $targetParts;

        $scheduledTargetId = $this->getEmployeeId($uid);
        $detail = ScheduleDetail::find($detailId);
        $divisionFromDetail = $detail?->division ?? $division ?? $data['part_a']['division'];

        $isPresent = (int) $data['part_a']['attendance'] === 1;
        $hasReplacement = in_array($data['part_a']['has_replacement'] ?? 0, [1, '1', true, 'true'], true);

        try {
            DB::beginTransaction();

            if ($isPresent) {
                $this->savePresentCase($data, $phase, $duration, $scheduledTargetId, $divisionFromDetail);
            } elseif (! $isPresent && ! $hasReplacement) {
                $this->saveAbsentNoReplacementCase($data, $phase, $scheduledTargetId, $divisionFromDetail);
            } else {
                $this->saveAbsentWithReplacementCase($data, $phase, $duration, $scheduledTargetId, $divisionFromDetail);
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
            Log::error('Checksheet Store Error: '.$e->getMessage());

            if ($req->ajax() || $req->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal menyimpan data! '.$e->getMessage());
        }
    }

    // ==========================================
    // 4. PRIVATE SAVING ACTIONS
    // ==========================================

    private function savePresentCase($data, $phase, $duration, $scheduledTargetId, $division)
    {
        $cs = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'phase' => $phase,
            'stopwatch_duration' => $duration,
            'score' => 0,
            'scheduled_target' => $scheduledTargetId,
            'shift' => $data['part_a']['shift'],
            'target' => $scheduledTargetId,
            'division' => $division,
            'attendance' => '1',
            'condition' => $data['part_a']['kondisi'],
            'replacement' => false,
            'replacement_of_id' => null,
        ]);
        $this->processAnswers($cs, $data);
    }

    private function saveAbsentNoReplacementCase($data, $phase, $scheduledTargetId, $division)
    {
        Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
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

    private function saveAbsentWithReplacementCase($data, $phase, $duration, $scheduledTargetId, $division)
    {
        $penggantiParts = array_pad(explode('::', $data['part_a']['nama_pengganti']), 3, null);
        $penggantiUid = $penggantiParts[1] ?? $data['part_a']['nama_pengganti'];
        $penggantiTargetId = $this->getEmployeeId($penggantiUid);

        $penggantiUser = User::where('employeeID', $penggantiUid)->orWhere('id', $penggantiUid)->first();
        $penggantiName = $penggantiUser ? $penggantiUser->name : $penggantiUid;

        $parent = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
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
}
