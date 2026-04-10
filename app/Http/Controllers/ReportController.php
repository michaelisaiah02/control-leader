<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\ConsistencyProblem;
use App\Models\Department;
use App\Models\Problem;
use App\Models\ScheduleDetail;
use App\Models\SchedulePlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function form($type)
    {
        $user = auth()->user();

        // 1. Base Query (Default: Management/YPQ liat semua)
        $departments = Department::orderBy('name');
        $supervisors = User::where('role', 'supervisor')->orderBy('name');
        $leaders     = User::where('role', 'leader')->orderBy('name');
        $operators   = User::where('role', 'operator')->orderBy('name');

        // 2. 🔥 SATPAM HIERARKI (Role-Based Access Control) 🔥
        if ($user->role === 'supervisor') {

            // SPV cuma bisa liat kandangnya sendiri
            $departments->where('id', $user->department_id);
            $supervisors->where('employeeID', $user->employeeID);
            $leaders->where('superior_id', $user->employeeID);

            // Filter Operator: Cuma yang leadernya ada di bawah SPV ini
            $myLeaderIds = User::where('role', 'leader')
                ->where('superior_id', $user->employeeID)
                ->pluck('employeeID');
            $operators->whereIn('superior_id', $myLeaderIds);
        } elseif ($user->role === 'leader') {

            // Leader lebih sempit lagi aksesnya
            $departments->where('id', $user->department_id);
            $supervisors->where('employeeID', $user->superior_id);
            $leaders->where('employeeID', $user->employeeID);
            $operators->where('superior_id', $user->employeeID);
        }

        // 3. Eksekusi Query
        return view('reports.form', [
            'type'        => $type,
            'departments' => $departments->get(),
            'supervisors' => $supervisors->get(),
            'leaders'     => $leaders->get(),
            'operators'   => $operators->get(),
        ]);
    }

    public function consistency($type, Request $request)
    {
        $monthInput = $request->month ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $monthInput);

        $targetId = null;
        if ($type === 'leader') $targetId = $request->leader;
        elseif ($type === 'supervisor') $targetId = $request->supervisor;

        $member = User::where('employeeID', $targetId)->first();
        $department = Department::find($request->department);

        // Problem Konsistensi biarin pakai created_at karena nyatet kapan masalahnya dilaporin
        $problems = ConsistencyProblem::where('user_id', $targetId)
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->latest()
            ->get();

        return view('reports.consistency', compact('type', 'problems', 'member', 'department', 'date'));
    }

    public function score($type, Request $request)
    {
        $monthInput = $request->month ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $monthInput);

        $targetId = null;
        if ($type === 'leader') $targetId = $request->leader;
        elseif ($type === 'supervisor') $targetId = $request->supervisor;
        elseif ($type === 'operator') $targetId = $request->operator;

        $member = User::where('employeeID', $targetId)->first();
        $department = Department::find($request->department);

        $problems = Problem::where($type === 'operator' ? 'inferior_id' : 'user_id', $targetId)
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->latest()
            ->get();

        return view('reports.score', compact('type', 'problems', 'member', 'department', 'date'));
    }

    // ==========================================
    // AREA API UNTUK CHART / AJAX
    // ==========================================

    public function apiSupervisorScore(Request $request)
    {
        $monthInput = $request->month ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $monthInput);
        $supervisorId = $request->supervisor_id;

        // 🔥 Ganti created_at jadi shift_date
        $checksheets = Checksheet::with(['answers', 'targetUser'])
            ->whereMonth('shift_date', $date->month)
            ->whereYear('shift_date', $date->year)
            ->whereHas('schedulePlan', function ($q) use ($supervisorId) {
                $q->where('scheduler_id', $supervisorId)
                    ->where('type', 'supervisor_checks_leader');
            })
            ->get();

        $leaders = $checksheets->pluck('targetUser')->filter()->unique('employeeID');
        $datasets = [];
        $colors = ['#2171b5', '#6baed6', '#fd8d3c', '#74c476', '#9e9ac8'];

        $colorIndex = 0;
        foreach ($leaders as $leader) {
            $leaderData = [];
            $leaderChecksheets = $checksheets->where('target', $leader->employeeID);

            $weeks = [
                ['start' => 1, 'end' => 7],
                ['start' => 8, 'end' => 14],
                ['start' => 15, 'end' => 21],
                ['start' => 22, 'end' => 31],
            ];

            foreach ($weeks as $week) {
                $weekData = $leaderChecksheets->filter(function ($c) use ($week) {
                    // 🔥 Parse shift_date ke Carbon
                    $day = Carbon::parse($c->shift_date)->day;
                    return $day >= $week['start'] && $day <= $week['end'];
                });

                if ($weekData->isEmpty()) {
                    $leaderData[] = 0;
                } else {
                    $avgPercentage = $weekData->map(function ($c) {
                        $totalPoints = $c->score;
                        $maxPoints = $c->answers->count() * 2;
                        return $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
                    })->avg();

                    $leaderData[] = round($avgPercentage, 2);
                }
            }

            $datasets[] = [
                'type' => 'bar',
                'label' => $leader->name,
                'data' => $leaderData,
                'backgroundColor' => $colors[$colorIndex % count($colors)],
            ];
            $colorIndex++;
        }

        $datasets[] = [
            'type' => 'line',
            'label' => 'Target',
            'data' => [100, 100, 100, 100],
            'borderColor' => '#33a02c',
            'backgroundColor' => '#33a02c',
            'borderWidth' => 8,
            'fill' => false,
            'pointRadius' => 0,
        ];

        return response()->json([
            'labels' => ['W1', 'W2', 'W3', 'W4'],
            'datasets' => $datasets
        ]);
    }

    public function apiLeaderScore(Request $request)
    {
        $monthInput = $request->month ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $monthInput);
        $leaderId = $request->leader_id;

        $days_in_month = $date->daysInMonth;

        // 🔥 Ganti created_at jadi shift_date
        $checksheets = Checksheet::with('answers')
            ->whereHas('schedulePlan', function ($q) use ($leaderId) {
                $q->where('scheduler_id', $leaderId)
                    ->where('type', 'leader_checks_operator');
            })
            ->whereMonth('shift_date', $date->month)
            ->whereYear('shift_date', $date->year)
            ->get();

        $tScoreData = [];
        $liburData = [];
        $targetData = [];
        $labels = [];

        for ($day = 1; $day <= $days_in_month; $day++) {
            $currentDate = $date->copy()->day($day);
            $labels[] = $day;
            $targetData[] = 100;

            // 🔥 Filter berdasarkan shift_date
            $dayData = $checksheets->filter(fn($c) => Carbon::parse($c->shift_date)->day == $day);

            if ($dayData->isEmpty() && $currentDate->isWeekend()) {
                $tScoreData[] = 0;
                $liburData[] = 100;
            } else {
                $liburData[] = 0;

                if ($dayData->isEmpty()) {
                    $tScoreData[] = 0;
                } else {
                    $totalPoints = $dayData->sum('score');
                    $totalMaxPoints = $dayData->sum(function ($c) {
                        return $c->answers->count() * 2;
                    });

                    $scorePercentage = $totalMaxPoints > 0 ? ($totalPoints / $totalMaxPoints) * 100 : 0;
                    $tScoreData[] = round($scorePercentage, 2);
                }
            }
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => 'T. Score',
                    'data' => $tScoreData,
                    'backgroundColor' => '#2171b5',
                ],
                [
                    'type' => 'bar',
                    'label' => 'Libur',
                    'data' => $liburData,
                    'backgroundColor' => '#ff0000',
                ],
                [
                    'type' => 'line',
                    'label' => 'Target',
                    'data' => $targetData,
                    'borderColor' => '#33a02c',
                    'backgroundColor' => '#33a02c',
                    'borderWidth' => 8,
                    'fill' => false,
                    'pointRadius' => 0,
                ]
            ]
        ]);
    }

    public function apiOperatorScore(Request $request)
    {
        $monthInput = $request->month ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $monthInput);
        $operatorId = $request->operator_id;

        $days_in_month = $date->daysInMonth;

        // 🔥 Ganti created_at jadi shift_date
        $checksheets = Checksheet::with('answers')
            ->whereMonth('shift_date', $date->month)
            ->whereYear('shift_date', $date->year)
            ->where('target', $operatorId)
            ->get();

        $labels = range(1, $days_in_month);
        $targetData = array_fill(0, $days_in_month, 100);

        $phases = [
            'awal_shift' => ['label' => 'Awal Shift', 'color' => '#f47920'],
            'saat_bekerja'    => ['label' => 'Saat Kerja', 'color' => '#5b9bd5'],
            'setelah_istirahat'  => ['label' => 'Setelah Istirahat', 'color' => '#255e91'],
            'akhir_shift' => ['label' => 'Akhir Shift', 'color' => '#ffc000']
        ];

        $datasets = [];

        foreach ($phases as $key => $config) {
            $phaseDataArray = [];

            for ($day = 1; $day <= $days_in_month; $day++) {
                // 🔥 Filter berdasarkan shift_date
                $dayData = $checksheets->filter(fn($c) => Carbon::parse($c->shift_date)->day == $day && $c->phase == $key);

                if ($dayData->isEmpty()) {
                    $phaseDataArray[] = 0;
                } else {
                    $avgPercentage = $dayData->map(function ($c) {
                        $totalPoints = $c->score;
                        $maxPoints = $c->answers->count() * 2;
                        return $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
                    })->avg();

                    $phaseDataArray[] = round($avgPercentage / 4, 2);
                }
            }

            $datasets[] = [
                'type' => 'bar',
                'label' => $config['label'],
                'data' => $phaseDataArray,
                'backgroundColor' => $config['color'],
            ];
        }

        $datasets[] = [
            'type' => 'line',
            'label' => 'Target',
            'data' => $targetData,
            'borderColor' => '#33a02c',
            'backgroundColor' => '#33a02c',
            'borderWidth' => 8,
            'fill' => false,
            'pointRadius' => 0,
        ];

        return response()->json([
            'labels' => $labels,
            'datasets' => $datasets
        ]);
    }

    public function apiSupervisorConsistency(Request $request)
    {
        $monthInput = $request->month ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $monthInput);
        $supervisorId = $request->supervisor_id;

        $plans = SchedulePlan::where('scheduler_id', $supervisorId)
            ->where('type', 'supervisor_checks_leader')
            ->pluck('id');

        $schedules = ScheduleDetail::whereIn('schedule_plan_id', $plans)
            ->whereMonth('scheduled_date', $date->month)
            ->whereYear('scheduled_date', $date->year)
            ->get();

        $problems = ConsistencyProblem::where('user_id', $supervisorId)
            ->where('role_type', 'supervisor')
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->get();

        $leaderIds = $schedules->pluck('target_user_id')->unique();
        $leaders = User::whereIn('employeeID', $leaderIds)->get();

        $datasets = [];
        $colors = ['#2171b5', '#6baed6', '#fd8d3c', '#74c476', '#9e9ac8'];
        $colorIndex = 0;

        $weeks = [
            ['start' => 1, 'end' => 7],
            ['start' => 8, 'end' => 14],
            ['start' => 15, 'end' => 21],
            ['start' => 22, 'end' => 31],
        ];

        foreach ($leaders as $leader) {
            $leaderData = [];
            $leaderSchedules = $schedules->where('target_user_id', $leader->employeeID);
            $leaderProblems = $problems->where('inferior_id', $leader->employeeID);

            foreach ($weeks as $week) {
                $weekSchedules = $leaderSchedules->filter(function ($s) use ($week) {
                    $day = Carbon::parse($s->scheduled_date)->day;
                    return $day >= $week['start'] && $day <= $week['end'];
                })->count();

                if ($weekSchedules == 0) {
                    $leaderData[] = 0;
                } else {
                    $weekProblemsCount = $leaderProblems->filter(function ($p) use ($week) {
                        $day = $p->created_at->day;
                        return $day >= $week['start'] && $day <= $week['end'];
                    })->count();

                    $score = (($weekSchedules - $weekProblemsCount) / $weekSchedules) * 100;
                    $leaderData[] = round(max(0, $score), 2);
                }
            }

            $datasets[] = [
                'type' => 'bar',
                'label' => $leader->name,
                'data' => $leaderData,
                'backgroundColor' => $colors[$colorIndex % count($colors)],
            ];
            $colorIndex++;
        }

        $datasets[] = [
            'type' => 'line',
            'label' => 'Target',
            'data' => [100, 100, 100, 100],
            'borderColor' => '#33a02c',
            'backgroundColor' => '#33a02c',
            'borderWidth' => 8,
            'fill' => false,
            'pointRadius' => 0,
        ];

        return response()->json([
            'labels' => ['W1', 'W2', 'W3', 'W4'],
            'datasets' => $datasets
        ]);
    }

    public function apiLeaderConsistency(Request $request)
    {
        $monthInput = $request->month ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $monthInput);
        $leaderId = $request->leader_id;

        $days_in_month = $date->daysInMonth;

        $totalOperators = User::where('superior_id', $leaderId)
            ->where('role', 'operator')
            ->count();

        // 🔥 Ganti created_at jadi shift_date
        $checksheets = Checksheet::whereHas('schedulePlan', function ($q) use ($leaderId) {
            $q->where('scheduler_id', $leaderId);
        })
            ->whereMonth('shift_date', $date->month)
            ->whereYear('shift_date', $date->year)
            ->get();

        $labels = range(1, $days_in_month);
        $targetData = array_fill(0, $days_in_month, 100);

        $phases = [
            'awal_shift' => ['label' => 'Awal Shift', 'color' => '#ed7d31'],
            'saat_bekerja'    => ['label' => 'Saat Bekerja', 'color' => '#a5a5a5'],
            'setelah_istirahat'  => ['label' => 'Setelah Istirahat', 'color' => '#5b9bd5'],
            'akhir_shift' => ['label' => 'Akhir Shift', 'color' => '#ffc000']
        ];

        $datasets = [];
        $phaseDataArrays = [
            'awal_shift' => [],
            'saat_bekerja' => [],
            'setelah_istirahat' => [],
            'akhir_shift' => []
        ];
        $liburData = [];

        for ($day = 1; $day <= $days_in_month; $day++) {
            $currentDate = $date->copy()->day($day);

            // 🔥 Filter berdasarkan shift_date
            $dayChecksheets = $checksheets->filter(fn($c) => Carbon::parse($c->shift_date)->day == $day);

            if ($dayChecksheets->isEmpty() && $currentDate->isWeekend()) {
                $liburData[] = 100;
                foreach ($phases as $key => $config) {
                    $phaseDataArrays[$key][] = 0;
                }
            } else {
                $liburData[] = 0;

                if ($totalOperators == 0) {
                    foreach ($phases as $key => $config) {
                        $phaseDataArrays[$key][] = 0;
                    }
                } else {
                    foreach ($phases as $key => $config) {
                        $filledChecksheets = $dayChecksheets->filter(function ($c) use ($key) {
                            return $c->phase == $key;
                        })->pluck('target')->unique()->count();

                        $score = ($filledChecksheets / $totalOperators) * 25;
                        $phaseDataArrays[$key][] = round(min(25, $score), 2);
                    }
                }
            }
        }

        foreach ($phases as $key => $config) {
            $datasets[] = [
                'type' => 'bar',
                'label' => $config['label'],
                'data' => $phaseDataArrays[$key],
                'backgroundColor' => $config['color'],
            ];
        }

        $datasets[] = [
            'type' => 'bar',
            'label' => 'Libur',
            'data' => $liburData,
            'backgroundColor' => '#ff0000',
        ];

        $datasets[] = [
            'type' => 'line',
            'label' => 'Target',
            'data' => $targetData,
            'borderColor' => '#33a02c',
            'backgroundColor' => '#33a02c',
            'borderWidth' => 8,
            'fill' => false,
            'pointRadius' => 0,
        ];

        return response()->json([
            'labels' => $labels,
            'datasets' => $datasets
        ]);
    }
}
