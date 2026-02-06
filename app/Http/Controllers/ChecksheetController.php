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

class ChecksheetController extends Controller
{
    // map phase -> package untuk Question
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
        // bila role supervisor → dia menilai leader, selain itu leader menilai operator
        return $me->role === 'Supervisor' ? 'supervisor_checks_leader' : 'leader_checks_operator';
    }

    public function createPartA(Request $req)
    {
        $me = auth()->user();
        $phase = $req->query('type', 'awal_shift');
        // Ambil plan paling baru milik scheduler=me untuk direction sesuai role
        $direction = $this->directionFor($me);
        $plan = SchedulePlan::where('scheduler_id', $me->employeeID)
            ->where('type', $direction)
            ->orderByDesc('year')->orderByDesc('month')->first();

        if (! $plan) {
            return back()->with('error', 'Belum ada Schedule Plan untuk Anda.');
        }

        // Opsi target dari DETAILS plan terbaru (tanpa tanggal di label)
        if ($direction === 'supervisor_checks_leader') {
            $leaders = User::where('role', 'leader')->where('is_active', true)->orderBy('name')->get();
            $targetLabel = 'ID & Nama Leader';
            $options = $leaders->map(fn($u) => [
                'value' => 'U::' . $u->id,
                'label' => ($u->employeeID ?? "LDR{$u->id}") . ' - ' . $u->name,
            ])->all();
        } else {
            $scheduleDetails = ScheduleDetail::where('schedule_plan_id', $plan->id)
                ->whereNotNull('target_user_id')
                ->with('targetUser')
                ->get();
            dd($scheduleDetails);
            $targetLabel = 'ID & Nama Operator';
            $options = $scheduleDetails->map(fn($detail) => [
                'value' => "{$detail->id}::{$detail->target_user_id}::{$detail->division}",
                'label' => ($detail->targetUser->employeeID ?: "OP{$detail->target_user_id}") . ' - ' . $detail->targetUser->name,
            ])->all();
        }

        // Draft (key: user_id + plan + phase)
        $draft = ChecksheetDraft::where('user_id', $me->id)
            ->where('schedule_plan_id', $plan->id)
            ->where('phase', $phase)
            ->first();

        // Jika draft ada dan last_ping lebih dari 45 detik, hapus dan buat baru
        if ($draft && $draft->last_ping && $draft->last_ping->diffInSeconds(now()) > 45) {
            $draft->delete();
            $draft = false;
        }

        // Buat draft baru jika belum ada
        if (! $draft) {
            $draft = ChecksheetDraft::updateOrCreate([
                'user_id' => $me->id,
                'schedule_plan_id' => $plan->id,
                'phase' => $phase,
                'session_id' => session()->getId(),
                'started_at' => now(),
                'last_ping' => now(),
                'is_active' => true,
            ]);
        } else {
            // Update session_id dan is_active tanpa mengubah last_ping
            $draft->update([
                'session_id' => session()->getId(),
                'is_active' => true,
            ]);
        }

        return view('checksheets.part-a', [
            'phase' => $phase,
            'plan' => $plan,
            'startedAtMs' => $draft->started_at?->getTimestampMs() ?? now()->getTimestampMs(),
            'targetLabel' => $targetLabel,
            'options' => $options,
        ]);
    }

    public function startDraft(Request $r)
    {
        $me = auth()->user();
        $data = $r->validate([
            'schedule_plan_id' => 'required|exists:schedule_plans,id',
            'phase' => 'required|string',
        ]);
        // Delete draft lama dengan kriteria sama yang sudah tidak aktif
        ChecksheetDraft::where('user_id', $me->id)->where('schedule_plan_id', $data['schedule_plan_id'])
            ->where('phase', $data['phase'])->where('is_active', false)->delete();

        // Buat atau update draft aktif
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
        $me = auth()->user();
        ChecksheetDraft::where('user_id', $me->id)->where('session_id', session()->getId())
            ->update(['last_ping' => now()]);

        return response()->json(['ok' => true]);
    }

    public function showPartB(Request $req)
    {
        $me = auth()->user();
        $phase = $req->query('type', 'awal_shift');

        $planId = (int) $req->query('plan');
        $plan = SchedulePlan::findOrFail($planId);

        $direction = $this->directionFor($me);
        $package = $this->packageFor($phase, $direction);

        $questions = Question::where('package', $package)->where('is_active', true)
            ->orderBy('display_order')->get();

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

    private function userLabel(string $uid): string
    {
        $u = User::find($uid);
        if (! $u) {
            return '';
        }
        $code = $u->employeeID ?: ($u->role === 'leader' ? 'LDR' . $u->id : 'OP' . $u->id);

        return $code;
    }

    public function store(Request $req)
    {
        $me = auth()->user();
        $phase = $req->query('type');

        $data = $req->validate([
            'schedule_plan_id' => 'required|exists:schedule_plans,id',
            'part_a.shift' => 'required|in:1,2,3',
            'part_a.target' => 'required|string',
            'part_a.division' => 'required|string',
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

        // ambil duration dari draft
        $draft = ChecksheetDraft::where('user_id', $me->id)
            ->where('schedule_plan_id', $data['schedule_plan_id'])
            ->where('phase', $phase)->where('is_active', true)->first();
        $duration = $draft && $draft->started_at ? $draft->started_at->diffInSeconds(now()) : 0;
        // return response()->json(['success' => true, 'message' => $draft]);
        // --- Parse target pick
        [$detailId, $uidStr, $division] = explode('::', $req->input('part_a.target'));
        $uid = $uidStr;
        $scheduledLabel = $this->userLabel($uid);

        // --- Ambil division dari schedule_details
        $detail = ScheduleDetail::find($detailId);
        $divisionFromDetail = $detail?->division ?? $division;

        // --- Attendance
        $isPresent = (int) $data['part_a']['attendance'] === 1;

        // === Case 1: HADIR → simpan satu checksheet (evaluated = scheduled)
        if ($isPresent) {
            // dd($scheduledLabel);
            $cs = Checksheet::create([
                'schedule_plan_id' => $data['schedule_plan_id'],
                'phase' => $phase,
                'stopwatch_duration' => $duration,
                'score' => 0,
                'scheduled_target' => $scheduledLabel,       // simpan jadwal
                'shift' => $data['part_a']['shift'],
                'target' => $scheduledLabel,       // yang dinilai = yang dijadwalkan
                'division' => $divisionFromDetail,
                'attendance' => '1',
                'condition' => $data['part_a']['kondisi'], // kondisi orangnya
                'replacement' => false,
                'replacement_of_id' => null,

                // raw pengganti (kosong)
                'replacement_name' => null,
                'replacement_division' => null,
                'replacement_condition' => null,
            ]);

            // simpan jawaban B
            $answers = $data['answers'] ?? [];
            $probs = $data['problems'] ?? [];
            $cms = $data['countermeasures'] ?? [];

            foreach ($answers as $qid => $val) {
                ChecksheetAnswer::create([
                    'checksheet_id' => $cs->id,
                    'question_text' => Question::find($qid)?->question_text,
                    'choices' => Question::find($qid)?->choices,
                    'answer_value' => (string) $val,
                    'problem' => $probs[$qid] ?? null,
                    'countermeasure' => $cms[$qid] ?? null,
                ]);

                // Hitung skor berdasarkan jumlah choices
                $question = Question::find($qid);
                if ($question) {
                    $choicesCount = is_array($question->choices) ? count($question->choices) : 0;
                    $answerValue = (int) $val;

                    if ($choicesCount == 2) {
                        // 2 pilihan: 0 -> 0, 1 -> 2
                        $score = $answerValue == 0 ? 0 : 2;
                    } elseif ($choicesCount == 3) {
                        // 3 pilihan: 0 -> 0, 1 -> 1, 2 -> 2
                        $score = $answerValue;
                    } else {
                        $score = 0;
                    }

                    $cs->score += $score;
                }
            }
            $cs->update();

            if ($draft) {
                $draft->update(['is_active' => false]);
            }

            return redirect()->route('dashboard')->with('ok', 'Checksheet tersimpan.');
        }

        // === Case 2: ABSEN tanpa pengganti → buat satu checksheet (evaluated = scheduled)
        $hasReplacement = isset($data['part_a']['has_replacement']) && $data['part_a']['has_replacement'];
        if (! $isPresent && ! $hasReplacement) {
            Checksheet::create([
                'schedule_plan_id' => $data['schedule_plan_id'],
                'phase' => $phase,
                'stopwatch_duration' => null,
                'score' => 0,
                'scheduled_target' => $scheduledLabel,       // yang dinilai = yang dijadwalkan
                'shift' => $data['part_a']['shift'],
                'target' => $scheduledLabel,       // yang dinilai = yang dijadwalkan
                'division' => $divisionFromDetail,
                'attendance' => '0',
                'condition' => null,                    // kondisi scheduled (bisa biarin null)
                'replacement' => false,
                'replacement_of_id' => null,
            ]);
            if ($draft) {
                $draft->update(['is_active' => false]);
            }

            return response()->json(['success' => true, 'message' => $draft]);
        }

        // === Case 3: ABSEN → buat PARENT (scheduled, absen)
        $parent = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'phase' => $phase,
            'stopwatch_duration' => null,
            'score' => 0,
            'scheduled_target' => $scheduledLabel,
            'shift' => $data['part_a']['shift'],
            'target' => $scheduledLabel,     // tetap snapshot yang dijadwalkan
            'division' => $divisionFromDetail, // dari detail
            'attendance' => '0',
            'condition' => null,                // kondisi scheduled (bisa biarin null)
            'replacement' => false,
            'replacement_of_id' => null,

            // raw pengganti juga disimpan di parent biar jejaknya utuh
            'replacement_name' => $data['part_a']['nama_pengganti'],
            'replacement_division' => $data['part_a']['bagian_pengganti'] ?: $divisionFromDetail,
            'replacement_condition' => $data['part_a']['kondisi_pengganti'],
        ]);

        // --- Bangun label evaluated dari pengganti
        //   NB: kalau kamu minta input "ID pengganti", tinggal gabung "ID - Nama".
        $evaluatedLabel = trim(($req->input('part_a.operator_id_pengganti', '') ?: '')
            . ' - ' . $data['part_a']['nama_pengganti']);

        // === Buat CHILD (replacement, yang akan dipakai untuk jawaban B)
        $child = Checksheet::create([
            'schedule_plan_id' => $data['schedule_plan_id'],
            'phase' => $phase,
            'stopwatch_duration' => $duration,
            'score' => 0,
            'scheduled_target' => $scheduledLabel,     // tetap tahu siapa jadwalnya
            'shift' => $data['part_a']['shift'],
            'target' => $evaluatedLabel,     // yang dinilai = pengganti
            'division' => $divisionFromDetail, // tetap pakai divisi dari detail jadwal
            'attendance' => '1',
            'condition' => $data['part_a']['kondisi_pengganti'],
            'replacement' => true,
            'replacement_of_id' => $parent->id,

            'replacement_name' => null,
            'replacement_division' => null,
            'replacement_condition' => null,
        ]);

        // simpan jawaban B
        $answers = $data['answers'] ?? [];
        $probs = $data['problems'] ?? [];
        $cms = $data['countermeasures'] ?? [];

        foreach ($answers as $qid => $val) {
            ChecksheetAnswer::create([
                'checksheet_id' => $child->id,
                'question_text' => Question::find($qid)?->question_text,
                'choices' => Question::find($qid)?->choices,
                'answer_value' => (string) $val,
                'problem' => $probs[$qid] ?? null,
                'countermeasure' => $cms[$qid] ?? null,
            ]);
            // Hitung skor berdasarkan jumlah choices
            $question = Question::find($qid);
            if ($question) {
                $choicesCount = is_array($question->choices) ? count($question->choices) : 0;
                $answerValue = (int) $val;
                if ($choicesCount == 2) {
                    // 2 pilihan: 0 -> 0, 1 -> 2
                    $score = $answerValue == 0 ? 0 : 2;
                } elseif ($choicesCount == 3) {
                    // 3 pilihan: 0 -> 0, 1 -> 1, 2 -> 2
                    $score = $answerValue;
                } else {
                    $score = 0;
                }
                $child->score += $score;
            }
        }
        $child->update();

        if ($draft) {
            $draft->update(['is_active' => false]);
        }

        return redirect()->route('dashboard')->with('ok', 'Checksheet tersimpan.');
    }
}
