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
    public function createPartAToday(Request $request)
    {
        session(['cl_in_progress' => true]);

        $slot = $request->query('type');                      // subjudul
        $dateQ = $request->query('date');                      // optional (YYYY-MM-DD) dari picker
        $me = auth('web_control_leader')->user();
        $ptype = $this->inferPlanType($me);
        $today = Carbon::today()->toDateString();

        $detailToday = ScheduleDetail::with(['plan.scheduler'])
            ->whereDate('scheduled_date', $today)
            ->whereHas('plan', function ($q) use ($ptype, $me) {
                $q->where('type', $ptype)
                    ->where('scheduler_id', $me->id);   // <= pembuat jadwal = user login
            })
            ->first();


        if ($detailToday && !$dateQ) {
            return $this->renderPartA($detailToday, $ptype, $slot);
        }

        // 2) KUMPULKAN TANGGAL YANG DIIJINKAN (hanya dari plan buatan saya)
        $allowedDates = ScheduleDetail::query()
            ->select('scheduled_date')
            ->whereHas('plan', function ($q) use ($ptype, $me) {
                $q->where('type', $ptype)
                    ->where('scheduler_id', $me->id);
            })
            ->orderBy('scheduled_date', 'desc')
            ->pluck('scheduled_date')
            ->unique()
            ->values();


        // Kalau belum pilih tanggal & hari ini tidak ada → tampilkan picker terbatas
        if (!$dateQ && !$detailToday) {
            return view('control.checksheets.pick-date', [
                'slot' => $slot,
                'allowedDates' => $allowedDates,              // hanya tanggal yang boleh
            ]);
        }

        // 3) VALIDASI tanggal pilihan: harus termasuk allowedDates
        $date = Carbon::parse($dateQ ?? $today)->toDateString();
        if ($allowedDates->isNotEmpty() && !$allowedDates->contains($date)) {
            abort(403, 'Tanggal tidak diizinkan untuk kamu.');
        }

        // 4) AMBIL/BUAT PLAN untuk saya sebagai scheduler
        $plan = SchedulePlan::firstOrCreate(
            ['type' => $ptype, 'scheduler_id' => $me->id],
            ['name' => strtoupper($ptype) . ' ' . \Carbon\Carbon::parse($date)->format('F Y')]
        );



        // target_user_id (leader) hanya saat supervisor_checks_leader
        $targetLeader = $ptype === 'supervisor_checks_leader'
            ? User::where('role', 'Leader')->orderBy('id')->first()
            : null;

        // 5) AMBIL/BUAT DETAIL untuk tanggal pilihan
        $detail = ScheduleDetail::firstOrCreate(
            [
                'schedule_plan_id' => $plan->id,
                'scheduled_date' => $date,
            ],
            [
                'target_user_id' => $targetLeader?->id, // null kalau operator
                // dept diambil saat simpan dari $plan->scheduler->department_id
            ]
        );

        return $this->renderPartA($detail->load('plan.scheduler'), $ptype, $slot);
    }

    // helper render (hapus kebutuhan division select)
    private function renderPartA($detail, string $ptype, ?string $slot)
    {
        $leaders = User::where('role', 'Leader')->orderBy('name')->get(['id', 'name', 'employeeID']);
        $schedulerDeptId = optional($detail->plan->scheduler)->department_id;
        $schedulerDeptName = optional($detail->plan->scheduler?->department)->department_name ?? '—';

        return view('control.checksheets.part-a', [
            'detail' => $detail,
            'planType' => $ptype,
            'leaders' => $leaders,
            'slot' => $slot,
            'schedulerDeptId' => $schedulerDeptId,
            'schedulerDeptName' => $schedulerDeptName,
        ]);
    }

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
        $base = $request->validate([
            'schedule_detail_id' => ['required', 'integer', 'exists:schedule_details,id'],
            'shift' => ['required', 'in:1,2,3'],
            'attendance' => ['required', 'in:0,1'],
            'stopwatch_duration' => ['required', 'integer', 'min:0'],
            'answers' => ['nullable', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'answers.*.answer' => ['nullable', 'string'],
            'answers.*.problem' => ['nullable', 'string'],
            'answers.*.countermeasure' => ['nullable', 'string'],
        ]);

        $detail = ScheduleDetail::with(['plan.scheduler', 'targetLeader'])->findOrFail($base['schedule_detail_id']);
        $ptype = $detail->plan->type;

        // Tentukan personField dari detail (bukan dari request)
        if ($ptype === 'supervisor_checks_leader') {
            if (!$detail->targetLeader)
                abort(422, 'Target Leader belum dipilih.');
            $personField = ($detail->targetLeader->employeeID ?? ('LDR' . $detail->targetLeader->id)) . ' - ' . $detail->targetLeader->name;
        } else {
            if (!$detail->target_operator_id || !$detail->target_operator_name)
                abort(422, 'Target Operator belum diisi.');
            $personField = $detail->target_operator_id . ' - ' . $detail->target_operator_name;
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

    private function inferPlanType($user): string
    {
        // mapping sederhana sesuai 4 role: Admin, Guest, Supervisor, Leader
        return match (strtolower($user->role ?? '')) {
            'supervisor' => 'supervisor_checks_leader',
            default => 'leader_checks_operator', // Leader (atau lainnya) menilai Operator
        };
    }

    public function targetsJson(ScheduleDetail $detail)
    {
        $detail->load(['plan.scheduler', 'targetLeader']);
        $scheduler = $detail->plan->scheduler;
        $deptName = optional($scheduler?->department)->department_name ?? '—';
        $date = $detail->scheduled_date;              // ← tanggal yang sedang dibuka
        $planId = $detail->schedule_plan_id;

        // SUPERVISOR → cek Leader, opsi = leader yang TERJADWAL di tanggal itu (dari schedule_details)
        if ($scheduler?->role === 'Supervisor') {
            // sudah terkunci?
            if ($detail->target_leader_id && $detail->targetLeader) {
                return response()->json([
                    'mode' => 'locked_leader',
                    'departmentName' => $deptName,
                    'selected' => [
                        'id' => (string) $detail->targetLeader->id,
                        'label' => ($detail->targetLeader->employeeID ?? 'LDR' . $detail->targetLeader->id) . ' - ' . $detail->targetLeader->name,
                    ],
                ]);
            }

            // ambil semua detail “saudara” pada tanggal ini di plan yang sama yg SUDAH ada target_leader_id
            $leaderIds = ScheduleDetail::query()
                ->where('schedule_plan_id', $planId)
                ->whereDate('scheduled_date', $date)
                ->whereNotNull('target_leader_id')
                ->pluck('target_leader_id')
                ->unique()
                ->values();

            $leaders = User::whereIn('id', $leaderIds)
                ->orderBy('name')->get(['id', 'name', 'employeeID']);

            return response()->json([
                'mode' => $leaders->isEmpty() ? 'select_leader_empty' : 'select_leader',
                'departmentName' => $deptName,
                'field' => ['name' => 'person_id', 'label' => 'Leader (ID & Nama)'],
                'options' => $leaders->map(fn($u) => [
                    'value' => (string) $u->id,
                    'label' => ($u->employeeID ?? 'LDR' . $u->id) . ' - ' . $u->name,
                ])->values(),
            ]);
        }

        // default: LEADER → cek Operator, opsi = operator TERJADWAL di tanggal itu (dari schedule_details)
        if ($detail->target_operator_id && $detail->target_operator_name) {
            return response()->json([
                'mode' => 'locked_operator',
                'departmentName' => $deptName,
                'selected' => [
                    'id' => $detail->target_operator_id,
                    'label' => $detail->target_operator_id . ' - ' . $detail->target_operator_name,
                ],
            ]);
        }

        $ops = ScheduleDetail::query()
            ->where('schedule_plan_id', $planId)
            ->whereDate('scheduled_date', $date)
            ->whereNotNull('target_operator_id')
            ->whereNotNull('target_operator_name')
            ->select('target_operator_id', 'target_operator_name')
            ->distinct()
            ->orderBy('target_operator_name')
            ->get();

        if ($ops->isNotEmpty()) {
            // value pakai delimiter supaya bisa di-split di JS → "ID@@Nama"
            $options = $ops->map(fn($r) => [
                'value' => $r->target_operator_id . '@@' . $r->target_operator_name,
                'label' => $r->target_operator_id . ' - ' . $r->target_operator_name,
            ])->values();

            return response()->json([
                'mode' => 'select_operator_from_schedule',
                'departmentName' => $deptName,
                'field' => ['name' => 'operator_pick', 'label' => 'ID & Nama Operator'],
                'options' => $options,
            ]);
        }

        // kalau tanggal itu belum ada operator di jadwal → manual
        return response()->json([
            'mode' => 'manual_operator',
            'departmentName' => $deptName,
            'field_id' => ['id' => 'operator_id', 'label' => 'ID Operator', 'placeholder' => 'OPxxx'],
            'field_name' => ['id' => 'operator_name', 'label' => 'Nama Operator', 'placeholder' => 'Nama Lengkap'],
        ]);
    }

    public function commitTarget(Request $request, ScheduleDetail $detail)
    {
        $detail->load('plan');
        $ptype = $detail->plan->type;

        if ($ptype === 'supervisor_checks_leader') {
            $data = $request->validate(['person_id' => ['required', 'integer', 'exists:users,id']]);
            // hanya set jika belum ada (jaga idempotensi)
            if (!$detail->target_leader_id) {
                $detail->forceFill(['target_leader_id' => $data['person_id']])->save();
            }
            return response()->json(['ok' => true]);
        }

        // leader_checks_operator
        $data = $request->validate([
            'operator_id' => ['required', 'string', 'max:100'],
            'operator_name' => ['required', 'string', 'max:200'],
        ]);
        if (!$detail->target_operator_id || !$detail->target_operator_name) {
            $detail->forceFill([
                'target_operator_id' => $data['operator_id'],
                'target_operator_name' => $data['operator_name'],
            ])->save();
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
