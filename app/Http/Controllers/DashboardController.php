<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Standard;
use Carbon\CarbonPeriod;
use App\Models\MasterList;
use Illuminate\Http\Request;
use App\Models\ChecksheetDraft;
use App\Models\IncompleteInput;

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
