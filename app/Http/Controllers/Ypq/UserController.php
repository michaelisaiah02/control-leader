<?php

namespace App\Http\Controllers\Ypq;

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
        $users = User::with('superior')->whereNotIn('role', ['operator'])->orderBy('employeeID')->get();
        $departments = Department::all();

        return view('users.index', compact('users', 'departments'), [
            'title' => 'MASTER DATA INPUT - USERS TABLE',
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
                        $fail("ID {$value} sudah dipakai oleh {$existingUser->name} sebagai {$role}.");
                    }
                }
            ],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'role' => ['required', 'in:management,ypq,leader,supervisor,guest'],
            'password' => ['required', 'string', 'min:8'],
            'superior_id' => ['nullable', 'string', Rule::exists('users', 'employeeID')],
        ]);

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User added successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employeeID' => [
                'required',
                'size:5',
                function ($attribute, $value, $fail) use ($user) {
                    $existingUser = User::where('employeeID', $value)->where('id', '!=', $user->id)->first();
                    if ($existingUser) {
                        $role = strtoupper($existingUser->role);
                        $fail("ID {$value} bentrok dengan {$existingUser->name} yang menjabat sebagai {$role}.");
                    }
                }
            ],
            'role' => 'required|in:management,ypq,leader,supervisor,guest',
            'password' => 'nullable|string|min:6',
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'superior_id' => ['nullable', 'string', Rule::exists('users', 'employeeID')],
        ]);

        $data = $validated;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if (is_null($request->password)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User has been successfully deleted.');
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

        $users = $query->orderBy('employeeID')->get();

        return response()->json([
            'html' => view('users.partials.table_rows', compact('users'))->render(),
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
                $query = User::where('role', 'supervisor');

                // Hanya filter departemen jika departmentId tidak kosong
                if (! empty($departmentId)) {
                    $query->where('department_id', $departmentId);
                }

                $superiors = $query->get();
                break;

            case 'supervisor':
                // Logic: Supervisor butuh YPQ (Bebas departemen karena tidak ada departemen di user YPQ)
                $superiors = User::where('role', 'ypq')->get();
                break;

            case 'ypq':
                // Logic: YPQ butuh Management (Bebas departemen karena tidak ada departemen di user Management)
                $superiors = User::where('role', 'management')->get();
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
                'department_id' => $user->department_id,
            ];
        });

        return response()->json($formattedSuperiors);
    }
}
