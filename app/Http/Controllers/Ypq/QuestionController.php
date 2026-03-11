<?php

namespace App\Http\Controllers\Ypq;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('questions._table', compact(['questions']))->render(),
            ]);
        }

        return view('questions.index', compact(['questions']));
    }

    public function create()
    {
        return view('questions.create');
    }

    public function store(QuestionRequest $request)
    {
        $data = $request->validated();
        // Kalau checkbox dicentang nilainya 'on'/true, kalau nggak otomatis false
        $data['extra_fields'] = $request->has('extra_fields');

        // LOGIC BARU: Set urutan otomatis ke paling bawah!
        $maxOrder = Question::where('package', $data['package'])->max('display_order');
        $data['display_order'] = $maxOrder ? $maxOrder + 1 : 1;

        Question::create($data);

        return redirect()->route('question.index')->with('success', 'Question created successfully!');
    }

    public function edit(Question $question)
    {
        return view('questions.edit', compact(['question']));
    }

    public function update(QuestionRequest $request, Question $question)
    {
        $data = $request->validated();
        $data['extra_fields'] = $request->has('extra_fields');

        $question->update($data);

        return redirect()->route('question.index')->with('success', 'Question updated successfully!');
    }

    public function destroy(Question $question)
    {
        $package = $question->package;
        $order = $question->display_order;

        $question->delete();

        // LOGIC BARU (Batch Update): Geser semua urutan yang ada di bawahnya naik 1 level.
        // No more N+1 looping query! 🔥
        Question::where('package', $package)
            ->where('display_order', '>', $order)
            ->decrement('display_order');

        return redirect()->route('question.index')->with('success', 'Question deleted.');
    }

    public function updateOrder(Request $request)
    {
        // Validasi input biar aman
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:questions,id',
            'order.*.display_order' => 'required|integer',
        ]);

        $order = $request->input('order');

        // Gunakan transaction biar kalau ada satu error, semua batal (data safety)
        DB::transaction(function () use ($order) {
            foreach ($order as $item) {
                // Update display_order berdasarkan ID saja
                // Kita tidak perlu filter package di sini karena ID sudah unik
                Question::where('id', $item['id'])
                    ->update(['display_order' => $item['display_order']]);
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Urutan berhasil diperbarui']);
    }
}
