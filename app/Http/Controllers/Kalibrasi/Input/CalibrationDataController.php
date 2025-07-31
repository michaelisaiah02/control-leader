<?php

namespace App\Http\Controllers\Kalibrasi\Input;

use App\Http\Controllers\Controller;
use App\Models\IncompleteInput;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CalibrationDataController extends Controller
{
    public function create()
    {
        return view('kalibrasi.input.calibration-data', [
            'title' => 'CALIBRATION DATA INPUT',
            'results' => Result::all(),
            'pending' => IncompleteInput::forCurrentUser()
                ->atStage('calibration')
                ->first(),
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
            dd($validated);
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
