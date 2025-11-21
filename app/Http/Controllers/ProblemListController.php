<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProblemListController extends Controller
{
    public function index()
    {
        return view('control.problem_list.index');
    }

    public function list($type)
    {
        return view('control.problem_list.list', compact(['type']));
    }

    public function editTemplate($type)
    {
        return view('control.problem_list.edit', compact(['type']));
    }
}
