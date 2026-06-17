<?php

namespace App\Http\Controllers\Ypq;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::with('department')->get();
        $departments = Department::all();

        return view('divisions.index', compact('divisions', 'departments'), [
            'title' => 'DIVISIONS MANAGEMENT',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value && !Department::where('id', $value)->exists()) {
                        $fail('Selected department does not exist.');
                    }
                }
            ]
        ]);

        Division::create($validated);
        return redirect()->route('divisions.index')->with('success', 'Division added successfully.');
    }

    public function update(Request $request, $id)
    {
        $division = Division::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value && !Department::where('id', $value)->exists()) {
                        $fail('Selected department does not exist.');
                    }
                }
            ]
        ]);

        $division->update($validated);
        return redirect()->route('divisions.index')->with('success', 'Division updated successfully.');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $divisions = Division::with('department')
            ->where('name', 'like', "%{$query}%")
            ->orWhereHas('department', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->get();

        return response()->json([
            'html' => view('divisions.partials.table_rows', compact('divisions'))->render(),
        ]);
    }
}
