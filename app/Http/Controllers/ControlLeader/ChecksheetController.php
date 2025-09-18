<?php

namespace App\Http\Controllers\ControlLeader;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ControlLeader\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ControlLeader\Question;
use App\Models\ControlLeader\Checksheet;
use App\Models\ControlLeader\ScheduleDetail;
use App\Models\ControlLeader\ChecksheetDraft;
use App\Models\ControlLeader\ChecksheetAnswer;

class ChecksheetController extends Controller
{
    // ==== PART A ====
    public function createPartA(Request $req)
    {
        $me = auth('web_control_leader')->user();
        $phase = $req->query('type', 'awal_shift');            // nama fase buat subtitle
        $date = $req->query('date', now()->toDateString());   // tanggal dipilih / hari ini

        // 1) Ambil detail untuk tanggal ini + scheduler = user login
        $detail = ScheduleDetail::with(['plan.scheduler.department'])
            ->whereDate('scheduled_date', $date)
            ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
            ->orderBy('id')
            ->first();

        if (!$detail) {
            return redirect()
                ->route('control.checksheets.pickDate', ['type' => $phase])
                ->with('info', 'Tidak ada jadwal untuk tanggal ini. Silakan pilih tanggal.');
        }

        // 2) Jika SUDAH SUBMIT untuk fase ini → anggap unavailable
        $already = Checksheet::where('phase', $phase)
            ->where('schedule_detail_id', $detail->id)
            ->exists();

        if ($already) {
            return redirect()
                ->route('control.checksheets.pickDate', ['type' => $phase])
                ->with('info', 'Tanggal tersebut sudah disubmit untuk fase ini.');
        }

        // 3) Draft per-fase: untuk stopwatch & auto-resume
        $draft = ChecksheetDraft::firstOrCreate(
            ['user_id' => $me->id, 'schedule_detail_id' => $detail->id, 'phase' => $phase],
            ['session_id' => session()->getId(), 'started_at' => now(), 'last_ping' => now(), 'is_active' => true]
        );
        // refresh sesi tiap buka
        $draft->forceFill(['session_id' => session()->getId(), 'last_ping' => now(), 'is_active' => true])->save();

        $startedAtMs = $draft->started_at?->getTimestampMs() ?? now()->getTimestampMs();

        // 4) Build dropdown target + label berdasarkan arah penilaian
        $direction = $detail->plan->type; // 'leader_checks_operator' | 'supervisor_checks_leader'
        $deptName = optional($detail->plan->scheduler?->department)->department_name ?? '';

        if ($direction === 'supervisor_checks_leader' || $me->role === 'Supervisor') {
            // Supervisor menilai Leader
            $targetLabel = 'ID & Nama Leader';
            $leaderIds = ScheduleDetail::query()
                ->whereDate('scheduled_date', $date)
                ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
                ->whereNotNull('target_leader_id')
                ->pluck('target_leader_id')->unique()->values();

            $leaders = User::whereIn('id', $leaderIds)
                ->orderBy('name')
                ->get(['id', 'employeeID', 'name']);

            $options = $leaders->map(fn($u) => [
                'value' => 'L::' . $u->id,
                'label' => ($u->employeeID ?? ('LDR' . $u->id)) . ' - ' . $u->name,
            ])->all();
        } else {
            // Leader menilai Operator
            $targetLabel = 'ID & Nama Operator';
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
                'value' => 'O::' . $r->target_operator_id . '::' . $r->target_operator_name,
                'label' => $r->target_operator_id . ' - ' . $r->target_operator_name,
            ])->all();
        }

        // 5) Render Part A – kirim SEMUA variabel yang dipakai Blade
        return view('control.checksheets.part-a', [
            'detail' => $detail,
            'phase' => $phase,
            'type' => $phase,      // supaya Blade lama yang pakai $type tetap aman
            'startedAtMs' => $startedAtMs,
            'deptName' => $deptName,
            'targetLabel' => $targetLabel,
            'options' => $options,
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
        $me = auth('web_control_leader')->user();
        $phase = $request->query('type', 'awal_shift');

        $data = $request->validate([
            'schedule_detail_id' => 'required|exists:mysql_control_leader.schedule_details,id',
            // field lain...
        ]);

        $detail = ScheduleDetail::findOrFail($data['schedule_detail_id']);

        // jangan double submit
        if (
            Checksheet::where('phase', $phase)
                ->where('schedule_detail_id', $detail->id)->exists()
        ) {
            return back()->with('error', 'Checksheet sudah pernah disubmit.');
        }

        // ambil draft untuk hitung durasi
        $draft = ChecksheetDraft::where('user_id', $me->id)
            ->where('schedule_detail_id', $detail->id)->where('phase', $phase)->first();

        $duration = $draft && $draft->started_at
            ? $draft->started_at->diffInSeconds(now())
            : 0;

        Checksheet::create([
            'schedule_detail_id' => $detail->id,
            'type' => $detail->plan->type,
            'phase' => $phase,
            'stopwatch_duration' => $duration,
            'part_a_answer_1' => $request->input('part_a_answer_1'),
            'part_a_answer_2' => $request->input('part_a_answer_2'),
            'part_a_answer_3' => $request->input('part_a_answer_3'),
            'part_a_answer_4' => $request->input('part_a_answer_4'),
        ]);

        if ($draft) {
            $draft->update(['is_active' => false]);
        }

        return redirect()->route('control.dashboard')->with('ok', 'Checksheet tersimpan');
    }

    /** commitTarget: simpan pilihan target (idempotent, tapi kita hanya catat kalau belum ada) */
    public function commitTarget(Request $request, ScheduleDetail $detail)
    {
        $me = auth('web_control_leader')->user();
        $pick = $request->input('target_pick'); // "L::123" atau "O::57259::Budi Santoso"

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

    public function pickDate(Request $request)
    {
        $me = auth('web_control_leader')->user();
        $phase = $request->query('type', 'awal_shift');

        // tanggal yg punya jadwal utk user ini
        $dates = ScheduleDetail::query()
            ->whereBetween('scheduled_date', [now()->subDays(60), now()->addDays(60)])
            ->whereHas('plan', fn($q) => $q->where('scheduler_id', $me->id))
            ->selectRaw('DATE(scheduled_date) d')->distinct()->pluck('d');

        // tanggal yg sudah ada checksheet utk fase ini
        $doneDates = Checksheet::query()
            ->where('phase', $phase)
            ->whereHas('detail.plan', fn($q) => $q->where('scheduler_id', $me->id))
            ->selectRaw('DATE(schedule_details.scheduled_date) d')
            ->join('schedule_details', 'schedule_details.id', '=', 'check-sheets.schedule_detail_id')
            ->pluck('d')->unique();

        // saring yang belum dikerjakan
        $available = $dates->diff($doneDates)->values();

        return view('control.checksheets.pick-date', [
            'type' => $phase,
            'dates' => $available,
        ]);
    }

    public function heartbeat()
    {
        $me = auth('web_control_leader')->user();
        ChecksheetDraft::where('user_id', $me->id)
            ->where('session_id', session()->getId())
            ->where('is_active', true)
            ->update(['last_ping' => now()]);
        return response()->noContent();
    }

    private function packageFromContext(string $phase, string $direction): string
    {
        // direction: 'leader_checks_operator' | 'supervisor_checks_leader'
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
