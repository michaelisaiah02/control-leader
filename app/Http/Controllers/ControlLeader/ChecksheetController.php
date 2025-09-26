<?php

namespace App\Http\Controllers\ControlLeader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ControlLeader\{
    ScheduleDetail,
    Checksheet,
    ChecksheetDraft,
    Question,
    ChecksheetAnswer,
    User
};
use App\Support\ControlLeader as CL;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ChecksheetController extends Controller
{
    private function phaseToPackage(string $phase, string $direction): string
    {
        if ($direction === 'supervisor_checks_leader')
            return 'leader';
        return match ($phase) {
            'awal_shift' => 'op_awal',
            'saat_bekerja' => 'op_bekerja',
            'setelah_istirahat' => 'op_istirahat',
            'akhir_shift' => 'op_akhir',
            default => 'op_awal',
        };
    }

    /** Arah penilaian diambil dari role user login */
    private function evalDirection(User $me): string
    {
        return $me->role === 'Supervisor'
            ? 'supervisor_checks_leader'
            : 'leader_checks_operator';
    }

    /** ===== HEARTBEAT (opsional) ===== */
    public function heartbeat()
    {
        return response()->json(['ok' => true]);
    }

    /** ===== BAGIAN A ===== */
    public function createPartA(Request $req)
    {
        $me = auth('web_control_leader')->user();
        $phase = $req->query('type', 'awal_shift');
        $dir = $this->evalDirection($me);

        // Stopwatch start time di-session (per-phase)
        $sessKey = "cl.started_at.$phase";
        if (!$req->session()->has($sessKey)) {
            $req->session()->put($sessKey, now()->getTimestampMs());
        }
        $startedAtMs = (int) $req->session()->get($sessKey);

        // Dept label
        $deptName = optional($me->department)->department_name ?? '';

        // Build opsi target BERDASARKAN scheduler=me, tanpa peduli tanggal (distinct)
        if ($dir === 'supervisor_checks_leader') {
            $targetLabel = 'ID & Nama Leader';
            $leaderIds = ScheduleDetail::query()
                ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
                ->whereNotNull('target_leader_id')
                ->distinct()
                ->pluck('target_leader_id');

            $leaders = User::whereIn('id', $leaderIds)->orderBy('name')->get(['id', 'employeeID', 'name']);

            $options = $leaders->map(fn($u) => [
                'value' => "L::{$u->id}",
                'label' => ($u->employeeID ?? "LDR{$u->id}") . ' - ' . $u->name,
            ])->values()->all();
        } else {
            $targetLabel = 'ID & Nama Operator';
            $ops = ScheduleDetail::query()
                ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
                ->whereNotNull('target_operator_id')
                ->whereNotNull('target_operator_name')
                ->select('target_operator_id', 'target_operator_name')
                ->distinct()
                ->orderBy('target_operator_name')
                ->get();

            $options = $ops->map(fn($r) => [
                'value' => "O::{$r->target_operator_id}::{$r->target_operator_name}",
                'label' => "{$r->target_operator_id} - {$r->target_operator_name}",
            ])->values()->all();
        }

        // Prefill dari session (kalau balik dari B → A)
        $draftKey = "cl.partA.$phase";
        $prefill = $req->session()->get($draftKey, []);

        return view('control.checksheets.part-a', [
            'phase' => $phase,
            'type' => $phase, // biar blade lama aman
            'startedAtMs' => $startedAtMs,
            'deptName' => $deptName,
            'targetLabel' => $targetLabel,
            'options' => $options,
            'prefill' => $prefill,
        ]);
    }

    /** Simpan pilihan target & page-1/2 Part A ke session */
    public function commitTarget(Request $req)
    {
        $me = auth('web_control_leader')->user();
        $phase = $req->input('phase');

        $data = $req->validate([
            'phase' => 'required|string',
            'shift' => 'required|in:1,2,3',
            'target_pick' => 'required|string', // O::id::name | L::id
            'bagian' => 'required|string',
            'attendance' => 'required|in:0,1',
            'nama_pengganti' => 'nullable|string',
            'bagian_pengganti' => 'nullable|string',
            'kondisi_pengganti' => 'nullable|in:Sehat,Sakit',
            'kondisi' => 'nullable|in:Sehat,Sakit',
        ]);

        // Simpan Part A ke session
        $req->session()->put("cl.partA.$phase", $data);

        return response()->json(['ok' => true]);
    }

    /** ===== BAGIAN B ===== */
    public function showPartB(Request $req)
    {
        $me = auth('web_control_leader')->user();
        $phase = $req->query('type', 'awal_shift');
        $dir = $this->evalDirection($me);

        // Harus punya Part A di session
        $partA = $req->session()->get("cl.partA.$phase");
        if (!$partA) {
            return redirect()->route('control.checksheets.create', ['type' => $phase])
                ->with('info', 'Lengkapi Bagian A terlebih dahulu.');
        }

        // Stopwatch dari session (tetap dari start awal)
        $startedAtMs = (int) $req->session()->get("cl.started_at.$phase", now()->getTimestampMs());

        // Ambil paket pertanyaan
        $package = $this->phaseToPackage($phase, $dir);
        $questions = Question::where('package', $package)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return view('control.checksheets.part-b', [
            'phase' => $phase,
            'type' => $phase,
            'dir' => $dir,
            'startedAtMs' => $startedAtMs,
            'partA' => $partA,
            'questions' => $questions,
        ]);
    }

    /** ===== FINAL SUBMIT ===== */
    public function store(Request $req)
    {
        $me = auth('web_control_leader')->user();
        $phase = $req->input('phase');

        // Pastikan ada Part A di session
        $partA = $req->session()->get("cl.partA.$phase");
        if (!$partA)
            return back()->with('error', 'Session Bagian A hilang, ulangi dari awal.');

        // Validasi jawaban B
        $validated = $req->validate([
            'phase' => 'required|string',
            'answers' => 'required|array', // answers[question_id] = value
            'problems' => 'array',
            'countermeasures' => 'array',
        ]);

        // Hitung durasi dari started_at (ms)
        $startedAtMs = (int) $req->session()->get("cl.started_at.$phase", now()->getTimestampMs());
        $duration = max(0, floor((now()->getTimestampMs() - $startedAtMs) / 1000));

        // Determine direction
        $dir = $this->evalDirection($me);

        // Cari/BUAT schedule_detail untuk target + TODAY (bebas pilih)
        [$kind, $id, $name] = explode('::', $partA['target_pick'] . '::::'); // safe explode

        DB::connection('mysql_control_leader')->beginTransaction();
        try {
            // Pastikan ada plan untuk scheduler ini + type (dir)
            $plan = SchedulePlan::firstOrCreate(
                [
                    'scheduler_id' => $me->id,
                    'type' => $dir,
                    'month' => now()->month,
                    'year' => now()->year,
                ],
                ['display_order' => 0]
            );

            // Cari detail by target + today, kalau tidak, buat
            $detailQuery = ScheduleDetail::where('schedule_plan_id', $plan->id)
                ->whereDate('scheduled_date', now()->toDateString());

            if ($dir === 'supervisor_checks_leader') {
                $detailQuery->where('target_leader_id', $id);
            } else {
                $detailQuery->where('target_operator_id', $id)
                    ->where('target_operator_name', $name);
            }

            $detail = $detailQuery->first();

            if (!$detail) {
                $detail = ScheduleDetail::create([
                    'schedule_plan_id' => $plan->id,
                    'target_leader_id' => $dir === 'supervisor_checks_leader' ? $id : null,
                    'target_operator_id' => $dir === 'leader_checks_operator' ? $id : null,
                    'target_operator_name' => $dir === 'leader_checks_operator' ? $name : null,
                    'scheduled_date' => now()->toDateString(),
                ]);
            }

            // Cegah double submit untuk hari ini & phase yang sama & target yg sama
            $already = Checksheet::where('schedule_detail_id', $detail->id)
                ->where('phase', $phase)->exists();
            if ($already) {
                DB::rollBack();
                return redirect()->route('control.checksheets.create', ['type' => $phase])
                    ->with('info', 'Checksheet hari ini untuk target & fase ini sudah ada.');
            }

            // Simpan header
            $cs = Checksheet::create([
                'schedule_detail_id' => $detail->id,
                'type' => $dir,
                'phase' => $phase,
                'stopwatch_duration' => $duration,
                // part A (4 kolom fix – mapping dari requirement kamu)
                'part_a_answer_1' => $partA['shift'] ?? null,
                'part_a_answer_2' => $partA['target_pick'] ?? null, // simpan string pick
                'part_a_answer_3' => $partA['bagian'] ?? null,
                'part_a_answer_4' => $partA['attendance'] ?? null,
            ]);

            // Simpan detail jawaban B
            $answers = $validated['answers'] ?? [];
            $probs = $validated['problems'] ?? [];
            $cms = $validated['countermeasures'] ?? [];

            foreach ($answers as $qid => $val) {
                ChecksheetAnswer::create([
                    'checksheet_id' => $cs->id,
                    'question_id' => $qid,
                    'answer' => is_array($val) ? json_encode($val) : (string) $val,
                    'problem' => $probs[$qid] ?? null,
                    'countermeasure' => $cms[$qid] ?? null,
                ]);
            }

            DB::commit();

            // Clear session draft phase
            $req->session()->forget("cl.partA.$phase");
            $req->session()->forget("cl.started_at.$phase");

            return redirect()->route('control.dashboard')->with('ok', 'Checksheet tersimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Gagal menyimpan checksheet.');
        }
    }

    public function startDraft(Request $request)
    {
        $user = auth('web_control_leader')->user();

        $data = $request->validate([
            'schedule_detail_id' => 'required|exists:mysql_control_leader.schedule_details,id',
            'phase' => 'required|string|in:awal_shift,saat_bekerja,setelah_istirahat,akhir_shift',
            'started_at_ms' => 'nullable|numeric', // dari sessionStorage
        ]);

        $startedAt = isset($data['started_at_ms'])
            ? Carbon::createFromTimestampMs((int) $data['started_at_ms'])
            : now();

        $draft = ChecksheetDraft::updateOrCreate(
            [
                'user_id' => $user->id,
                'schedule_detail_id' => $data['schedule_detail_id'],
                'phase' => $data['phase'],
            ],
            [
                'session_id' => session()->getId(),
                'started_at' => $startedAt,
                'last_ping' => now(),
                'is_active' => true,
            ]
        );

        return response()->json(['ok' => true, 'draft_id' => $draft->id]);
    }
}
