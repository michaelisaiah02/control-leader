<?php

namespace App\Http\Controllers\Kalibrasi\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::paginate(5);

        return view('kalibrasi.admin.units.index', compact('units'), [
            'title' => 'MASTER DATA INPUT - UNITS TABLE',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'symbol' => [
                'required',
                'string',
                Rule::unique('units')->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $existing = Unit::withTrashed()->where('symbol', $validated['symbol'])->first();

        if ($existing) {
            // Unit pernah ada, tinggal reaktivasi
            $existing->restore(); // kalau pakai soft delete

            $existing->update([
                'name' => $validated['name'],
            ]);
        } else {
            Unit::create($validated);
        }

        return redirect()->route('kalibrasi.admin.units.index')->with('success', 'Unit added successfully.');
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'symbol' => [
                'required',
                Rule::unique('units', 'symbol')->ignore($unit->id),
            ],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $unit->update($validated);

        return redirect()->route('kalibrasi.admin.units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('kalibrasi.admin.units.index')->with('success', 'Unit was successfully deleted.');
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = Unit::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('symbol', 'like', "%{$keyword}%");
            });

        $units = $query->orderBy('created_at', 'desc')->paginate(5);

        return response()->json([
            'html' => view('kalibrasi.admin.units.partials.table_rows', compact('units'))->render(),
            'pagination' => view('kalibrasi.admin.units.partials.pagination', compact('units'))->render(),
        ]);
    }
}
