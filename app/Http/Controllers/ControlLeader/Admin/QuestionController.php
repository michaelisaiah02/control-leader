<?php

namespace App\Http\Controllers\ControlLeader\Admin;

use App\Http\Controllers\Controller;
use App\Models\ControlLeader\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Question::query();

        if ($request->has('package') && $request->package != '') {
            $query->where('package', $request->package);
        }

        $questions = $query->orderBy('package')
            ->orderBy('display_order')
            ->paginate(5);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('control.admin.questions._table', compact(['questions']))->render(),
                'paginate' => $questions->links()->toHtml()
            ]);
        }

        return view("control.admin.questions.index", compact(['questions']));
    }

    public function create()
    {
        return view('control.admin.questions.create');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('control.question.index')->with('success', 'Question deleted.');
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

        return redirect()->route('control.question.index')->with('success', 'Question created successfully!');
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

        return redirect()->route('control.question.index')->with('success', 'Question updated successfully!');
    }

    public function updateOrder(Request $request)
    {
        $order = $request->input('order');
        $package = $request->input('package');

        foreach ($order as $item) {
            Question::where('id', $item['id'])
                ->where('package', $package)
                ->update(['display_order' => $item['display_order']]);
        }

        return response()->json(['success' => true]);
    }
}
