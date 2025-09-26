<?php

namespace App\Http\Controllers;

use App\Models\ControlLeader\ChecksheetField;
use Illuminate\Http\Request;

class ChecksheetFormController extends Controller
{
    public function create()
    {
        return view('control.admin.checksheet.create');
    }

    public function store(Request $request)
    {
        foreach ($request->fields as $field) {
            ChecksheetField::create($field);
        }
        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        $fields = ChecksheetField::all();
        return view('control.admin.checksheet.edit', compact('fields'));
    }

    public function update(Request $request, $id)
    {
        ChecksheetField::truncate();

        foreach ($request->fields as $field) {
            ChecksheetField::create($field);
        }
        return response()->json(['success' => true]);
    }
}
