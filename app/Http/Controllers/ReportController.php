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
        $departments = Department::orderBy('name')->get();
        $supervisors = User::where('role', 'supervisor')->orderBy('name')->get();
        $leaders = User::where('role', 'leader')->orderBy('name')->get();
        $operators = User::where('role', 'operator')->orderBy('name')->get();

        return view('reports.form', compact([
            'type',
            'departments',
            'supervisors',
            'leaders',
            'operators',
        ]));
    }

    public function monthly($type, Request $request)
    {
        // 1. Tangkap parameter form
        $monthInput = $request->month ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $monthInput);

        $targetId = null;
        if ($type === 'leader') $targetId = $request->leader;
        elseif ($type === 'supervisor') $targetId = $request->supervisor;

        // 2. Tarik data user dan department
        $member = User::where('employeeID', $targetId)->first();
        $department = Department::find($request->department);

        // 3. Tarik data dari tabel baru kita: consistency_problems
        $problems = ConsistencyProblem::where('user_id', $targetId) // user_id = yang ngecek (Supervisor/Leader)
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->latest()
            ->get();

        return view('reports.monthly', compact('type', 'problems', 'member', 'department', 'date'));
    }

    public function score($type, Request $request)
    {
        // 1. Tangkap parameter bulan dari form, default ke bulan ini kalau kosong
        $monthInput = $request->month ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $monthInput);

        // 2. Tentukan ID target yang lagi dinilai (berdasarkan role form yang di-submit)
        $targetId = null;
        if ($type === 'leader') $targetId = $request->leader;
        elseif ($type === 'supervisor') $targetId = $request->supervisor;
        elseif ($type === 'operator') $targetId = $request->operator;

        // 3. Tarik data asli dari database
        // Pake ->first() dan ->find() biar nggak error kalau datanya kosong
        $member = User::where('employeeID', $targetId)->first();
        $department = Department::find($request->department);

        // 4. Tarik problem performance miliki target di bulan tersebut
        // Asumsi: Ini report performance, jadi ngambil dari tabel 'problems'
        $problems = Problem::where($type === 'operator' ? 'inferior_id' : 'user_id', $targetId)
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->latest()
            ->get();
        // dd($problems);
        // Lempar semua data ke Blade
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

        // 1. Tarik semua checksheet beserta jawaban & target leadernya
        $checksheets = Checksheet::with(['answers', 'targetUser'])
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->whereHas('schedulePlan', function ($q) use ($supervisorId) {
                $q->where('scheduler_id', $supervisorId)
                    ->where('type', 'supervisor_checks_leader');
            })
            ->get();

        // 2. Ambil daftar Leader yang unik
        $leaders = $checksheets->pluck('targetUser')->filter()->unique('employeeID');

        $datasets = [];
        $colors = ['#2171b5', '#6baed6', '#fd8d3c', '#74c476', '#9e9ac8'];

        $colorIndex = 0;
        foreach ($leaders as $leader) {
            $leaderData = [];
            $leaderChecksheets = $checksheets->where('target', $leader->employeeID);

            // 3. Pecah jadi 4 Minggu (W1-W4)
            $weeks = [
                ['start' => 1, 'end' => 7],
                ['start' => 8, 'end' => 14],
                ['start' => 15, 'end' => 21],
                ['start' => 22, 'end' => 31],
            ];

            foreach ($weeks as $week) {
                $weekData = $leaderChecksheets->filter(function ($c) use ($week) {
                    $day = $c->created_at->day;
                    return $day >= $week['start'] && $day <= $week['end'];
                });

                if ($weekData->isEmpty()) {
                    $leaderData[] = 0;
                } else {
                    // 🔥 MAGIC HAPPENS HERE: Hitung Persentase (Max Poin Fix = 2) 🔥
                    $avgPercentage = $weekData->map(function ($c) {
                        // Total poin mentah yang didapet user
                        $totalPoints = $c->score;

                        // Hitung Max Poin: Jumlah soal/jawaban dikali 2
                        $maxPoints = $c->answers->count() * 2;

                        // Rumus: (Poin / Max Poin) * 100
                        return $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
                    })->avg();

                    $leaderData[] = round($avgPercentage, 2);
                }
            }

            // 4. Masukin ke struktur dataset Bar Chart
            $datasets[] = [
                'type' => 'bar',
                'label' => $leader->name,
                'data' => $leaderData,
                'backgroundColor' => $colors[$colorIndex % count($colors)],
            ];
            $colorIndex++;
        }

        // 5. Garis Target 100%
        $datasets[] = [
            'type' => 'line',
            'label' => 'Target',
            'data' => [100, 100, 100, 100],
            'borderColor' => '#33a02c',
            'borderWidth' => 2,
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

        // 🔥 PERBAIKAN FATAL: Ambil checksheet di mana Leader sebagai PENGISI/AUDITOR 🔥
        $checksheets = Checksheet::with('answers')
            ->whereHas('schedulePlan', function ($q) use ($leaderId) {
                $q->where('scheduler_id', $leaderId)
                    ->where('type', 'leader_checks_operator');
            })
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->get();

        $tScoreData = [];
        $liburData = [];
        $targetData = [];
        $labels = [];

        for ($day = 1; $day <= $days_in_month; $day++) {
            $currentDate = $date->copy()->day($day);
            $labels[] = $day;
            $targetData[] = 100;

            if ($currentDate->isWeekend()) {
                $tScoreData[] = 0;
                $liburData[] = 100;
            } else {
                $liburData[] = 0;

                // Cari semua checksheet yang dibuat oleh Leader ini di hari tersebut
                $dayData = $checksheets->filter(fn($c) => $c->created_at->day == $day);

                if ($dayData->isEmpty()) {
                    $tScoreData[] = 0;
                } else {
                    // Hitung akumulasi total poin murni dari kolom 'score' di database
                    $totalPoints = $dayData->sum('score');

                    // Hitung akumulasi max poin (jumlah jawaban di tiap checksheet * 2)
                    $totalMaxPoints = $dayData->sum(function ($c) {
                        return $c->answers->count() * 2;
                    });

                    // Eksekusi rumus klien: (Total Poin / Total Max Poin) * 100
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
                    'borderWidth' => 2,
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
        $operatorId = $request->operator_id; // ID target operator

        $days_in_month = $date->daysInMonth;

        // Tarik data checksheet khusus buat Operator ini
        $checksheets = Checksheet::with('answers')
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->where('target', $operatorId)
            ->get();

        $labels = range(1, $days_in_month);
        $targetData = array_fill(0, $days_in_month, 100); // Garis target selalu 100%

        // Konfigurasi 4 Phase (sesuai legend di gambar lo)
        $phases = [
            'awal_shift' => ['label' => 'Awal Shift', 'color' => '#f47920'],    // Orange
            'saat_bekerja'    => ['label' => 'Saat Kerja', 'color' => '#5b9bd5'],    // Biru Muda
            'setelah_istirahat'  => ['label' => 'Setelah Istirahat', 'color' => '#255e91'], // Biru Tua
            'akhir_shift' => ['label' => 'Akhir Shift', 'color' => '#ffc000']    // Kuning
        ];

        $datasets = [];

        foreach ($phases as $key => $config) {
            $phaseDataArray = [];

            for ($day = 1; $day <= $days_in_month; $day++) {
                // Filter data berdasarkan hari dan phase
                $dayData = $checksheets->filter(fn($c) => $c->created_at->day == $day && $c->phase == $key);

                if ($dayData->isEmpty()) {
                    $phaseDataArray[] = 0;
                } else {
                    // Hitung persentase murni (Max Poin = Jumlah Soal * 2)
                    $avgPercentage = $dayData->map(function ($c) {
                        $totalPoints = $c->score;
                        $maxPoints = $c->answers->count() * 2;
                        return $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
                    })->avg();

                    // 🔥 MAGIC: Karena ada 4 phase, persentasenya kita bagi 4 biar pas ditumpuk mentok di 100%
                    $phaseDataArray[] = round($avgPercentage / 4, 2);
                }
            }

            // Masukin ke struktur dataset
            $datasets[] = [
                'type' => 'bar',
                'label' => $config['label'],
                'data' => $phaseDataArray,
                'backgroundColor' => $config['color'],
            ];
        }

        // Tambahin dataset buat Garis Target 100%
        $datasets[] = [
            'type' => 'line',
            'label' => 'Target',
            'data' => $targetData,
            'borderColor' => '#33a02c', // Hijau
            'borderWidth' => 2,
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

        // 1. Tarik semua jadwal (Schedule) Supervisor ini buat ngecek Leader
        $plans = SchedulePlan::where('scheduler_id', $supervisorId)
            ->where('type', 'supervisor_checks_leader')
            ->pluck('id');

        $schedules = ScheduleDetail::whereIn('schedule_plan_id', $plans)
            ->whereMonth('scheduled_date', $date->month)
            ->whereYear('scheduled_date', $date->year)
            ->get();

        // 2. Tarik semua problem konsistensi yang diturunkan oleh Supervisor ini
        $problems = ConsistencyProblem::where('user_id', $supervisorId)
            ->where('role_type', 'supervisor')
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->get();

        // 3. Dapatkan daftar Leader yang dinilai
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
                // Total jadwal di minggu ini
                $weekSchedules = $leaderSchedules->filter(function ($s) use ($week) {
                    $day = Carbon::parse($s->scheduled_date)->day;
                    return $day >= $week['start'] && $day <= $week['end'];
                })->count();

                if ($weekSchedules == 0) {
                    $leaderData[] = 0;
                } else {
                    // Total problem (Miss/Late/Advanced) di minggu ini
                    $weekProblems = $leaderProblems->filter(function ($p) use ($week) {
                        $day = $p->created_at->day;
                        return $day >= $week['start'] && $day <= $week['end'];
                    })->count();

                    // Hitung Skor Konsistensi = (Jadwal - Problem) / Jadwal * 100
                    $score = (($weekSchedules - $weekProblems) / $weekSchedules) * 100;
                    $leaderData[] = round(max(0, $score), 2); // Pastikan ga minus
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

        // Garis Target
        $datasets[] = [
            'type' => 'line',
            'label' => 'Target',
            'data' => [100, 100, 100, 100],
            'borderColor' => '#33a02c',
            'borderWidth' => 2,
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

        // 🔥 FIX: Hitung murni jumlah bawahan si Leader ini dari tabel User 🔥
        $totalOperators = User::where('superior_id', $leaderId)
            ->where('role', 'operator')
            ->count();

        // Tarik realisasi checksheet yang diisi Leader ini
        $checksheets = Checksheet::whereHas('schedulePlan', function ($q) use ($leaderId) {
            $q->where('scheduler_id', $leaderId);
        })
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->get();

        $labels = range(1, $days_in_month);
        $targetData = array_fill(0, $days_in_month, 100);

        // Konfigurasi warna phase
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

            // Deteksi Weekend (Sabtu & Minggu)
            if ($currentDate->isWeekend()) {
                $liburData[] = 100;
                foreach ($phases as $key => $config) {
                    $phaseDataArrays[$key][] = 0;
                }
            } else {
                $liburData[] = 0;

                // Kalau ternyata Leader ini belum punya bawahan sama sekali di DB
                if ($totalOperators == 0) {
                    foreach ($phases as $key => $config) {
                        $phaseDataArrays[$key][] = 0;
                    }
                } else {
                    foreach ($phases as $key => $config) {
                        // Total checksheet unik per operator yang diisi di hari & phase ini
                        $filledChecksheets = $checksheets->filter(function ($c) use ($day, $key) {
                            return $c->created_at->day == $day && $c->phase == $key;
                        })->pluck('target')->unique()->count();

                        // 🔥 RUMUS KLIEN: (Isi / Total Bawahan) * 25% 🔥
                        // (dikali 25% karena 1 phase menyumbang 1/4 dari total nilai harian)
                        $score = ($filledChecksheets / $totalOperators) * 25;

                        // Masukin ke array (maksimal 25% biar ga over 100% kalo ada bug form dobel)
                        $phaseDataArrays[$key][] = round(min(25, $score), 2);
                    }
                }
            }
        }

        // Susun dataset untuk 4 phase
        foreach ($phases as $key => $config) {
            $datasets[] = [
                'type' => 'bar',
                'label' => $config['label'],
                'data' => $phaseDataArrays[$key],
                'backgroundColor' => $config['color'],
            ];
        }

        // Tambah dataset Libur (Merah)
        $datasets[] = [
            'type' => 'bar',
            'label' => 'Libur',
            'data' => $liburData,
            'backgroundColor' => '#ff0000',
        ];

        // Tambah dataset Target (Garis Ijo)
        $datasets[] = [
            'type' => 'line',
            'label' => 'Target',
            'data' => $targetData,
            'borderColor' => '#33a02c',
            'borderWidth' => 2,
            'fill' => false,
            'pointRadius' => 0,
        ];

        return response()->json([
            'labels' => $labels,
            'datasets' => $datasets
        ]);
    }
}
