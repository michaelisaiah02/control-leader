<?php

namespace App\Http\Controllers\ControlLeader;

use App\Models\ControlLeader\Problem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProblemListController extends Controller
{
    public function index()
    {
        return view('control.problem_list.index');
    }

    public function list($type)
    {
        $Problems = Problem::all();
        return view('control.problem_list.list', compact(['type', 'Problems']));
    }

    public function editTemplate($type)
    {
        return view('control.problem_list.edit', compact(['type']));
    }
}
