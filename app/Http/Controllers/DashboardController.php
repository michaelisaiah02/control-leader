<?php

namespace App\Http\Controllers;

use App\Models\IncompleteInput;
use App\Models\MasterList;
use App\Models\Standard;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $activeApp = session('active_app');

        // Jika sesi aplikasi adalah 'control_leader'
        if ($activeApp === 'control_leader') {

            // Siapkan data yang dibutuhkan untuk view control leader
            $user = auth()->user(); // Contoh mengambil data user
            $leaderName = $user->name;
            $leaderRole = "Contoh Bagian"; // Ganti dengan data bagian/role yang sebenarnya

            return view('dashboards.control_leader', [
                'leaderName' => $leaderName,
                'leaderRole' => $leaderRole,
            ]);
        }

        // Jika sesi aplikasi adalah 'kalibrasi' (atau sebagai default)
        else {
            if ($request->has('key')) {
                return redirect()->route('dashboard')->with(['key' => $request->key]);
            }

            $equipments = MasterList::with(['results' => function ($q) {
                $q->latest('calibration_date')->limit(1);
            }])->get();

            $warnings = []; // utk soon & NG
            $dangers = []; // utk overdue

            foreach ($equipments as $eq) {
                $latest = $eq->results->first();

                // jika belum pernah ada result, gunakan first_used & treat as OK
                if (! $latest) {
                    $lastCal = Carbon::parse($eq->first_used);
                    $judg = 'OK';
                } else {
                    $lastCal = Carbon::parse($latest->calibration_date);
                    $judg = $latest->judgement;
                }

                // skip Disposal
                if ($judg === 'Disposal') {
                    continue;
                }

                // hitung kapan due, dan sebulan sebelum
                $freq = $eq->calibration_freq;
                $dueDate = $lastCal->copy()->addMonths($freq);
                $warnFrom = $dueDate->copy()->subMonth();
                $now = Carbon::now();
                // bentuk pesan
                $msg = "The equipment {$eq->id_num} - {$eq->sn_num} device needs to be recalibrated. Last calibrate: {$lastCal->format('d-m-Y')}.";

                if ($judg === 'NG' || $now->between($warnFrom, $dueDate)) {
                    // NG selalu warning, atau OK dalam sebulan ke depan
                    $warnings[] = $msg;
                } elseif ($now->gt($dueDate)) {
                    // sudah lewat due date
                    $dangers[] = $msg . " (should be calibrated before: {$dueDate->format('d-m-Y')})";
                }
            }

            $pending = IncompleteInput::forCurrentUser()->atStage('standard')->first();
            $masterList = $pending?->masterList;

            return view('dashboards.kalibrasi', compact('warnings', 'dangers', 'masterList', 'pending'));
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_num' => ['required', 'string', 'max:255', 'unique:standards,id_num'],
            'param_01' => ['required', 'numeric'],
            'param_02' => ['required', 'numeric'],
            'param_03' => ['required', 'numeric'],
            'param_04' => ['required', 'numeric'],
            'param_05' => ['required', 'numeric'],
            'param_06' => ['required', 'numeric'],
            'param_07' => ['required', 'numeric'],
            'param_08' => ['required', 'numeric'],
            'param_09' => ['required', 'numeric'],
            'param_10' => ['required', 'numeric'],
        ]);

        // Ensure numeric values are properly cast
        foreach (range(1, 9) as $i) {
            $validated["param_0{$i}"] = (float) $validated["param_0{$i}"];
        }
        $validated['param_10'] = (float) $validated['param_10'];

        // Simpan acceptance criteria
        Standard::create($validated);

        // Update jadi tahap calibration
        IncompleteInput::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'master_list_id' => $request->master_list_id,
            ],
            ['stage' => 'calibration']
        );

        return redirect()->route('kalibrasi.input.calibration.data')->with([
            'success' => 'Please input the calibration data.',
        ]);
    }
}
