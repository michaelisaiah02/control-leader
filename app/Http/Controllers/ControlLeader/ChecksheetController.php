<?php

namespace App\Http\Controllers\ControlLeader;

use App\Http\Controllers\Controller;
use App\Models\Checksheet;
use App\Models\ChecksheetAnswer;
use App\Models\Division;
use App\Models\Question;
use App\Models\ScheduleDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChecksheetController extends Controller
{
    // ==== PART A ====
    public function createPartA(ScheduleDetail $detail)
    {
        // Flag biar gak bisa logout auto / manual (kalau kamu pakai logika itu)
        session(['cl_in_progress' => true]);

        // Ambil tipe plan: leader_checks_operator | supervisor_checks_leader
        $type = $detail->plan->type ?? 'leader_checks_operator';

        // --- Data dropdown (SILAKAN SESUAIKAN QUERY) ---
        if ($type === 'leader_checks_operator') {
            // Ambil list operator aktif untuk pilihan "ID & Nama"
            $people = User::query()
                ->where('role', 'operator')       // TODO: sesuaikan field/relasi rolenya
                ->orderBy('name')
                ->get(['id', 'name']);
        } else { // supervisor_checks_leader
            $people = User::query()
                ->where('role', 'leader')         // TODO: sesuaikan
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $divisions = Division::query()->orderBy('name')->get(['id', 'name']); // kalau gak ada, kirim array kosong

        // Judul sub-judul (opsional) — kamu sudah punya switch-case $type shift; kirim apa pun yang kamu butuh
        $shiftTypes = ['awal_shift', 'saat_bekerja', 'setelah_istirahat', 'akhir_shift']; // contoh

        return view('control.checksheets.part-a', [
            'detail' => $detail,
            'planType' => $type,
            'people' => $people,
            'divisions' => $divisions,
            'shiftTypes' => $shiftTypes,
        ]);
    }

    // ==== PART B ====
    public function showPartB(ScheduleDetail $detail, Request $request)
    {
        session(['cl_in_progress' => true]); // tetap on

        // Ambil “attendance” dari sessionStorage saat submit nanti.
        // Di sini kita cuma butuh filter question berdasarkan type plan & (opsional) attendance.
        $type = $detail->plan->type ?? 'leader_checks_operator';

        // NOTE: kalau kamu perlu filter by attendance, kirimkan via querystring dari JS
        $attendance = $request->query('attendance'); // '0' | '1' | null

        $questions = Question::query()
            ->where('is_active', 1)
            ->where('type', $type) // pastikan kolom type di questions cocok: leader_checks_operator / supervisor_checks_leader
            ->when($attendance !== null, function ($q) use ($attendance) {
                $q->where(function ($p) use ($attendance) {
                    $p->whereNull('when_attendance')
                        ->orWhere('when_attendance', (int) $attendance); // 0 atau 1
                });
            })
            ->orderBy('display_order')
            ->get(['id', 'code', 'prompt', 'display_order']);

        return view('control.checksheets.part-b', [
            'detail' => $detail,
            'questions' => $questions,
        ]);
    }

    // ==== FINAL SUBMIT (SAVE KE DB SEKALI) ====
    public function store(Request $request)
    {
        // Validasi Part A (datang dari hidden inputs yang diisi JS Part B)
        $dataA = $request->validate([
            'schedule_detail_id' => ['required', 'integer', 'exists:schedule_details,id'],
            'shift' => ['required', 'in:1,2,3'],
            'person_id' => ['required', 'integer'], // operator_id / leader_id → disatukan: person_id
            'division_id' => ['required', 'integer'],
            'attendance' => ['required', 'in:0,1'],
            'stopwatch_duration' => ['required', 'integer', 'min:0'],
        ]);

        // Validasi Part B
        $dataB = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'answers.*.answer' => ['nullable', 'string'],
            'answers.*.problem' => ['nullable', 'string'],
            'answers.*.countermeasure' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($dataA, $dataB) {
            $detail = ScheduleDetail::with('plan')->findOrFail($dataA['schedule_detail_id']);

            $checksheet = Checksheet::create([
                'schedule_detail_id' => $detail->id,
                'type' => $detail->plan->type, // leader_checks_operator | supervisor_checks_leader
                'stopwatch_duration' => $dataA['stopwatch_duration'],

                // Part A fixed:
                'part_a_answer_1' => (int) $dataA['shift'],        // shift (1/2/3)
                'part_a_answer_2' => (int) $dataA['person_id'],    // id & nama (ID saja; nama bisa join saat view)
                'part_a_answer_3' => (int) $dataA['division_id'],  // division_id
                'part_a_answer_4' => (int) $dataA['attendance'],   // hadir(1)/absen(0)
            ]);

            if (! empty($dataB['answers'])) {
                // Sisipkan checksheet_id untuk setiap jawaban
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

        // selesai → clear flag anti-logout
        session()->forget('cl_in_progress');

        return redirect()->route('control.checksheets.create', request('schedule_detail_id'))
            ->with('ok', 'Checksheet tersimpan ✅');
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
