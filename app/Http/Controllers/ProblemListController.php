<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProblemListController extends Controller
{
    public function index()
    {
        return view('control.problem_list.index');
    }
}
