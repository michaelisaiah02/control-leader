<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OperatorController extends Controller
{
    public function index()
    {
        // Ambil department_id dari user yang lagi login
        $userDeptId = auth()->user()->department_id;

        // Filter operator berdasarkan departemen login (jika ada)
        $users = User::where('role', 'operator')
            ->when($userDeptId, function ($query, $deptId) {
                $query->where('department_id', $deptId);
            })
            ->with('division')
            ->get();

        // Filter leader biar dropdown-nya relevan sama departemennya doang
        $leaders = User::where('role', 'leader')
            ->when($userDeptId, function ($query, $deptId) {
                $query->where('department_id', $deptId);
            })
            ->with('department')
            ->get();

        // Filter divisi, terus di-grouping
        $divisions = Division::with('department')
            ->when($userDeptId, function ($query, $deptId) {
                $query->where('department_id', $deptId);
            })
            ->get()
            ->groupBy(function ($data) {
                return $data->department ? $data->department->name : 'Tanpa Departemen';
            });

        return view('schedule.operator', compact('users', 'divisions', 'leaders'), [
            'title' => 'DATA OPERATOR',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employeeID' => [
                'required',
                'size:5',
                function ($attribute, $value, $fail) {
                    $existingUser = User::where('employeeID', $value)->first();
                    if ($existingUser) {
                        $role = strtoupper($existingUser->role);
                        $fail("ID {$value} sudah terdaftar atas nama {$existingUser->name} (Role: {$role}). Silakan cek di menu yang sesuai.");
                    }
                }
            ],
            'division_id' => ['required', 'integer', Rule::exists('divisions', 'id')],
            'superior_id' => ['nullable', 'integer', Rule::exists('users', 'employeeID')],
        ]);

        $validated['name'] = Str::ucfirst($validated['name']);
        $validated['password'] = Hash::make(Str::random(10));
        $validated['role'] = 'operator';
        $validated['can_login'] = false;

        $division = Division::find($request->division_id);
        $validated['department_id'] = $division->department_id;

        User::create($validated);

        return redirect()->route('operator.index')->with('success', 'Operator added successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employeeID' => [
                'required',
                'size:5',
                function ($attribute, $value, $fail) use ($user) {
                    $existingUser = User::where('employeeID', $value)->where('id', '!=', $user->id)->first();
                    if ($existingUser) {
                        $role = strtoupper($existingUser->role);
                        $fail("ID {$value} sudah terdaftar atas nama {$existingUser->name} (Role: {$role}).");
                    }
                }
            ],
            'division_id' => [
                'required',
                'integer',
                Rule::exists('divisions', 'id'),
            ],
            'superior_id' => ['nullable', 'integer', Rule::exists('users', 'employeeID')],
        ]);

        if ($validated['division_id'] != $user->division_id) {
            $division = Division::find($validated['division_id']);
            $validated['department_id'] = $division->department_id;
        }

        $user->update($validated);

        return redirect()->route('operator.index')
            ->with('success', 'Operator updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('operator.index')->with('success', 'Operator has been successfully deleted.');
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');
        $leaderInput = $request->query('leader');

        // Ambil department_id user yang login
        $userDeptId = auth()->user()->department_id;

        $leaderIds = collect($leaderInput === null ? [] : (array) $leaderInput)
            ->flatMap(fn($value) => is_array($value) ? $value : explode(',', (string) $value))
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->values();

        $users = User::query()
            ->where('role', 'operator')
            // Tembok pertahanan utama: kunci di department_id user yang login
            ->when($userDeptId, function ($query, $deptId) {
                $query->where('department_id', $deptId);
            })
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
            'html' => view('schedule.partials.table_rows', compact('users'))->render(),
        ]);
    }
}
