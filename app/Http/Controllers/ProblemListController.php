<?php

namespace App\Http\Controllers;

use App\Models\Problem;

class ProblemListController extends Controller
{
    public function index()
    {
        $problemCount = Problem::count();
        return view('problem_list.index', compact(['problemCount']));
    }

    public function edit($type, $id)
    {
        $problem = Problem::find($id);
        return view('problem_list.edit', compact(['type', 'problem']));
    }

    public function update(Request $request, $type, $id)
    {
        $problem = Problem::findOrFail($id);

        if (auth()->user()->role === 'leader') {
            $problem->update(['countermeasure' => $request->countermeasure]);
        } else if (auth()->user()->role === 'supervisor') {
            if ($problem->due_date !== null) {
                $problem->update([
                    'status' => $request->department,
                ]);
            } else {
                $problem->update([
                    'status' => $request->department,
                    'due_date' => $request->due_date
                ]);
            }
        }

        return redirect()->route('listProblem.list', ['type' => $type]);
    }

    public function list($type)
    {
        $Problems = Problem::all();

        return view('problem_list.list', compact(['type', 'Problems']));
    }
}
