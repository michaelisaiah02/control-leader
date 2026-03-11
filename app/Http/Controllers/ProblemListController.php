<?php

namespace App\Http\Controllers;

use App\Models\ConsistencyProblem;
use App\Models\Problem;
use Illuminate\Http\Request;

class ProblemListController extends Controller
{
    public function index()
    {
        // 1. Hitung Performance Problems
        $leaderPerformanceCount = Problem::whereHas('user', function ($query) {
            $query->where('role', 'leader');
        })->where('status', '!=', 'close')->count();

        $supervisorPerformanceCount = Problem::whereHas('user', function ($query) {
            $query->where('role', 'supervisor');
        })->where('status', '!=', 'close')->count();

        // 2. Hitung Consistency Problems (Karena udah ada tabel dan kolom role_type, query-nya lebih gampang)
        $leaderConsistencyCount = ConsistencyProblem::where('role_type', 'leader')
            ->where('status', '!=', 'close')->count();

        $supervisorConsistencyCount = ConsistencyProblem::where('role_type', 'supervisor')
            ->where('status', '!=', 'close')->count();

        return view('problem_list.index', compact(
            'leaderPerformanceCount',
            'supervisorPerformanceCount',
            'leaderConsistencyCount',
            'supervisorConsistencyCount'
        ));
    }

    public function list($type)
    {
        $roleFilter = explode('-', $type)[0]; // 'leader' atau 'supervisor'
        $isPerformance = str_contains($type, 'performance');

        // Tarik data beda tabel tergantung tipe
        if ($isPerformance) {
            $Problems = Problem::with(['user', 'inferior'])
                ->whereHas('user', function ($query) use ($roleFilter) {
                    $query->where('role', $roleFilter);
                })
                ->latest()
                ->get();
        } else {
            // Consistency
            $Problems = ConsistencyProblem::with(['user', 'inferior'])
                ->where('role_type', $roleFilter)
                ->latest()
                ->get();
        }

        return view('problem_list.list', compact(['type', 'Problems']));
    }

    public function edit($type, $id)
    {
        $isPerformance = str_contains($type, 'performance');

        // Cari data di tabel yang bener
        $problem = $isPerformance
            ? Problem::findOrFail($id)
            : ConsistencyProblem::findOrFail($id);

        return view('problem_list.edit', compact(['type', 'problem']));
    }

    public function update(Request $request, $type, $id)
    {
        $isPerformance = str_contains($type, 'performance');
        $pageRole = explode('-', $type)[0];
        $loggedInRole = auth()->user()->role;

        // Cari data di tabel yang bener
        $problem = $isPerformance
            ? Problem::findOrFail($id)
            : ConsistencyProblem::findOrFail($id);

        // Otorisasi Dinamis
        $canEditCountermeasure = $loggedInRole === $pageRole;
        $canEditStatus = false;

        if ($pageRole === 'leader') {
            $canEditStatus = in_array($loggedInRole, ['supervisor', 'management', 'ypq']);
        } elseif ($pageRole === 'supervisor') {
            $canEditStatus = in_array($loggedInRole, ['management', 'ypq']);
        }

        if (! $canEditCountermeasure && ! $canEditStatus) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $rules = [];

        // Rakit rules validasi
        if ($canEditCountermeasure) {
            $rules['countermeasure'] = 'required|string';
        }

        if ($canEditStatus) {
            $rules['status'] = 'required|in:open,close,delay,follow_up_1,follow_up_1_delay';
            if (! $problem->is_due_date_changed) {
                $rules['due_date'] = 'required|date';
            }
        }

        $validated = $request->validate($rules);

        // Eksekusi Save
        if ($canEditCountermeasure && isset($validated['countermeasure'])) {
            $problem->countermeasure = $validated['countermeasure'];
        }

        if ($canEditStatus) {
            if (isset($validated['status'])) {
                $problem->status = $validated['status'];
            }
            if (isset($validated['due_date'])) {
                $problem->due_date = $validated['due_date'];
            }
        }

        $problem->save();

        return redirect()->route('listProblem.list', $type)
            ->with('success', 'Data berhasil diperbarui! ✨');
    }
}
