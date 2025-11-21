<?php

namespace App\Http\Controllers\ControlLeader;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ControlLeader\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\ControlLeader\Division;

class OperatorController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'operator')->paginate(5);
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
        $leader = $request->query('leader');

        $query = User::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('employeeID', 'like', "%{$keyword}%");
                });
            });

        $users = $query->where('role', 'like', 'operator')->where('superior_id', $request)->with('division')->orderBy('created_at', 'desc')->paginate(5);

        return response()->json([
            'html' => view('control.schedule.partials.table_rows', compact('users'))->render(),
            'pagination' => view('control.schedule.partials.pagination', compact('users'))->render(),
        ]);
    }
}
