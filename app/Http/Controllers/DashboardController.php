<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        // Siapkan data yang dibutuhkan untuk view control leader
        $user = auth()->user(); // Contoh mengambil data user
        $leaderName = $user->name;
        $leaderRole = $user->role; // Ganti dengan data bagian/role yang sebenarnya

        return view('dashboard', [
            'leaderName' => $leaderName,
            'leaderRole' => $leaderRole,
        ]);
    }
}
