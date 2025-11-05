<?php

namespace App\Http\Controllers;

use App\Models\ControlLeader\ChecksheetDraft;
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
            $guardName = 'web_control_leader';
            // Siapkan data yang dibutuhkan untuk view control leader
            $user = auth()->guard($guardName)->user(); // Contoh mengambil data user
            $leaderName = $user->name;
            $leaderRole = $user->role; // Ganti dengan data bagian/role yang sebenarnya

            // Kalau ada draft checksheet yang belum selesai, redirect ke sana
            $draft = ChecksheetDraft::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
            if ($draft) {
                return redirect()->route('control.checksheets.create', ['type' => $draft->phase])
                    ->with('info', 'Anda belum menyelesaikan pengisian checksheet sebelumnya.');
            }

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

            $equipments = MasterList::with([
                'results' => function ($q) {
                    $q->latest('calibration_date')->limit(1);
                },
            ])->get();

            $warnings = []; // utk soon & NG
            $dangers = []; // utk overdue

            foreach ($equipments as $eq) {
                $latest = $eq->results->first();
                // dump($latest);
                // Skip if equipment is disposed
                if ($latest && $latest->judgement === 'Disposal') {
                    continue;
                }

                // Determine last calibration date and judgement
                $lastCalDate = $latest
                    ? Carbon::parse($latest->calibration_date)
                    : Carbon::parse($eq->first_used);

                $judgement = $latest?->judgement ?? 'OK';

                // Calculate due date and warning threshold
                $dueDate = $lastCalDate->copy()->addMonths($eq->calibration_freq);
                $warningDate = $dueDate->copy()->subMonth();
                $now = Carbon::now();

                // Build notification message
                $baseMessage = sprintf(
                    "The equipment %s - %s needs to be recalibrated. Last calibrate: %s.",
                    $eq->id_num,
                    $eq->sn_num,
                    $lastCalDate->format('d-m-Y')
                );

                // Categorize alerts
                if ($judgement === 'NG' || $now->between($warningDate, $dueDate)) {
                    $warnings[] = $baseMessage;
                } elseif ($now->gt($dueDate)) {
                    $dangers[] = sprintf(
                        "%s (should be calibrated before: %s)",
                        $baseMessage,
                        $dueDate->format('d-m-Y')
                    );
                }
            }

            // hitung warning dan danger
            $count = [];
            $count['warning'] = count($warnings);
            $count['danger'] = count($dangers);

            $pending = IncompleteInput::forCurrentUser()->atStage('standard')->first();
            $masterList = $pending?->masterList;

            return view('dashboards.kalibrasi', compact('warnings', 'dangers', 'count', 'masterList', 'pending'));
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
