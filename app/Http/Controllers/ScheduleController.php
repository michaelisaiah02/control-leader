<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\ScheduleDetail;
use App\Models\SchedulePlan;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // 2. Get/Create Plan (Khusus Supervisor)
        $plan = SchedulePlan::firstOrCreate(
            [
                'scheduler_id' => auth()->user()->employeeID,
                'month' => $month,
                'year' => $year,
                'type' => 'supervisor_checks_leader',
            ]
        );

        // 3. Ambil Semua Leader Aktif (Untuk Dropdown Modal & Mapping Total)
        $leaders = User::where('role', 'leader')
            ->where('superior_id', auth()->user()->employeeID)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // 4. Ambil Jadwal yang Udah Ada
        $details = ScheduleDetail::where('schedule_plan_id', $plan->id)->get();

        // --- [ START KANBAN LOGIC ] ---

        // Setup Skeleton 5 Minggu
        $weeksData = [
            1 => ['label' => '01 - 07', 'leaders' => []],
            2 => ['label' => '08 - 14', 'leaders' => []],
            3 => ['label' => '15 - 21', 'leaders' => []],
            4 => ['label' => '22 - 28', 'leaders' => []],
        ];

        // Kalau bulan itu ada tgl 29, 30, atau 31, baru kita bikin Week 5
        if ($daysInMonth > 28) {
            $weeksData[5] = ['label' => '29 - ' . str_pad($daysInMonth, 2, '0', STR_PAD_LEFT), 'leaders' => []];
        }

        // Setup Penampung Total (Bawah)
        $leaderTotals = [];
        foreach ($leaders as $leader) {
            $leaderTotals[$leader->employeeID] = [
                'name' => $leader->name,
                'count' => 0 // Awalnya nol semua
            ];
        }

        // Looping data DB, masukin ke keranjang minggunya masing-masing
        foreach ($details as $detail) {
            $day = $detail->scheduled_date->day;

            // Rumus Sakti: Tgl dibagi 7 lalu dibulatkan ke atas = Minggu ke berapa
            $weekNum = ceil($day / 7);
            if ($weekNum > 5) $weekNum = 5; // Jaga-jaga buat tgl 29-31 biar tetep masuk Week 5

            $leaderId = $detail->target_user_id;

            // Masukin leader ke Array Minggu tsb (Pake ID sebagai key biar ga dobel biarpun ada 7 row di DB)
            if (!isset($weeksData[$weekNum]['leaders'][$leaderId])) {
                // Cari nama dari collection yg udah ada biar ga N+1 query
                $leaderData = $leaders->where('employeeID', $leaderId)->first();

                if ($leaderData) {
                    $weeksData[$weekNum]['leaders'][$leaderId] = [
                        'id' => $leaderData->employeeID,
                        'name' => $leaderData->name
                    ];

                    // Tambahin point di Totals
                    $leaderTotals[$leaderId]['count']++;
                }
            }
        }
        // --- [ END KANBAN LOGIC ] ---

        return view('schedule.schedule-supervisor', compact(
            'plan',
            'leaders',      // Buat pilihan di dropdown modal
            'weeksData',    // Data utama 5 minggu
            'leaderTotals', // Data buat footer
            'daysInMonth',
            'isPastMonth',
            'isCurrentMonth'
        ));
    }


    // =========================================================================
    // API ENDPOINTS UNTUK SUPERVISOR (AJAX)
    // =========================================================================

    public function addWeeklyLeader(Request $request, SchedulePlan $plan)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,employeeID',
            'week'    => 'required|integer|min:1|max:5',
        ]);

        $week = $validated['week'];

        // Kalkulasi tgl awal dan akhir di minggu tersebut
        $startDay = ($week - 1) * 7 + 1;
        $endDay   = $week * 7;

        // Pastikan endDay nggak kelewatan dari total hari di bulan tersebut
        $daysInMonth = Carbon::createFromDate($plan->year, $plan->month, 1)->daysInMonth;
        if ($endDay > $daysInMonth) {
            $endDay = $daysInMonth;
        }

        // --- [ LOGIC GEMBOK MINGGUAN ] ---
        $today = Carbon::now();
        $isCurrentMonth = ($plan->month == $today->month && $plan->year == $today->year);
        $isPastMonth = Carbon::createFromDate($plan->year, $plan->month, 1)->endOfMonth()->isPast();

        // Hitung kita lagi di minggu ke berapa (1-5)
        $currentWeekLimit = ceil($today->day / 7);

        // Kalau bulannya udah lewat, ATAU bulan ini tapi minggunya <= minggu sekarang -> TOLAK!
        if ($isPastMonth || ($isCurrentMonth && $validated['week'] <= $currentWeekLimit)) {
            return response()->json(['success' => false, 'message' => 'Jadwal untuk minggu ini sudah terkunci!'], 403);
        }
        // --- [ END GEMBOK ] ---

        try {
            DB::beginTransaction();

            // Kumpulin array tanggal buat minggu ini
            $datesToSave = [];
            for ($d = $startDay; $d <= $endDay; $d++) {
                $datesToSave[] = Carbon::createFromDate($plan->year, $plan->month, $d)->format('Y-m-d');
            }

            // 1. DELETE DULU: Bersihin biar ga ada jadwal nyangkut/dobel
            ScheduleDetail::where('schedule_plan_id', $plan->id)
                ->where('target_user_id', $validated['user_id'])
                ->whereIn('scheduled_date', $datesToSave)
                ->delete();

            // 2. INSERT BULK: Masukin 7 hari full untuk minggu tersebut
            $inserts = [];
            foreach ($datesToSave as $date) {
                $inserts[] = [
                    'schedule_plan_id' => $plan->id,
                    'target_user_id'   => $validated['user_id'],
                    'scheduled_date'   => $date,
                    'shift'            => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            ScheduleDetail::insert($inserts);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function removeWeeklyLeader(Request $request, SchedulePlan $plan)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,employeeID',
            'week'    => 'required|integer|min:1|max:5',
        ]);

        $week = $validated['week'];
        $startDay = ($week - 1) * 7 + 1;
        $endDay   = $week * 7;

        $daysInMonth = Carbon::createFromDate($plan->year, $plan->month, 1)->daysInMonth;
        if ($endDay > $daysInMonth) $endDay = $daysInMonth;

        $datesToClear = [];
        for ($d = $startDay; $d <= $endDay; $d++) {
            $datesToClear[] = Carbon::createFromDate($plan->year, $plan->month, $d)->format('Y-m-d');
        }

        // --- [ LOGIC GEMBOK MINGGUAN ] ---
        $today = Carbon::now();
        $isCurrentMonth = ($plan->month == $today->month && $plan->year == $today->year);
        $isPastMonth = Carbon::createFromDate($plan->year, $plan->month, 1)->endOfMonth()->isPast();

        // Hitung kita lagi di minggu ke berapa (1-5)
        $currentWeekLimit = ceil($today->day / 7);

        // Kalau bulannya udah lewat, ATAU bulan ini tapi minggunya <= minggu sekarang -> TOLAK!
        if ($isPastMonth || ($isCurrentMonth && $validated['week'] <= $currentWeekLimit)) {
            return response()->json(['success' => false, 'message' => 'Jadwal untuk minggu ini sudah terkunci!'], 403);
        }
        // --- [ END GEMBOK ] ---

        try {
            // Langsung tebas jadwalnya!
            ScheduleDetail::where('schedule_plan_id', $plan->id)
                ->where('target_user_id', $validated['user_id'])
                ->whereIn('scheduled_date', $datesToClear)
                ->delete();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
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
            ->where('superior_id', auth()->user()->employeeID)
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
                    'dates' => $dates,
                ];
            }
        } else {
            $plan = (object) ['year' => $year, 'month' => $month, 'id' => 0];
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
                        'shift' => $validated['shift']
                    ]
                );
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e], 500);
        }
    }

    public function updateRange(Request $request, SchedulePlan $plan)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,employeeID',
            'dates' => 'required|array',
            'dates.*' => 'date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();

            // 1. (Opsional) Hapus semua jadwal lama si leader di bulan ini biar bersih
            ScheduleDetail::where('schedule_plan_id', $plan->id)
                ->where('target_user_id', $validated['user_id'])
                ->delete();

            // 2. Insert range tanggal yang baru kepilih
            $inserts = [];
            foreach ($validated['dates'] as $date) {
                $inserts[] = [
                    'schedule_plan_id' => $plan->id,
                    'target_user_id' => $validated['user_id'],
                    'scheduled_date' => $date,
                    'shift' => null, // Supervisor nggak pakai shift
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($inserts)) {
                ScheduleDetail::insert($inserts); // Bulk insert biar makin wuzz 🚀
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e], 500);
        }
    }
}
