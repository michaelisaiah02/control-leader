<?php

namespace App\Http\Controllers\Kalibrasi\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterList;
use App\Models\Standard;
use Illuminate\Http\Request;

class StandardController extends Controller
{
    public function index()
    {
        $standards = Standard::paginate(5);
        $masterLists = MasterList::all();

        return view('kalibrasi.admin.standards.index', compact('standards', 'masterLists'), [
            'title' => 'EQUIPMENT ACCEPTANCE CRITERIA FORM',
        ]);
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

        return redirect()->route('kalibrasi.admin.standards.index')->with([
            'success' => 'Standard added successfully.',
        ]);
    }

    public function update(Request $request, $id)
    {
        $standard = Standard::findOrFail($id);

        $validated = $request->validate([
            'id_num' => ['required', 'string', 'max:255', 'unique:standards,id_num,' . $standard->id],
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

        $standard->update($validated);

        return redirect()->route('kalibrasi.admin.standards.index')->with('success', 'Standard updated successfully.');
    }

    public function destroy($id)
    {
        $standard = Standard::findOrFail($id);
        $standard->delete();

        return redirect()->route('kalibrasi.admin.standards.index')->with('success', 'Standard has been successfully deleted.');
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = Standard::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('id', 'like', "%{$keyword}%");
            });

        $standards = $query->orderBy('created_at', 'desc')->paginate(5);

        return response()->json([
            'html' => view('kalibrasi.admin.standards.partials.table_rows', compact('standards'))->render(),
            'pagination' => view('kalibrasi.admin.standards.partials.pagination', compact('standards'))->render(),
        ]);
    }
}
