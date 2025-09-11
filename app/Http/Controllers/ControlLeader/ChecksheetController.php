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
        $ptype = $this->inferPlanType($me);                    // leader_checks_operator | supervisor_checks_leader

        // 1) CARI DETAIL HARI INI milik plan yang dibuat oleh user (scheduler_id = saya)
        $today = Carbon::today()->toDateString();

        $detailToday = ScheduleDetail::with(['plan.scheduler'])
            ->whereDate('scheduled_date', $today)
            ->whereHas('plan', function ($q) use ($ptype, $me) {
                $q->where('type', $ptype)
                    ->where('scheduler_id', $me->id);           // <-- hanya plan yang dibuat saya
            })
            ->where('evaluator_id', $me->id)                  // evaluator = user yang mengisi
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
            ->where('evaluator_id', $me->id)
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
            ['name' => strtoupper($ptype) . ' ' . Carbon::parse($date)->format('F Y')]
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
                'evaluator_id' => $me->id,
            ],
            [
                'target_user_id' => $targetLeader?->id,      // null jika operator
                // department_id akan diambil dari $plan->scheduler saat simpan (bukan di sini)
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
        ]);

        $detail = ScheduleDetail::with('plan.scheduler')->findOrFail($base['schedule_detail_id']);
        $ptype = $detail->plan->type;

        if ($ptype === 'leader_checks_operator') {
            $extra = $request->validate([
                'operator_id' => ['required', 'string', 'max:100'],
                'operator_name' => ['required', 'string', 'max:200'],
            ]);
            $personField = trim($extra['operator_id'] . ' - ' . $extra['operator_name']);
        } else {
            $extra = $request->validate([
                'person_id' => ['required', 'integer', 'exists:users,id'],
            ]);
            $ldr = User::find($extra['person_id']);
            $personField = ($ldr->employeeID ?? ('LDR' . $ldr->id)) . ' - ' . $ldr->name;
        }

        $answers = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'answers.*.answer' => ['nullable', 'string'],
            'answers.*.problem' => ['nullable', 'string'],
            'answers.*.countermeasure' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($detail, $ptype, $base, $personField, $answers) {
            $deptId = optional($detail->plan->scheduler)->department_id; // <-- ambil dari scheduler

            $checksheet = Checksheet::create([
                'schedule_detail_id' => $detail->id,
                'type' => $ptype,
                'stopwatch_duration' => $base['stopwatch_duration'],
                'part_a_answer_1' => (int) $base['shift'],     // Shift
                'part_a_answer_2' => $personField,            // ID & Nama
                'part_a_answer_3' => (int) ($deptId ?? 0),     // Department ID dari scheduler
                'part_a_answer_4' => (int) $base['attendance'],// Hadir/Absen
            ]);

            if (!empty($answers['answers'])) {
                $payload = array_map(fn($row) => [
                    'checksheet_id' => $checksheet->id,
                    'question_id' => (int) $row['question_id'],
                    'answer' => $row['answer'] ?? null,
                    'problem' => $row['problem'] ?? null,
                    'countermeasure' => $row['countermeasure'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $answers['answers']);
                ChecksheetAnswer::insert($payload);
            }
        });

        // clear lock
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
