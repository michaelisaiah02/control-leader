<?php

namespace App\Http\Controllers\Kalibrasi\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\MasterList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipments = Equipment::paginate(5);

        return view('kalibrasi.admin.equipments.index', compact('equipments'), [
            'title' => 'MASTER DATA INPUT - EQUIPMENTS TABLE',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_id' => ['required', 'string', 'min:3', 'unique:equipments,type_id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['type_id'] = strtoupper($validated['type_id']);

        Equipment::create($validated);

        return redirect()->route('kalibrasi.admin.equipments.index')->with('success', 'Equipment added successfully.');
    }

    public function update(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);
        $oldTypeId = $equipment->type_id;

        $validated = $request->validate([
            'type_id' => [
                'required',
                'min:3',
                Rule::unique('equipments', 'type_id')->ignore($equipment->id),
            ],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['type_id'] = strtoupper($validated['type_id']);

        $equipment->update($validated);

        // Update id_num di master_lists
        if ($oldTypeId !== $equipment->type_id) {
            $masterLists = MasterList::where('type_id', $equipment->type_id)->get();
            foreach ($masterLists as $ml) {
                // Ambil nomor urutnya dari id_num (misal: TIM-005 → 005)
                $no = substr($ml->id_num, -3);
                $ml->type_id = $equipment->type_id;
                $ml->id_num = $equipment->type_id.'-'.$no;
                $ml->save();
            }
        }

        return redirect()->route('kalibrasi.admin.equipments.index')->with('success', 'Equipment updated successfully.');
    }

    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->delete();

        return redirect()->route('kalibrasi.admin.equipments.index')->with('success', 'Equipment deleted successfully.');
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = Equipment::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('type_id', 'like', "%{$keyword}%");
            });

        $equipments = $query->orderBy('created_at', 'desc')->paginate(5);

        return response()->json([
            'html' => view('kalibrasi.admin.equipments.partials.table_rows', compact('equipments'))->render(),
            'pagination' => view('kalibrasi.admin.equipments.partials.pagination', compact('equipments'))->render(),
        ]);
    }
}
