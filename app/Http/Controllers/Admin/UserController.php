<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('superior')->whereNotIn('role', ['operator'])->get();
        $departments = Department::all();

        return view('admin.users.index', compact('users', 'departments'), [
            'title' => 'MASTER DATA INPUT - USERS TABLE',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employeeID' => ['required', 'size:5', 'unique:users,employeeID'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'role' => ['required', 'in:admin,management,ypq,leader,supervisor,guest'],
            'password' => ['required', 'string', 'min:8'],
            'superior_id' => ['nullable', 'string', Rule::exists('users', 'employeeID')],
        ]);

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User added successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employeeID' => ['required', 'size:5', Rule::unique('users', 'employeeID')->ignore($user->id)],
            'role' => 'required|in:admin,management,ypq,leader,supervisor,guest',
            'password' => 'nullable|string|min:6',
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'superior_id' => ['nullable', 'string', Rule::exists('users', 'employeeID')]
        ]);

        // put everything into $data for update
        $data = $validated;

        // hash password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // if password is null, remove it from $data to avoid updating it
        if (is_null($request->password)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User has been successfully deleted.');
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');
        $role = $request->query('role');

        $query = User::query()->where('role', '!=', 'operator')
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('employeeID', 'like', "%{$keyword}%");
                });
            })
            ->when($role, function ($q) use ($role) {
                $q->where('role', $role); // <-- filter role kalau ada
            });

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'html' => view('admin.users.partials.table_rows', compact('users'))->render(),
        ]);
    }

    public function getSuperiors(Request $request)
    {
        // 1. Ambil input
        $role = $request->query('role');
        $departmentId = $request->query('department_id');

        // 2. Normalisasi input (Jaga-jaga huruf besar)
        $role = strtolower($role);

        $superiors = []; // Inisialisasi array kosong

        switch ($role) {
            case 'leader':
                // Logic: Leader butuh Supervisor di departemen yang sama
                $rolesToFetch = ['supervisor'];

                // Query
                $query = User::whereIn('role', $rolesToFetch);

                // Hanya filter departemen jika departmentId tidak kosong
                if (!empty($departmentId)) {
                    $query->where('department_id', $departmentId);
                }

                $superiors = $query->get();
                break;

            case 'supervisor':
                // Logic: Supervisor butuh YPQ atau Management (Bebas departemen?)
                $rolesToFetch = ['ypq', 'management'];
                $superiors = User::whereIn('role', $rolesToFetch)->get();
                break;

            default:
                // Jika role tidak dikenali
                return response()->json([]);
        }

        // 3. Transformasi Data (Opsional tapi disarankan)
        // Supaya frontend nerima struktur yang pasti
        $formattedSuperiors = $superiors->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'employeeID' => $user->employee_id ?? $user->employeeID, // Sesuaikan kolom DB
                'role' => $user->role,
                'department_id' => $user->department_id
            ];
        });

        return response()->json($formattedSuperiors);
    }
}
