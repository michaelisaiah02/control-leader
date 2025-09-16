<?php

namespace App\Http\Controllers\ControlLeader;

use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\ControlLeader\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ControlLeader\Division;
use App\Models\ControlLeader\Question;
use App\Models\ControlLeader\Checksheet;
use App\Models\ControlLeader\Department;
use App\Models\ControlLeader\SchedulePlan;
use App\Models\ControlLeader\ScheduleDetail;
use App\Models\ControlLeader\ChecksheetAnswer;

class ChecksheetController extends Controller
{
    // ==== PART A ====
    public function createPartA(Request $req)
    {
        $me = auth('web_control_leader')->user();
        $type = $req->query('type', 'awal_shift');
        $date = $req->query('date', now()->toDateString());

        // detail “utama” yang dibuka (sesuai tanggal & scheduler login)
        $detail = ScheduleDetail::with(['plan.scheduler'])
            ->whereDate('scheduled_date', $date)
            ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
            ->orderBy('id')
            ->firstOrFail();

        $deptName = optional($detail->plan->scheduler?->department)->department_name ?? '—';

        // ==== build opsi target DARI JADWAL TANGGAL ITU + SCHEDULER YG SAMA ====
        if ($me->role === 'Supervisor') {
            // supervisor cek leader
            $leaderIds = ScheduleDetail::query()
                ->whereDate('scheduled_date', $date)
                ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
                ->whereNotNull('target_leader_id')
                ->pluck('target_leader_id')->unique()->values();

            $leaders = User::whereIn('id', $leaderIds)->orderBy('name')->get(['id', 'employeeID', 'name']);
            $options = $leaders->map(fn($u) => [
                'value' => 'L::' . $u->id,
                'label' => ($u->employeeID ?? 'LDR' . $u->id) . ' - ' . $u->name,
            ]);
            $targetLabel = 'ID & Nama Leader';
        } else {
            // leader cek operator
            $ops = ScheduleDetail::query()
                ->whereDate('scheduled_date', $date)
                ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
                ->whereNotNull('target_operator_id')
                ->whereNotNull('target_operator_name')
                ->select('target_operator_id', 'target_operator_name')
                ->distinct()
                ->orderBy('target_operator_name')
                ->get();

            $options = $ops->map(fn($r) => [
                'value' => 'O::' . $r->target_operator_id . '::' . $r->target_operator_name, // biar gampang parse
                'label' => $r->target_operator_id . ' - ' . $r->target_operator_name,
            ]);
            $targetLabel = 'ID & Nama Operator';
        }

        return view('control.checksheets.part-a', [
            'detail' => $detail,
            'type' => $type,
            'deptName' => $deptName,
            'options' => $options,
            'targetLabel' => $targetLabel,
            'role' => $me->role,
        ]);
    }

    // ==== PART B ====
    public function showPartB(ScheduleDetail $detail, Request $request)
    {
        session(['cl_in_progress' => true]);

        $ptype = $detail->plan->type ?? 'leader_checks_operator';
        $attendance = $request->query('attendance'); // '0' | '1' | null

        $questions = Question::query()
            ->where('is_active', 1)
            ->where('type', $ptype)
            ->when($attendance !== null, function ($q) use ($attendance) {
                $q->where(function ($p) use ($attendance) {
                    $p->whereNull('when_attendance')
                        ->orWhere('when_attendance', (int) $attendance);
                });
            })
            ->orderBy('display_order')
            ->get(['id', 'code', 'prompt', 'display_order']);

        return view('control.checksheets.part-b', compact('detail', 'questions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'schedule_detail_id' => ['required', 'exists:schedule_details,id'],
            'shift' => ['required', 'in:1,2,3'],
            'attendance' => ['required', 'in:0,1'],
            'target_pick' => ['required', 'string'], // "L::123" atau "O::57259::Budi Santoso"
            'stopwatch_duration' => ['required', 'integer', 'min:0'],
            // ... answers part B
        ]);

        $detail = ScheduleDetail::with('plan.scheduler')->findOrFail($data['schedule_detail_id']);
        $me = auth('web_control_leader')->user();
        $date = $detail->scheduled_date;

        // derive allowed options again (server-side trust no one)
        if ($me->role === 'Supervisor') {
            $allowed = ScheduleDetail::whereDate('scheduled_date', $date)
                ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
                ->whereNotNull('target_leader_id')
                ->pluck('target_leader_id')->unique()->map(fn($id) => "L::{$id}")->toArray();
        } else {
            $allowed = ScheduleDetail::whereDate('scheduled_date', $date)
                ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
                ->whereNotNull('target_operator_id')
                ->whereNotNull('target_operator_name')
                ->select('target_operator_id', 'target_operator_name')
                ->distinct()->get()
                ->map(fn($r) => "O::{$r->target_operator_id}::{$r->target_operator_name}")
                ->toArray();
        }

        abort_unless(in_array($data['target_pick'], $allowed, true), 422, 'Target tidak valid untuk jadwal ini.');

        // parse target
        $personField = '';
        if (str_starts_with($data['target_pick'], 'L::')) {
            $id = (int) substr($data['target_pick'], 3);
            $u = User::findOrFail($id);
            $personField = ($u->employeeID ?? 'LDR' . $u->id) . ' - ' . $u->name;
        } else { // O::<id>::<name>
            [, $opId, $opName] = explode('::', $data['target_pick'], 3);
            $personField = $opId . ' - ' . $opName;
        }

        $deptId = optional($detail->plan->scheduler)->department_id;

        \DB::transaction(function () use ($detail, $ptype, $base, $personField, $deptId) {
            $checksheet = Checksheet::create([
                'schedule_detail_id' => $detail->id,
                'type' => $ptype,
                'stopwatch_duration' => $base['stopwatch_duration'],
                'part_a_answer_1' => (int) $base['shift'],       // Shift
                'part_a_answer_2' => $personField,              // "ID - Nama"
                'part_a_answer_3' => (int) ($deptId ?? 0),       // Department dari scheduler
                'part_a_answer_4' => (int) $base['attendance'],  // Hadir/Absen
            ]);

            $ans = $base['answers'] ?? [];
            if (!empty($ans)) {
                $payload = array_map(fn($row) => [
                    'checksheet_id' => $checksheet->id,
                    'question_id' => (int) $row['question_id'],
                    'answer' => $row['answer'] ?? null,
                    'problem' => $row['problem'] ?? null,
                    'countermeasure' => $row['countermeasure'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $ans);
                ChecksheetAnswer::insert($payload);
            }
        });

        auth('web_control_leader')->user()?->forceFill(['cl_in_progress' => false])->save();

        return redirect()->route('control.checksheets.create', ['type' => $request->query('type')])
            ->with('ok', 'Checksheet tersimpan ✅');
    }

    /** commitTarget: simpan pilihan target (idempotent, tapi kita hanya catat kalau belum ada) */
    public function commitTarget(Request $r, ScheduleDetail $detail)
    {
        $me = auth('web_control_leader')->user();
        $pick = $r->input('target_pick'); // "L::123" atau "O::57259::Budi Santoso"

        if ($me->role === 'Supervisor') {
            if (!str_starts_with($pick, 'L::'))
                abort(422, 'Target tidak valid.');
            $leaderId = (int) substr($pick, 3);
            // validasi leaderId harus ada di jadwal tanggal itu + scheduler sama
            $ok = ScheduleDetail::whereDate('scheduled_date', $detail->scheduled_date)
                ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
                ->where('target_leader_id', $leaderId)
                ->exists();
            abort_unless($ok, 422, 'Target tidak valid.');
            if (!$detail->target_leader_id)
                $detail->forceFill(['target_leader_id' => $leaderId])->save();
        } else {
            if (!str_starts_with($pick, 'O::'))
                abort(422, 'Target tidak valid.');
            [, $opId, $opName] = explode('::', $pick, 3);
            $ok = ScheduleDetail::whereDate('scheduled_date', $detail->scheduled_date)
                ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
                ->where('target_operator_id', $opId)
                ->where('target_operator_name', $opName)
                ->exists();
            abort_unless($ok, 422, 'Target tidak valid.');
            if (!$detail->target_operator_id || !$detail->target_operator_name) {
                $detail->forceFill([
                    'target_operator_id' => $opId,
                    'target_operator_name' => $opName,
                ])->save();
            }
        }
        return response()->json(['ok' => true]);
    }

    public function finalize(Checksheet $checksheet)
    {
        // set status finalized_at/locked_by, dsb
        // redirect back
    }

    public function approve(Checksheet $checksheet)
    {
        // cek role sesuai type, set approved_at/approved_by
    }
}
