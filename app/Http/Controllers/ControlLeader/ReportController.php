<?php

namespace App\Http\Controllers\ControlLeader;

use App\Http\Controllers\Controller;
use App\Models\ControlLeader\Checksheet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view("control.reports.index");
    }

    public function monthly()
    {
        return view('control.reports.monthly');
    }

    public function daily()
    {
        return view("control.reports.daily");
    }

    public function leaderScore()
    {
        return view("control.reports.score");
    }

    public function leaderConsistency()
    {
        return view("control.reports.consistency");
    }

    public function apiDaily(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $packages = ['awal_shift', 'bekerja', 'istirahat', 'akhir_shift'];
        $data = [];

        foreach ($packages as $package) {
            $scores = Checksheet::with("checksheet_answers")
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('phase', $package)->get()
                ->map(function ($c) {
                    if ($c->answers->count() == 0) return 0;
                    return $c->answers->avg(function ($a) {
                        $maxIndex = is_array(json_decode($a->choices, true)) ? count(json_decode($a->choices, true)) - 1 : 1;
                        return ($a->answer_value / max($maxIndex, 1)) * 100;
                    });
                });

            $data[$package] = round($scores->avg(), 2);
        }

        return response()->json([
            'labels' => ['Awal Shift', 'Saat Bekerja', 'Setelah Istirahat', 'Akhir Shift'],
            'scores' => array_values($data)
        ]);
    }

    public function apiMonthly(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $days_in_month = Carbon::create($year, $month)->daysInMonth;
        $packages = ['awal_shift', 'bekerja', 'istirahat', 'akhir_shift'];
        $data = [];

        foreach ($packages as $package) {
            for ($day = 1; $day <= $days_in_month; $day++) {
                $avg = Checksheet::with('checksheet_answers')
                    ->whereDay('created_at', $day)
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->where('phase', $package)
                    ->get()
                    ->map(function ($c) {
                        $c->answers->avg('answer_value');
                    })
                    ->avg();
                $data[$package][] = $avg ? round($avg * 10, 2) : 0;
            }
        }

        return response()->json([
            'labels' => range(1, $days_in_month),
            'data' => $data
        ]);
    }

    public function apiLeaderConsistency(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $days_in_month = Carbon::create($year, $month)->daysInMonth;
        $packages = ['awal_shift', 'bekerja', 'istirahat', 'akhir_shift'];
        $data = [];

        foreach ($packages as $package) {
            for ($day = 0; $day <= $days_in_month; $day++) {
                $avg = Checksheet::with('checksheet_answers')
                    ->whereDay('created_at', $day)
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->where('phase', $package)
                    ->get()
                    ->map(fn($c) => $c->answers->avg('answer_value'))
                    ->avg();
                $result[$package][] = $avg ? round($avg * 10, 2) : 0;
            }
        }

        return response()->json([
            'labels' => range(1, $days_in_month),
            'data' => $data
        ]);
    }
}
