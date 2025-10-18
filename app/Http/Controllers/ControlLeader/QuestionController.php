<?php

namespace App\Http\Controllers\ControlLeader;

use App\Http\Controllers\Controller;
use App\Models\ControlLeader\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        return view("control.admin.questions.index");
    }

    public function create()
    {
        return view('control.admin.questions.create');
    }

    public function delete(Question $question)
    {
        $question->delete();
        return redirect()->route('questions.index')->with('success', 'Question deleted.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package' => 'required|in:awal_shift,saat_bekerja,setelah_istirahat,akhir_shift,leader',
            'question_text' => 'required|string',
            'choices' => 'nullable|array',
            'problem_label' => 'nullable|string',
            'countermeasure_label' => 'nullable|string'
        ]);

        $validated['extra_fields'] = $request->countermeasure_label && $request->problem_label ? true : false;
        Question::create($validated);

        return redirect()->route('questions.index')->with('success', 'Question created successfully!');
    }

    public function edit(Question $question)
    {
        return view('control.admin.questions.edit', compact(['question']));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'package' => 'required|in:awal_shift,saat_bekerja,setelah_istirahat,akhir_shift,leader',
            'question_text' => 'required|string',
            'choices' => 'nullable|array',
            'problem_label' => 'nullable|string',
            'countermeasure_label' => 'nullable|string'
        ]);

        $validated['extra_fields'] = $request->countermeasure_label && $request->problem_label ? true : false;
        $question->update($validated);

        return redirect()->route('question.index')->with('success', 'Question updated successfully!');
    }
}
