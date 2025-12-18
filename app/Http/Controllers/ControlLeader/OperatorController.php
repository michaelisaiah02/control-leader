<?php

namespace App\Http\Controllers\ControlLeader;

use App\Http\Controllers\Controller;
use App\Models\ControlLeader\Division;
use App\Models\ControlLeader\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OperatorController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'operator')->with('division')->get();
        $leaders = User::where('role', 'leader')->where('superior_id', auth()->guard('web_control_leader')->user()->employeeID)->get();
        $divisions = Division::all();

        return view('control.schedule.operator', compact('users', 'divisions', 'leaders'), [
            'title' => 'DATA OPERATOR',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employeeID' => ['required', 'size:5', Rule::unique('mysql_control_leader.users', 'employeeID')],
            'division_id' => ['required', 'integer', Rule::exists('mysql_control_leader.divisions', 'id')],
        ]);

        $validated['name'] = Str::ucfirst($validated['name']);
        $validated['password'] = Hash::make(Str::random(10)); // Set default password
        $validated['role'] = 'operator';
        $validated['can_login'] = false;

        User::create($validated);

        return redirect()->route('control.operator.index')->with('success', 'Operator added successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employeeID' => [
                'required',
                'size:5',
                Rule::unique('mysql_control_leader.users', 'employeeID')->ignore($user->id),
            ],
            'division' => [
                'required',
                'integer',
                Rule::exists('mysql_control_leader.divisions', 'id'),
            ],
        ]);

        $user->update($validated);

        return redirect()->route('control.operator.index')
            ->with('success', 'Operator updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('control.operator.index')->with('success', 'Operator has been successfully deleted.');
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');
        $leaderInput = $request->query('leader');

        $leaderIds = collect($leaderInput === null ? [] : (array) $leaderInput)
            ->flatMap(fn ($value) => is_array($value) ? $value : explode(',', (string) $value))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values();

        $users = User::query()
            ->where('role', 'operator')
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('employeeID', 'like', "%{$keyword}%");
                });
            })
            ->when($leaderIds->isNotEmpty(), function ($query) use ($leaderIds) {
                $query->whereIn('superior_id', $leaderIds->all());
            })
            ->with('division')
            ->orderBy('employeeID', 'asc')
            ->get();

        return response()->json([
            'html' => view('control.schedule.partials.table_rows', compact('users'))->render(),
        ]);
    }
}
