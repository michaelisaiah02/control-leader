<?php

namespace App\Http\Controllers;

use App\Models\Problem;

use Illuminate\Http\Request;

class ProblemListController extends Controller
{
    public function index()
    {
        return view('problem_list.index');
    }

    public function list($type)
    {
        $Problems = Problem::all();
        return view('problem_list.list', compact(['type', 'Problems']));
    }

    public function editTemplate($type)
    {
        return view('problem_list.edit', compact(['type']));
    }
}
