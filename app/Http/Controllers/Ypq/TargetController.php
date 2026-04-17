<?php

namespace App\Http\Controllers\Ypq;

use App\Http\Controllers\Controller;
use App\Models\Target;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index()
    {
        $targets = Target::orderByRaw("
            CASE report
                WHEN 'consistency_supervisor' THEN 1
                WHEN 'consistency_leader' THEN 2
                WHEN 'score_supervisor' THEN 3
                WHEN 'score_leader' THEN 4
                WHEN 'score_operator' THEN 5
                ELSE 6
            END
        ")->get();
        return view('targets.index', compact('targets'));
    }

    public function update(Request $request)
    {
        // 1. Validasi pake kolom report
        $request->validate([
            'report' => 'required|exists:targets,report',
            'value' => 'required|numeric|min:0|max:100',
        ]);

        // 2. Cari datanya berdasarkan report, lalu update
        $target = Target::where('report', $request->report)->firstOrFail();

        $target->update([
            'value' => $request->value,
        ]);

        // 3. Kembalikan response JSON
        return response()->json([
            'success' => true,
            'message' => 'Target berhasil diperbarui!'
        ]);
    }
}
