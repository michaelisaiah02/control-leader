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
use App\Models\ControlLeader\SchedulePlan;
use App\Models\ControlLeader\ScheduleDetail;
use App\Models\ControlLeader\ChecksheetAnswer;

class ChecksheetController extends Controller
{
    // ==== PART A ====
    public function createPartAToday(Request $request)
    {
        session(['cl_in_progress' => true]);

        $slot = $request->query('type'); // awal_shift | saat_bekerja | setelah_istirahat | akhir_shift
        $user = auth('web_control_leader')->user();
        $today = Carbon::today();
        $ptype = $this->inferPlanType($user); // 'leader_checks_operator' | 'supervisor_checks_leader'

        // Plan bulan ini (sederhana; tambah kolom periode kalau perlu)
        $plan = SchedulePlan::firstOrCreate(
            ['type' => $ptype],
            ['name' => strtoupper($ptype) . ' ' . $today->format('F Y')]
        );

        // Detail untuk hari ini oleh evaluator = user sekarang.
        // target_user_id hanya dipakai ketika supervisor menilai leader.
        $defaultDivisionId = Division::orderBy('id')->value('id'); // boleh null kalau nggak ada
        $targetLeader = ($ptype === 'supervisor_checks_leader')
            ? User::where('role', 'Leader')->orderBy('id')->first()
            : null; // leader_checks_operator: operator tak punya akun

        $detail = ScheduleDetail::firstOrCreate(
            [
                'schedule_plan_id' => $plan->id,
                'scheduled_date' => $today->toDateString(),
                'evaluator_id' => $user->id,
            ],
            [
                'target_user_id' => $targetLeader?->id, // null jika operator
                'division_id' => $defaultDivisionId,
            ]
        );

        // Dropdowns:
        $leaders = User::where('role', 'Leader')->orderBy('name')->get(['id', 'name']); // hanya dipakai saat SCL
        $divisions = Division::orderBy('name')->get(['id', 'name']);

        return view('control.checksheets.part-a', [
            'detail' => $detail,
            'planType' => $ptype,
            'leaders' => $leaders,
            'divisions' => $divisions,
            'slot' => $slot,
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
        // Pertama, ambil detail untuk tahu plan type
        $detail = ScheduleDetail::with('plan')->findOrFail($request->input('schedule_detail_id'));
        $ptype = $detail->plan->type;

        // Validasi Part A tergantung tipe plan:
        // - leader_checks_operator  => butuh operator_id & operator_name (manual text)
        // - supervisor_checks_leader => butuh leader_id (person_id dari users)
        $baseA = $request->validate([
            'schedule_detail_id' => ['required', 'integer', 'exists:schedule_details,id'],
            'shift' => ['required', 'in:1,2,3'],
            'division_id' => ['required', 'integer'],
            'attendance' => ['required', 'in:0,1'],
            'stopwatch_duration' => ['required', 'integer', 'min:0'],
        ]);

        if ($ptype === 'leader_checks_operator') {
            $partAExtra = $request->validate([
                'operator_id' => ['required', 'string', 'max:100'],
                'operator_name' => ['required', 'string', 'max:200'],
            ]);
            // Gabungkan ID & Nama sesuai spesifikasi Part A (jawaban ke-2 adalah "ID & Nama")
            $personCompound = trim($partAExtra['operator_id'] . ' - ' . $partAExtra['operator_name']);
        } else { // supervisor_checks_leader
            $partAExtra = $request->validate([
                'person_id' => ['required', 'integer', 'exists:users,id'], // leader id
            ]);
            $leader = User::find($partAExtra['person_id']);
            $personCompound = $leader ? ($leader->employeeID ?? ('LDR' . $leader->id)) . ' - ' . $leader->name : 'UNKNOWN';
        }

        // Validasi Part B
        $dataB = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'answers.*.answer' => ['nullable', 'string'],
            'answers.*.problem' => ['nullable', 'string'],
            'answers.*.countermeasure' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($detail, $ptype, $baseA, $personCompound, $dataB) {
            $checksheet = Checksheet::create([
                'schedule_detail_id' => $detail->id,
                'type' => $ptype,
                'stopwatch_duration' => $baseA['stopwatch_duration'],

                // Part A fixed fields:
                'part_a_answer_1' => (int) $baseA['shift'],       // Shift 1/2/3
                'part_a_answer_2' => $personCompound,            // "ID - Nama" (operator manual atau leader)
                'part_a_answer_3' => (int) $baseA['division_id'], // Division ID
                'part_a_answer_4' => (int) $baseA['attendance'],  // 1 hadir / 0 absen
            ]);

            if (!empty($dataB['answers'])) {
                $payload = array_map(function ($row) use ($checksheet) {
                    return [
                        'checksheet_id' => $checksheet->id,
                        'question_id' => (int) $row['question_id'],
                        'answer' => $row['answer'] ?? null,
                        'problem' => $row['problem'] ?? null,
                        'countermeasure' => $row['countermeasure'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $dataB['answers']);

                ChecksheetAnswer::insert($payload);
            }
        });

        session()->forget('cl_in_progress');

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
