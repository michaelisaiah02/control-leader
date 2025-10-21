<?php

namespace App\Http\Controllers\ControlLeader;

use Illuminate\Http\Request;
use App\Models\ControlLeader\User;
use App\Http\Controllers\Controller;
use App\Models\ControlLeader\Division;
use App\Models\ControlLeader\SchedulePlan;
use App\Models\ControlLeader\ScheduleDetail;

class SchedulePlanController extends Controller
{
    public function index()
    {
        $plans = SchedulePlan::withCount('details')
            ->where('scheduler_id', auth()->guard('web_control_leader')->id())
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('control.schedule.index', compact('plans'));
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
        $authUser = auth()->guard('web_control_leader')->user();
        $targetRole = $authUser->role === 'leader' ? 'operator' : 'leader';

        $availableUsers = \App\Models\User::where('role', $targetRole)->orderBy('name')->get();

        $targets = $plan->details
            ->groupBy('target_user_id')
            ->map(function ($details) {
                $user = $details->first()->targetUser;
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'division' => $details->first()->division,
                    'dates' => $details->mapWithKeys(fn($d) => [$d->scheduled_date => $d->shift])->toArray(),
                ];
            });

        $divisionOptions = Division::orderBy('division_name')->get();

        return view('control.schedule.edit', compact(
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

    public function updateCell(Request $request, $id)
    {
        $plan = SchedulePlan::findOrFail($id);
        $userId = $request->input('user_id');
        $date = $request->input('date');
        $shift = $request->input('shift');
        $division = $request->input('division');

        // cari record lama kalau ada berarti update record tersebut
        $existSchedule = ScheduleDetail::where('schedule_plan_id', $plan->id)
            ->where('target_user_id', $userId)
            ->where('scheduled_date', $date)->first();

        // Hapus kalau shift dikosongkan
        if (empty($shift)) {
            if ($existSchedule) {
                $existSchedule->delete();
            }
        } else {
            if ($existSchedule) {
                $existSchedule->update([
                    'division' => $division,
                    'shift' => (int) $shift,
                ]);
            } else {
                ScheduleDetail::create([
                    'schedule_plan_id' => $plan->id,
                    'target_user_id' => $userId,
                    'division' => $division,
                    'shift' => (int) $shift,
                    'scheduled_date' => $date,
                ]);
            }
        }

        return response()->json(['success' => true, 'schedule_plan_id' => $plan->id, 'user_id' => $userId, 'date' => $date, 'shift' => $shift, 'division' => $division]);
    }

    public function addUser(Request $request, $id)
    {
        $plan = SchedulePlan::findOrFail($id);

        $exists = ScheduleDetail::where('schedule_plan_id', $plan->id)
            ->where('target_user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'User sudah ada di jadwal']);
        }

        ScheduleDetail::create([
            'schedule_plan_id' => $plan->id,
            'target_user_id' => $request->user_id,
            'division' => $request->division,
            'shift' => null,
            'scheduled_date' => sprintf('%04d-%02d-01', $plan->year, $plan->month), // placeholder
        ]);

        return response()->json(['success' => true]);
    }

    public function removeUser($id, $userId)
    {
        $plan = SchedulePlan::findOrFail($id);
        ScheduleDetail::where('schedule_plan_id', $plan->id)
            ->where('target_user_id', $userId)
            ->delete();

        return response()->json(['success' => true]);
    }
}
