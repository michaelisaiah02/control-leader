<?php

namespace App\Http\Controllers;

use App\Models\ConsistencyProblem;
use App\Models\Problem;

class DashboardController extends Controller
{
    public function index()
    {
        // Siapkan data yang dibutuhkan untuk view control leader
        $user = auth()->user(); // Contoh mengambil data user
        $userName = $user->name;
        $userRole = $user->role; // Ganti dengan data bagian/role yang sebenarnya

        // Hitung jumlah problem yang di tabel problem dan consistency problem yang bukan statusnya closed untuk ditampilkan di dashboard
        // 1. Hitung Performance Problems
        $leaderPerformanceCount = Problem::whereHas('user', function ($query) {
            $query->where('role', 'leader');
        })->where('status', '!=', 'close')->count();

        $supervisorPerformanceCount = Problem::whereHas('user', function ($query) {
            $query->where('role', 'supervisor');
        })->where('status', '!=', 'close')->count();

        // 2. Hitung Consistency Problems (Karena udah ada tabel dan kolom role_type, query-nya lebih gampang)
        $leaderConsistencyCount = ConsistencyProblem::where('role_type', 'leader')
            ->where('status', '!=', 'close')->count();

        $supervisorConsistencyCount = ConsistencyProblem::where('role_type', 'supervisor')
            ->where('status', '!=', 'close')->count();

        $finalProblemCount = $leaderPerformanceCount + $leaderConsistencyCount + (auth()->user()->role !== 'leader' ? ($supervisorPerformanceCount + $supervisorConsistencyCount) : 0);
        return view('dashboard', [
            'userName' => $userName,
            'userRole' => $userRole,
            'problemCount' => $finalProblemCount,
        ]);
    }
}
