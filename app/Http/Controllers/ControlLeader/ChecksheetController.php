<?php

namespace App\Http\Controllers\ControlLeader;

use App\Http\Controllers\Controller;
use App\Models\ControlLeader\Checksheet;
use App\Models\ControlLeader\ChecksheetAnswer;
use App\Models\ControlLeader\ChecksheetDraft;
use App\Models\ControlLeader\Question;
use App\Models\ControlLeader\ScheduleDetail;
use App\Models\ControlLeader\SchedulePlan;
use App\Models\ControlLeader\User;
use Illuminate\Http\Request;

class ChecksheetController extends Controller
{
    // map phase -> package untuk Question
    private function packageFor(string $phase, string $direction): string
    {
        if ($direction === 'supervisor_checks_leader') {
            return 'leader';
        }

        return match ($phase) {
            'awal_shift' => 'op_awal',
            'saat_bekerja' => 'op_bekerja',
            'setelah_istirahat' => 'op_istirahat',
            'akhir_shift' => 'op_akhir',
            default => 'op_awal'
        };
    }

    private function directionFor($me)
    {
        // bila role supervisor → dia menilai leader, selain itu leader menilai operator
        return $me->role === 'Supervisor' ? 'supervisor_checks_leader' : 'leader_checks_operator';
    }

    public function createPartA(Request $req)
    {
        $me = auth('web_control_leader')->user();
        $phase = $req->query('type', 'awal_shift');

        // Ambil plan paling baru milik scheduler=me untuk direction sesuai role
        $direction = $this->directionFor($me);
        $plan = SchedulePlan::where('scheduler_id', $me->id)
            ->where('type', $direction)
            ->orderByDesc('year')->orderByDesc('month')->first();

        if (! $plan) {
            return back()->with('error', 'Belum ada Schedule Plan untuk Anda.');
        }

        // Opsi target dari DETAILS plan terbaru (tanpa tanggal di label)
        if ($direction === 'supervisor_checks_leader') {
            $leaderIds = ScheduleDetail::where('schedule_plan_id', $plan->id)
                ->whereNotNull('target_leader_id')
                ->pluck('target_leader_id')->unique();
            $leaders = User::whereIn('id', $leaderIds)->orderBy('name')->get();
            $targetLabel = 'ID & Nama Leader';
            $options = $leaders->map(fn ($u) => [
                'value' => "L::{$u->id}",
                'label' => ($u->employeeID ?? "LDR{$u->id}").' - '.$u->name,
            ])->values()->all();
        } else {
            $ops = ScheduleDetail::where('schedule_plan_id', $plan->id)
                ->whereNotNull('target_operator_id')
                ->whereNotNull('target_operator_name')
                ->select('target_operator_id', 'target_operator_name')
                ->distinct()->orderBy('target_operator_name')->get();
            $targetLabel = 'ID & Nama Operator';
            $options = $ops->map(fn ($r) => [
                'value' => "O::{$r->target_operator_id}::{$r->target_operator_name}",
                'label' => "{$r->target_operator_id} - {$r->target_operator_name}",
            ])->values()->all();
        }

        // Draft (key: user_id + plan + phase)
        $draft = ChecksheetDraft::firstOrCreate(
            ['user_id' => $me->id, 'schedule_plan_id' => $plan->id, 'phase' => $phase],
            ['session_id' => session()->getId(), 'started_at' => now(), 'last_ping' => now(), 'is_active' => true]
        );
        $draft->forceFill(['session_id' => session()->getId(), 'last_ping' => now(), 'is_active' => true])->save();

        return view('control.checksheets.part-a', [
            'phase' => $phase,
            'plan' => $plan,
            'deptName' => optional($plan->scheduler?->department)->department_name ?? '',
            'startedAtMs' => $draft->started_at?->getTimestampMs() ?? now()->getTimestampMs(),
            'targetLabel' => $targetLabel,
            'options' => $options,
        ]);
    }

    public function startDraft(Request $r)
    {
        $me = auth('web_control_leader')->user();
        $data = $r->validate([
            'schedule_plan_id' => 'required|exists:mysql_control_leader.schedule_plans,id',
            'phase' => 'required|string',
        ]);
        $draft = ChecksheetDraft::updateOrCreate(
            ['user_id' => $me->id, 'schedule_plan_id' => $data['schedule_plan_id'], 'phase' => $data['phase']],
            ['session_id' => session()->getId(), 'is_active' => true, 'last_ping' => now()]
        );
        if (! $draft->started_at) {
            $draft->started_at = now();
            $draft->save();
        }

        return response()->json(['ok' => true, 'started_at_ms' => $draft->started_at->getTimestampMs()]);
    }

    public function heartbeat()
    {
        $me = auth('web_control_leader')->user();
        ChecksheetDraft::where('user_id', $me->id)->where('session_id', session()->getId())
            ->update(['last_ping' => now()]);

        return response()->json(['ok' => true]);
    }

    public function showPartB(Request $req)
    {
        $me = auth('web_control_leader')->user();
        $phase = $req->query('type', 'awal_shift');

        $planId = (int) $req->query('plan');
        $plan = SchedulePlan::findOrFail($planId);

        $direction = $this->directionFor($me);
        $package = $this->packageFor($phase, $direction);

        $questions = Question::where('package', $package)->where('is_active', true)
            ->orderBy('display_order')->get();

        return view('control.checksheets.part-b', [
            'phase' => $phase,
            'plan' => $plan,
            'questions' => $questions,
        ]);
    }

    public function store(Request $req)
    {
        $me = auth('web_control_leader')->user();
        $phase = $req->query('type', 'awal_shift');

        $data = $req->validate([
            'schedule_plan_id' => 'required|exists:mysql_control_leader.schedule_plans,id',
            'part_a.shift' => 'required|in:1,2,3',
            'part_a.target' => 'required|string', // "O::id::name" / "L::id"
            'part_a.division' => 'required|string',
            'part_a.attendance' => 'required|in:0,1',
            'part_a.condition' => 'nullable|string',
            'part_a.replacement_name' => 'nullable|string',
            'part_a.replacement_division' => 'nullable|string',
            'part_a.replacement_condition' => 'nullable|string',
            'answers' => 'array',
            'problems' => 'array',
            'countermeasures' => 'array',
        ]);
        // ambil duration dari draft
        $draft = ChecksheetDraft::where('user_id', $me->id)
            ->where('schedule_plan_id', $data['schedule_plan_id'])
            ->where('phase', $phase)->first();
        $duration = $draft && $draft->started_at ? $draft->started_at->diffInSeconds(now()) : 0;

        // simpan checksheet (Part A snapshot + durasi)
        $cs = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'phase' => $phase,
            'user_id' => $me->id,
            'stopwatch_duration' => $duration,
            'shift' => $data['part_a']['shift'],
            'target' => $data['part_a']['target'],
            'division' => $data['part_a']['division'],
            'attendance' => $data['part_a']['attendance'],
            'condition' => $data['part_a']['condition'] ?? null,
            'replacement_name' => $data['part_a']['replacement_name'] ?? null,
            'replacement_division' => $data['part_a']['replacement_division'] ?? null,
            'replacement_condition' => $data['part_a']['replacement_condition'] ?? null,
        ]);

        // hapus session draft

        // simpan jawaban B
        $answers = $data['answers'] ?? [];
        $probs = $data['problems'] ?? [];
        $cms = $data['countermeasures'] ?? [];

        foreach ($answers as $qid => $val) {
            ChecksheetAnswer::create([
                'checksheet_id' => $cs->id,
                'question_text' => Question::find($qid)?->question_text ?? '',
                'answer' => (string) $val,
                'problem' => $probs[$qid] ?? null,
                'countermeasure' => $cms[$qid] ?? null,
            ]);
        }

        if ($draft) {
            $draft->update(['is_active' => false]);
        }

        return redirect()->route('dashboard')->with('ok', 'Checksheet tersimpan.');
    }
}
