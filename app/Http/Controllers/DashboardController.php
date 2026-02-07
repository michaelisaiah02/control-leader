<?php

namespace App\Http\Controllers;

use App\Models\ChecksheetDraft;
use App\Models\IncompleteInput;
use App\Models\MasterList;
use App\Models\Standard;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Siapkan data yang dibutuhkan untuk view control leader
        $user = auth()->user(); // Contoh mengambil data user
        $leaderName = $user->name;
        $leaderRole = $user->role; // Ganti dengan data bagian/role yang sebenarnya

        // Kalau ada draft checksheet yang belum selesai, redirect ke sana
        $draft = ChecksheetDraft::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
        if ($draft) {
            return redirect()->route('checksheets.create', ['type' => $draft->phase])
                ->with('info', 'Anda belum menyelesaikan pengisian checksheet sebelumnya.');
        }

        return view('dashboard', [
            'leaderName' => $leaderName,
            'leaderRole' => $leaderRole,
        ]);
    }
}
