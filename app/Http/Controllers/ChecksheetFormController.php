<?php

namespace App\Http\Controllers;

use App\Models\ControlLeader\ChecksheetField;
use App\Models\ControlLeader\Question;
use Illuminate\Http\Request;

class ChecksheetFormController extends Controller
{
    public function create()
    {
        return view('control.admin.checksheet.create');
    }

    public function store(Request $request)
    {
        $question = new Question();
        $question->fields = $request->input('fields');
        $question->save();

        return response()->json(['success' => true, 'id' => $question->id]);
    }

    public function edit($id)
    {
        $question = Question::findOrFail($id);
        return view('control.admin.checksheet.create', compact(['question']));
    }

    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        $question->fields = $request->input('fields');
        $question->save();

        return response()->json(['success' => true]);
    }
}
