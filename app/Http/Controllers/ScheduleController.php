<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\ScheduleDetail;
use App\Models\SchedulePlan;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        // 1. Setup Date
        $monthInput = $request->input('month', now()->format('Y-m'));
        try {
            $date = Carbon::createFromFormat('Y-m', $monthInput);
        } catch (Exception $e) {
            $date = now();
        }

        $year = $date->year;
        $month = $date->month;
        $daysInMonth = $date->daysInMonth;

        $today = Carbon::now()->startOfDay();
        $isCurrentMonth = ($date->month == $today->month && $date->year == $today->year);
        $isPastMonth = $date->endOfMonth()->isPast();

        // 2. Get/Create Plan
        $plan = SchedulePlan::firstOrCreate(
            [
                'scheduler_id' => auth()->user()->employeeID,
                'month' => $month,
                'year' => $year,
                'type' => 'supervisor_checks_leader',
            ]
        );

        // 3. AMBIL SEMUA LEADER (Logic Baru)
        // Kita ambil user role 'leader' yang aktif & punya atasan si Supervisor yg login (opsional, tergantung rules)
        // Disini gue ambil semua leader aja sesuai request.
        $leaders = User::where('role', 'leader')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // 4. Ambil Jadwal yang udah ada
        $details = ScheduleDetail::where('schedule_plan_id', $plan->id)->get();

        // 5. Mapping Data (Fix Bug Tanggal '-' disini)
        $targets = [];
        foreach ($leaders as $leader) {

            // Ambil jadwal khusus leader ini dari collection diatas
            $userSchedule = $details->where('target_user_id', $leader->employeeID);

            // --- [START LOGIC BARU] ---
            // Kita bikin array dates manual loop dari tgl 1 s/d akhir bulan
            $dates = [];

            // Tips: Kita format dulu data DB biar gampang dicek (key = tanggal, value = shift)
            $dbMap = $userSchedule->mapWithKeys(function ($item) {
                return [$item->scheduled_date->format('Y-m-d') => $item->shift];
            });

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateObj = Carbon::createFromDate($year, $month, $d);
                $dateStr = $dateObj->format('Y-m-d');

                // 1. Cek DB dulu (Prioritas Utama)
                if (isset($dbMap[$dateStr])) {
                    $dates[$dateStr] = $dbMap[$dateStr];
                }
                // 2. Kalau DB kosong, Cek Weekend (Auto L)
                elseif ($dateObj->isWeekend()) {
                    $dates[$dateStr] = 'L';
                }
                // 3. Sisanya kosong
                else {
                    $dates[$dateStr] = '';
                }
            }
            // --- [END LOGIC BARU] ---

            $targets[] = [
                'id' => $leader->employeeID,
                'name' => $leader->name,
                'dates' => $dates
            ];
        }

        return view('schedule.schedule-supervisor', compact(
            'plan',
            'daysInMonth',
            'targets', // Variable $targets isinya sudah fix semua leader
            'isPastMonth',
            'today',
            'isCurrentMonth'
        ));
    }

    public function updateCell(Request $request, SchedulePlan $plan)
    {
        // Validasi Simple
        $validated = $request->validate([
            'user_id' => 'required|exists:users,employeeID',
            'date' => 'required|date_format:Y-m-d',
            'shift' => 'nullable|in:1,2,3,L',
        ]);

        try {
            DB::beginTransaction();

            if (empty($validated['shift'])) {
                // Hapus kalau kosong
                ScheduleDetail::where('schedule_plan_id', $plan->id)
                    ->where('target_user_id', $validated['user_id'])
                    ->where('scheduled_date', $validated['date'])
                    ->delete();
            } else {
                // Update or Create
                ScheduleDetail::updateOrCreate(
                    [
                        'schedule_plan_id' => $plan->id,
                        'target_user_id' => $validated['user_id'],
                        'scheduled_date' => $validated['date'],
                    ],
                    [
                        'shift' => $validated['shift'],
                        'division' => null, // FORCE NULL sesuai request
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'DB Error'], 500);
        }
    }

    public function leaderIndex(Request $request)
    {
        // 1. Setup Tanggal
        $monthInput = $request->input('month', now()->format('Y-m'));
        try {
            $date = Carbon::createFromFormat('Y-m', $monthInput);
        } catch (Exception $e) {
            $date = now();
        }

        $year = $date->year;
        $month = $date->month;
        $daysInMonth = $date->daysInMonth;

        $today = Carbon::now()->startOfDay();
        $isCurrentMonth = ($date->month == $today->month && $date->year == $today->year);
        $isPastMonth = $date->endOfMonth()->isPast();

        // 2. Ambil List Leader (Buat Dropdown Filter)
        $availableLeaders = User::where('role', 'leader')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // 3. Tentukan Leader Terpilih
        $selectedLeaderId = $request->input('leader', $availableLeaders->first()->employeeID ?? null);

        $targets = [];
        $plan = null;

        if ($selectedLeaderId) {
            // A. Get/Create Plan
            $plan = SchedulePlan::firstOrCreate(
                [
                    'scheduler_id' => $selectedLeaderId,
                    'month' => $month,
                    'year' => $year,
                    'type' => 'leader_checks_operator',
                ]
            );

            // B. Ambil SUBORDINATES (Logic Baru: Based on superior_id)
            $subordinates = User::where('superior_id', $selectedLeaderId)
                ->where('role', 'operator')
                ->where('is_active', true)
                ->with('division') // Eager load relasi division master
                ->orderBy('name')
                ->get();

            // C. Ambil Jadwal Existing
            $details = ScheduleDetail::where('schedule_plan_id', $plan->id)->get();

            // D. Mapping Data
            foreach ($subordinates as $sub) {
                // Filter jadwal khusus user ini
                $userShifts = $details->where('target_user_id', $sub->employeeID);

                // Logic Divisi (Tetap sama)
                $savedDivision = $userShifts->first()->division ?? null;
                $masterDivision = $sub->division->name ?? '';

                // --- [START LOGIC BARU] ---
                $dates = [];

                // Format data DB ke array biar gampang dicari
                $dbMap = $userShifts->mapWithKeys(function ($item) {
                    return [$item->scheduled_date->format('Y-m-d') => $item->shift];
                });

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dateObj = Carbon::createFromDate($year, $month, $d);
                    $dateStr = $dateObj->format('Y-m-d');

                    // 1. Cek DB
                    if (isset($dbMap[$dateStr])) {
                        $dates[$dateStr] = $dbMap[$dateStr];
                    }
                    // 2. Cek Weekend -> Auto L
                    elseif ($dateObj->isWeekend()) {
                        $dates[$dateStr] = 'L';
                    }
                    // 3. Kosong
                    else {
                        $dates[$dateStr] = '';
                    }
                }
                // --- [END LOGIC BARU] ---

                $targets[] = [
                    'id' => $sub->employeeID,
                    'name' => $sub->name,
                    'division' => $savedDivision ?: $masterDivision,
                    'dates' => $dates
                ];
            }
        } else {
            $plan = (object)['year' => $year, 'month' => $month, 'id' => 0];
        }

        $divisionOptions = Division::all();

        return view('schedule.schedule-leader', compact(
            'plan',
            'daysInMonth',
            'availableLeaders',
            'divisionOptions',
            'targets',
            'isPastMonth',
            'isCurrentMonth',
            'today'
        ));
    }

    public function updateCellOperator(Request $request, SchedulePlan $plan)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,employeeID',
            'date' => 'required|date_format:Y-m-d',
            'shift' => 'nullable|in:1,2,3,L',
            'division' => 'required|string', // Division wajib dikirim dari JS
        ]);

        try {
            DB::beginTransaction();

            if (empty($validated['shift'])) {
                // Delete
                ScheduleDetail::where('schedule_plan_id', $plan->id)
                    ->where('target_user_id', $validated['user_id'])
                    ->where('scheduled_date', $validated['date'])
                    ->delete();
            } else {
                // Update or Create
                ScheduleDetail::updateOrCreate(
                    [
                        'schedule_plan_id' => $plan->id,
                        'target_user_id' => $validated['user_id'],
                        'scheduled_date' => $validated['date'],
                    ],
                    [
                        'shift' => $validated['shift'],
                        'division' => $validated['division'], // Save division
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateDivisionOperator(Request $request, SchedulePlan $plan)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,employeeID',
            'division' => 'required|string',
        ]);

        try {
            // Update SEMUA record user ini di plan ini dengan divisi baru
            ScheduleDetail::where('schedule_plan_id', $plan->id)
                ->where('target_user_id', $validated['user_id'])
                ->update(['division' => $validated['division']]);

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
