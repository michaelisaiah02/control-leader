<?php

namespace App\Http\Controllers\Kalibrasi\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterList;
use App\Models\Unit;
use Illuminate\Http\Request;

class MasterListController extends Controller
{
    public function index()
    {
        $masterlists = MasterList::paginate(5);
        $units = Unit::all();

        return view('kalibrasi.admin.master-lists.index', compact('masterlists', 'units'), [
            'title' => 'MASTER DATA INPUT - MASTER LISTS TABLE',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_id' => ['required', 'exists:equipments,type_id'],
            'id_num' => ['required', 'string', 'unique:master_lists,id_num'],
            'sn_num' => ['required', 'string'],
            'capacity' => ['required', 'string'],
            'accuracy' => ['required', 'numeric', 'min:0.01'],
            'unit_id' => ['required', 'exists:units,id'],
            'brand' => ['required', 'string'],
            'calibration_type' => ['required', 'in:Internal,External'],
            'first_used' => ['required', 'date'],
            'rank' => ['required', 'in:AA,A,B,C'],
            'calibration_freq' => ['required', 'integer', 'min:0'],
            'acceptance_criteria' => ['required', 'string'],
            'pic' => ['required', 'string'],
            'location' => ['required', 'string'],
        ]);

        MasterList::create($validated);

        return redirect()->route('kalibrasi.admin.master-lists.index')->with('success', 'Master List successfully added.');
    }

    public function update(Request $request, $id)
    {
        $unit = MasterList::findOrFail($id);

        $validated = $request->validate([
            'sn_num' => ['required', 'string'],
            'capacity' => ['required', 'string'],
            'accuracy' => ['required', 'integer', 'min:0'],
            'unit_id' => ['required', 'exists:units,id'],
            'brand' => ['required', 'string'],
            'calibration_type' => ['required', 'in:Internal,External'],
            'first_used' => ['required', 'date'],
            'rank' => ['required', 'in:AA,A,B,C'],
            'calibration_freq' => ['required', 'integer', 'min:0'],
            'acceptance_criteria' => ['required', 'string'],
            'pic' => ['required', 'string'],
            'location' => ['required', 'string'],
        ]);

        $unit->update($validated);

        return redirect()->route('kalibrasi.admin.master-lists.index')->with('success', 'Master List successfully updated.');
    }

    public function destroy($id)
    {
        $unit = MasterList::findOrFail($id);
        $unit->delete();

        return redirect()->route('kalibrasi.admin.master-lists.index')->with('success', 'Master List was successfully deleted.');
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = MasterList::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('id_num', 'like', "%{$keyword}%")
                    ->orWhere('sn_num', 'like', "%{$keyword}%");
            });

        $masterlists = $query->orderBy('created_at', 'desc')->paginate(5);

        return response()->json([
            'html' => view('kalibrasi.admin.master-lists.partials.table_rows', compact('masterlists'))->render(),
            'pagination' => view('kalibrasi.admin.master-lists.partials.pagination', compact('masterlists'))->render(),
        ]);
    }
}
