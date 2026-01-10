<?php

namespace App\Http\Controllers\Kalibrasi\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(5);

        return view('kalibrasi.admin.users.index', compact('users'), [
            'title' => 'MASTER DATA INPUT - USERS TABLE',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employeeID' => ['required', 'size:5', 'unique:users,employeeID'],
            'role' => ['required', 'in:admin,user,guest'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
            'approved' => ['boolean'],
            'checked' => ['boolean'],
        ]);

        if ($validated['role'] !== 'admin' && $validated['approved']) {
            return back()->withErrors(['approved' => 'Approved can only be true if the role is admin.']);
        }

        if ($validated['role'] === 'guest' && $validated['checked']) {
            return back()->withErrors(['checked' => 'Checked can only be true if the role is not guest.']);
        }

        // approved dan checked dari semua user hanya satu yang boleh true, jadi jika ada yang true, maka yang lain otomatis di ubah jadi false
        if ($validated['approved']) {
            User::where('approved', true)->update(['approved' => false]);
        }
        if ($validated['checked']) {
            User::where('checked', true)->update(['checked' => false]);
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('kalibrasi.admin.users.index')->with('success', 'User added successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employeeID' => ['required', 'size:5', Rule::unique('users', 'employeeID')->ignore($user->id)],
            'role' => 'required|in:admin,user,guest',
            'password' => 'nullable|string|min:6',
            // no need to validate approved/checked here
        ]);

        // read them as booleans
        $approved = $request->boolean('approved');
        $checked = $request->boolean('checked');

        // role-based rules
        if ($validated['role'] !== 'admin' && $approved) {
            return back()->withErrors(['approved' => 'Only admin can be approved']);
        }
        if ($validated['role'] === 'guest' && $checked) {
            return back()->withErrors(['checked' => 'Guest cannot be checked']);
        }

        // enforce uniqueness: only one approved & one checked
        if ($approved) {
            User::where('approved', true)->update(['approved' => false]);
        }
        if ($checked) {
            User::where('checked', true)->update(['checked' => false]);
        }

        // Jika tidak ada user lain yang approved dan user ini tidak ingin di-approve, tolak
        if (! User::where('approved', true)->where('id', '!=', $user->id)->exists() && $approved === false) {
            return back()->withErrors(['approved' => 'At least one user must be approved.']);
        }

        // Jika tidak ada user lain yang checked dan user ini tidak ingin di-check, tolak
        if (! User::where('checked', true)->where('id', '!=', $user->id)->exists() && $checked === false) {
            return back()->withErrors(['checked' => 'At least one user must be checked.']);
        }

        // put everything into $data for update
        $data = $validated;
        $data['approved'] = $approved;
        $data['checked'] = $checked;

        // hash password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // if password is null, remove it from $data to avoid updating it
        if (is_null($request->password)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('kalibrasi.admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('kalibrasi.admin.users.index')->with('success', 'User has been successfully deleted.');
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = User::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('employeeID', 'like', "%{$keyword}%");
            });

        $users = $query->orderBy('created_at', 'desc')->paginate(5);

        return response()->json([
            'html' => view('kalibrasi.admin.users.partials.table_rows', compact('users'))->render(),
            'pagination' => view('kalibrasi.admin.users.partials.pagination', compact('users'))->render(),
        ]);
    }
}
