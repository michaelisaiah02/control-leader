<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ChecksheetController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->type;
        return view("checksheet.index", compact(['type']));
    }

    public function create(): View
    {
        return view("control_leader.input.add");
    }

    public function store(Request $request) {}

    public function edit($id): View
    {
        $checksheet = null;
        return view("control_leader.input.edit", compact(['checksheet']));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('checksheet.index');
    }
}
