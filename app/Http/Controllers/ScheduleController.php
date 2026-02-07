<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\SchedulePlan;
use Illuminate\Http\Request;
use App\Models\ScheduleDetail;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user();

        // Kalo user pilih month → gunakan itu
        if ($request->has('month')) {

            $year = intval(substr($request->month, 0, 4));
            $month = intval(substr($request->month, 5, 2));

            // Cari plan sesuai bulan
            $plan = SchedulePlan::where('scheduler_id', $authUser->employeeID)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            // Kalau belum ada → buat baru
            if (! $plan) {
                $plan = SchedulePlan::create([
                    'scheduler_id' => $authUser->employeeID,
                    'year' => $year,
                    'month' => $month,
                ]);
            }

            return redirect()->route('schedule.edit', $plan->id);
        }

        // Default: bulan sekarang
        $year = now()->year;
        $month = now()->month;

        $plan = SchedulePlan::where('scheduler_id', $authUser->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (! $plan) {
            $plan = SchedulePlan::create([
                'scheduler_id' => $authUser->employeeID,
                'year' => $year,
                'month' => $month,
            ]);
        }

        return redirect()->route('schedule.edit', $plan->id);
    }

    public function edit($id)
    {
        $plan = SchedulePlan::with(['details'])->findOrFail($id);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $plan->month, $plan->year);

        $today = now()->startOfDay();
        $monthStart = \Carbon\Carbon::create($plan->year, $plan->month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $isPastMonth = $today->greaterThan($monthEnd);
        $isCurrentMonth = $today->between($monthStart, $monthEnd);

        // Cek role user login
        $authUser = auth()->user();
        $targetRole = $authUser->role === 'leader' ? 'operator' : 'leader';

        $availableUsers = User::where('role', $targetRole)
            ->orderBy('name')
            ->get();

        // group schedule by user
        $detailGroups = $plan->details
            ->groupBy('target_user_id');

        // convert ke targets final
        $targets = $availableUsers->map(function ($user) use ($detailGroups) {

            $details = $detailGroups[$user->employeeID] ?? collect([]);

            return [
                'id' => $user->employeeID,
                'name' => $user->name,
                'division' => optional($details->first())->division ?? null,
                'dates' => $details->mapWithKeys(function ($d) {
                    return [$d->scheduled_date => $d->shift];
                })->toArray(),
            ];
        });

        $divisionOptions = Division::orderBy('division_name')->get();

        return view('schedule.schedule_supervisor', compact(
            'plan',
            'targets',
            'daysInMonth',
            'availableUsers',
            'isPastMonth',
            'isCurrentMonth',
            'today',
            'divisionOptions'
        ));
    }

    public function updateCellLeader(Request $request, $id)
    {
        $plan = SchedulePlan::findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'user_id' => 'required|exists:users,employeeID',
            'date' => 'required|date_format:Y-m-d',
            'shift' => 'nullable|in:1,2,3,L',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $employeeID = $data['user_id'];   // ini employeeID
        $date = $data['date'];
        $shift = $data['shift'];

        $existSchedule = ScheduleDetail::where('schedule_plan_id', $plan->id)
            ->where('target_user_id', $employeeID)   // pakai employeeID
            ->where('scheduled_date', $date)
            ->first();

        if ($shift === null || $shift === '') {
            if ($existSchedule) {
                $existSchedule->delete();
            }
        } else {
            if ($existSchedule) {
                $existSchedule->update([
                    'shift' => $shift,
                ]);
            } else {
                ScheduleDetail::create([
                    'schedule_plan_id' => $plan->id,
                    'target_user_id' => $employeeID, // FK ke employeeID
                    'scheduled_date' => $date,
                    'shift' => $shift,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function updateCellOperator(Request $request, $id)
    {
        $plan = SchedulePlan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,employeeID',
            'date' => 'required|date_format:Y-m-d',
            'shift' => 'nullable|in:1,2,3,L',
            'division' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $employeeID = $data['user_id'];   // ini employeeID
        $date = $data['date'];
        $shift = $data['shift'];
        $division = $data['division'] ?? null;

        $existSchedule = ScheduleDetail::where('schedule_plan_id', $plan->id)
            ->where('target_user_id', $employeeID)   // pakai employeeID
            ->where('scheduled_date', $date)
            ->first();

        if ($shift === null || $shift === '') {
            if ($existSchedule) {
                $existSchedule->delete();
            }
        } else {
            if ($existSchedule) {
                $existSchedule->update([
                    'shift' => $shift,
                    'division' => $division,
                ]);
            } else {
                ScheduleDetail::create([
                    'schedule_plan_id' => $plan->id,
                    'target_user_id' => $employeeID, // FK ke employeeID
                    'scheduled_date' => $date,
                    'shift' => $shift,
                    'division' => $division,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function updateDivision(Request $request, $id)
    {
        $plan = SchedulePlan::findOrFail($id);

        $data = $request->validate([
            'user_id' => 'required|exists:users,employeeID',
            'division' => 'required|string|max:100',
        ]);

        $employeeID = $data['user_id'];

        ScheduleDetail::where('schedule_plan_id', $plan->id)
            ->where('target_user_id', $employeeID)
            ->update(['division' => $data['division']]);

        return response()->json(['success' => true]);
    }

    public function addUser(Request $request, $id)
    {
        $plan = SchedulePlan::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,employeeID',
            'division' => 'nullable|string',
        ]);

        // Tidak menambah schedule_detail dulu
        // Cukup return nilai user_id agar row bisa aktif
        return response()->json([
            'success' => true,
            'user_id' => $validated['user_id'],
        ]);
    }
}
