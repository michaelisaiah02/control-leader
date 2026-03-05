<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        // Siapkan data yang dibutuhkan untuk view control leader
        $user = auth()->user(); // Contoh mengambil data user
        $userName = $user->name;
        $userRole = $user->role; // Ganti dengan data bagian/role yang sebenarnya

        return view('dashboard', [
            'userName' => $userName,
            'userRole' => $userRole,
        ]);
    }
}
