<?php

namespace App\Http\Controllers\Kalibrasi\Input;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use Illuminate\Http\Request;

class RepairDataController extends Controller
{
    public function create(Repair $repair)
    {
        return view('kalibrasi.input.repair-data', [
            'title' => 'REPAIR DATA INPUT',
            'repairs' => $repair->all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_num' => ['required', 'string', 'max:255'],
            'problem_date' => ['required', 'date', 'before_or_equal:repair_date'],
            'repair_date' => ['required', 'date', 'after_or_equal:problem_date', 'before_or_equal:now'],
            'problem' => ['required', 'string'],
            'countermeasure' => ['required', 'string'],
            'judgement' => ['required', 'string', 'in:OK,NG,Disposal'],
        ]);

        $validated['id_num'] = strtoupper($request->id_num);
        $validated['pic_repair'] = auth()->user()->name;

        Repair::create($validated);

        return redirect()->route('kalibrasi.input.repair')->with('success', 'Repair data successfully added.');
    }

    public function edit(Request $request, Repair $repair, $id)
    {
        // dd($request->all());
        $validated = $request->validate([
            'problem_date' => ['required', 'date', 'before_or_equal:repair_date'],
            'repair_date' => ['required', 'date', 'after_or_equal:problem_date', 'before_or_equal:now'],
            'problem' => ['required', 'string'],
            'countermeasure' => ['required', 'string'],
            'judgement' => ['required', 'string', 'in:OK,NG,Disposal'],
        ]);
        $validated['pic_repair'] = auth()->user()->name;

        $repair = Repair::find($id);
        $repair->update($validated);

        return redirect()->route('kalibrasi.input.repair')->with('success', 'Repair data successfully updated.');
    }
}
