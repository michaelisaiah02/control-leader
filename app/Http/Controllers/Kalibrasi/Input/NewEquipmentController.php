<?php

namespace App\Http\Controllers\Kalibrasi\Input;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\IncompleteInput;
use App\Models\MasterList;
use App\Models\Unit;
use Illuminate\Http\Request;

class NewEquipmentController extends Controller
{
    public function create()
    {
        return view('kalibrasi.input.new-equipment', [
            'title' => 'INPUT NEW EQUIPMENT',
            'equipments' => Equipment::all(),
            'units' => Unit::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_id' => ['required', 'string', 'max:255'],
            'id_num' => ['required', 'string', 'unique:master_lists,id_num'],
            'sn_num' => ['required', 'string'],
            'capacity' => ['required', 'string'],
            'accuracy' => ['required', 'numeric'],
            'unit_id' => ['required', 'exists:units,id'],
            'brand' => ['required', 'string'],
            'calibration_type' => ['required', 'in:Internal,External'],
            'first_used' => ['required', 'date'],
            'rank' => ['required', 'in:AA,A,B,C'],
            'calibration_freq' => ['required', 'integer'],
            'acceptance_criteria' => ['required', 'string'],
            'pic' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string'],
        ]);

        $validated['id_num'] = strtoupper($validated['id_num']);

        $master = MasterList::create($validated);

        IncompleteInput::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'master_list_id' => $master->id,
            ],
            ['stage' => 'standard'] // karena habis input master, sekarang wajib input standard
        );

        return redirect()->route('kalibrasi.print.label', $validated['id_num'])->with([
            'success' => 'The equipment was successfully added.',
            'key' => 'menu-input',
        ]);
    }
}
