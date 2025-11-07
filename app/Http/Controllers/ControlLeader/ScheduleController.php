<?php

namespace App\Http\Controllers\ControlLeader;

use Illuminate\Http\Request;
use App\Models\ControlLeader\User;
use App\Http\Controllers\Controller;
use App\Models\ControlLeader\Division;
use App\Models\ControlLeader\SchedulePlan;
use App\Models\ControlLeader\ScheduleDetail;

class ScheduleController extends Controller
{
    public function index()
    {
        $authUser = User::find(auth()->guard('web_control_leader')->id());

        // Validasi role supervisor
        // if ($authUser->role !== 'supervisor') {
        //     abort(403, 'Unauthorized access. Only supervisors can access this page.');
        // }

        $query = SchedulePlan::withCount('details')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        // Filter berdasarkan scheduler_id jika ada input
        if (request()->has('scheduler_id') && request('scheduler_id')) {
            $query->where('scheduler_id', request('scheduler_id'));
        }

        $plans = $query->get();

        // Data untuk dropdown filter
        $users = User::whereIn('role', ['leader'])->orderBy('name')->get();

        // Tambahkan user yang sedang login sebagai data pertama
        $users = $users->reject(fn($u) => $u->id === $authUser->id)->prepend($authUser);

        return view('control.schedule.index', compact('plans', 'users'));
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

        $availableUsers = User::where('role', $targetRole)->orderBy('name')->get();

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

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'employeeID' => 'required|exists:users,employeeID',
        ]);

        $month = date('m', strtotime($request->input('month')));
        $year = date('Y', strtotime($request->input('month')));

        $exists = SchedulePlan::where('scheduler_id', $request->input('employeeID'))
            ->where('month', $month)
            ->where('year', $year)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Rencana untuk user dan bulan tersebut sudah ada!');
        }

        $plan = SchedulePlan::create([
            'month' => $month,
            'year' => $year,
            'scheduler_id' => $request->input('employeeID'),
        ]);

        return redirect()->route('control.schedule.index', $plan->id)
            ->with('success', 'Rencana jadwal baru berhasil dibuat.');
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
