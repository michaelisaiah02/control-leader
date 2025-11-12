<?php

namespace App\Http\Controllers\Kalibrasi\Input;

use Carbon\Carbon;
use App\Models\Result;
use App\Models\MasterList;
use Illuminate\Http\Request;
use App\Models\IncompleteInput;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CalibrationDataController extends Controller
{
    public function create()
    {
        $dataType = null;
        if (request()->has('data')) {
            $dataType = request()->input('data');
        }
        $result = match ($dataType ?? null) {
            // Warning: Ambil data yang NG, atau OK yang tanggal kalibrasi selanjutnya dalam sebulan ke depan tapi yang belum lewat (dengan ID Masterlist masing-masing 1 yang terakhir)
            'warning' => Result::where(function ($query) {
                    $query->where('judgement', 'NG')
                    ->orWhere(function ($q) {
                        $q->where('judgement', 'OK')
                        ->whereRaw("DATE_ADD(calibration_date, INTERVAL (SELECT calibration_freq FROM master_lists WHERE master_lists.id_num = results.id_num) MONTH) BETWEEN ? AND ?", [
                            Carbon::now()->toDateString(),
                            Carbon::now()->addMonth()->toDateString(),
                        ]);
                    });
                })
                ->whereIn('id', function ($subQuery) {
                        $subQuery->selectRaw('MAX(id)')
                        ->from('results')
                        ->groupBy('id_num');
                    })->get(),
            // Danger: Ambil data yang sudah lewat due date (kalibrasi terakhir + freq) (dengan ID Masterlist masing-masing 1 yang terakhir)
            'danger' => Result::where(function ($query) {
                    $query->whereRaw("DATE_ADD(calibration_date, INTERVAL (SELECT calibration_freq FROM master_lists WHERE master_lists.id_num = results.id_num) MONTH) < ?", [Carbon::now()->toDateString()]);
                })
                ->whereIn('id', function ($subQuery) {
                        $subQuery->selectRaw('MAX(id)')
                        ->from('results')
                        ->groupBy('id_num');
                    })->get(),
            default => Result::all(),
        };

        // kalau data type danger, ambil juga data masterlist yang baru (belum pernah kalibrasi) yang sudah lewat due date
        if ($dataType === 'danger') {
            $masterlistOverdue = MasterList::whereNotIn('id_num', function ($subQuery) {
                $subQuery->select('id_num')
                    ->from('results');
            })
                ->whereRaw("DATE_ADD(first_used, INTERVAL calibration_freq MONTH) < ?", [Carbon::now()->toDateString()])
                ->get();

            // Gabungkan hasil masterlist overdue ke dalam result
            foreach ($masterlistOverdue as $ml) {
                $dummyResult = new Result();
                $dummyResult->id_num = $ml->id_num;
                $dummyResult->setAttribute('calibration_date', null);
                $dummyResult->judgement = 'Not Calibrated';
                $result->push($dummyResult);
            }
        }

        return view('kalibrasi.input.calibration-data', [
            'title' => 'CALIBRATION DATA INPUT',
            'results' => $result,
            'pending' => IncompleteInput::forCurrentUser()
                ->atStage('calibration')
                ->first(),
            'dataType' => $dataType,
        ]);
    }

    public function store(Request $request, Result $result)
    {
        $validated = $request->validate([
            'id_num' => 'required|exists:master_lists,id_num',
            'calibration_date' => 'nullable|date',
            'calibrator_equipment' => $request->calibration_type === 'Internal'
                ? 'required|exists:master_lists,id_num'
                : 'nullable',
            'param_01' => 'nullable|numeric',
            'param_02' => 'nullable|numeric',
            'param_03' => 'nullable|numeric',
            'param_04' => 'nullable|numeric',
            'param_05' => 'nullable|numeric',
            'param_06' => 'nullable|numeric',
            'param_07' => 'nullable|numeric',
            'param_08' => 'nullable|numeric',
            'param_09' => 'nullable|numeric',
            'param_10' => 'nullable|numeric',
            'judgement' => 'required|string|in:OK,NG,Disposal',
            'certificate' => $request->calibration_type === 'External'
                ? 'required|file|mimes:pdf|max:2048'
                : 'nullable',
        ]);

        $validated['id_num'] = strtoupper($request->id_num);
        $validated['created_by'] = auth()->user()->employeeID;

        foreach (range(1, 9) as $i) {
            $key = "param_0{$i}";
            if (isset($validated[$key])) {
                $validated[$key] = (float) $validated[$key];
            }
        }

        if (isset($validated['param_10'])) {
            $validated['param_10'] = (float) $validated['param_10'];
        }

        if (is_null($validated['calibration_date'])) {
            $validated['calibration_date'] = now()->toDateString();
        }

        if ($request->hasFile('certificate')) {
            if ($result->certificate && Storage::exists($result->certificate)) {
                Storage::delete($result->certificate);
            }
            $idNum = $validated['id_num'];
            $date = Carbon::parse($validated['calibration_date'])->format('dmY');
            $ext = $request->file('certificate')->getClientOriginalExtension();

            $filename = "{$idNum}-{$date}.{$ext}";
            $folder = "certificates/{$idNum}";

            $path = $request->file('certificate')->storeAs($folder, $filename);

            $validated['certificate'] = $path;
        }

        // Simpan hasil kalibrasi
        $result = Result::create($validated);

        // Cek dan hapus jika lebih dari 5
        $oldResults = Result::where('id_num', $validated['id_num'])
            ->orderByDesc('calibration_date')
            ->skip(5)->take(PHP_INT_MAX)->get();

        foreach ($oldResults as $old) {
            $old->delete();
        }

        IncompleteInput::forCurrentUser()
            ->where('master_list_id', $request->master_list_id)
            ->delete();

        return redirect()->route('kalibrasi.input.calibration.data')->with('success', 'Calibration result were successfully saved.');
    }

    public function edit(Request $request, Result $result, $id)
    {
        $validated = $request->validate([
            'id_num' => 'required|exists:master_lists,id_num',
            'calibration_date' => 'nullable|date',
            'calibrator_equipment' => $request->calibration_type === 'Internal'
                ? 'required|exists:master_lists,id_num'
                : 'nullable',
            'param_01' => 'nullable|numeric',
            'param_02' => 'nullable|numeric',
            'param_03' => 'nullable|numeric',
            'param_04' => 'nullable|numeric',
            'param_05' => 'nullable|numeric',
            'param_06' => 'nullable|numeric',
            'param_07' => 'nullable|numeric',
            'param_08' => 'nullable|numeric',
            'param_09' => 'nullable|numeric',
            'param_10' => 'nullable|numeric',
            'judgement' => 'required|string|in:OK,NG,Disposal',
            'certificate' => $request->calibration_type === 'External'
                ? 'file|mimes:pdf|max:2048'
                : 'nullable',
        ]);

        $validated['created_by'] = auth()->user()->employeeID;
        $validated['id_num'] = strtoupper($request->id_num);

        if ($request->hasFile('certificate')) {
            if ($result->certificate && Storage::exists($result->certificate)) {
                Storage::delete($result->certificate);
            }
            $idNum = $validated['id_num'];
            $date = Carbon::parse($validated['calibration_date'])->format('dmY');
            $ext = $request->file('certificate')->getClientOriginalExtension();

            $filename = "{$idNum}-{$date}.{$ext}";
            $folder = "certificates/{$idNum}";

            $path = $request->file('certificate')->storeAs($folder, $filename);

            $validated['certificate'] = $path;
        }

        foreach (range(1, 9) as $i) {
            $key = "param_0{$i}";
            if (isset($validated[$key])) {
                $validated[$key] = (float) $validated[$key];
            }
        }

        if (isset($validated['param_10'])) {
            $validated['param_10'] = (float) $validated['param_10'];
        }

        if (is_null($validated['calibration_date'])) {
            $validated['calibration_date'] = now()->toDateString();
        }

        // Simpan hasil kalibrasi
        $result = Result::find($id);
        $result->update($validated);

        return redirect()->route('kalibrasi.input.calibration.data')->with('success', 'Calibration result were successfully updated.');
    }
}
