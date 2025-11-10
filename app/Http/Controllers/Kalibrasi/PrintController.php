<?php

namespace App\Http\Controllers\Kalibrasi;

use App\Models\User;
use App\Models\Repair;
use App\Models\Result;
use App\Models\MasterList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PrintController extends Controller
{
    public function label($id)
    {
        $equipment = MasterList::with(['equipment', 'unit', 'results'])->where('id_num', $id)->firstOrFail();

        return view('kalibrasi.print-label', compact('equipment'));
    }

    public function reportMasterlist($id)
    {
        $result = Result::with(['masterList'])->where('id_num', $id)->orderByDesc('updated_at')->firstOrFail();
        $approved = User::where('approved', true)->first();
        $checked = User::where('checked', true)->first();

        return view('kalibrasi.print-report-masterlist', compact('result'), [
            'approved' => $approved,
            'checked' => $checked,
        ]);
    }

    public function reportRepair($id)
    {
        $repair = Repair::with(['masterList'])->where('id_num', $id)->firstOrFail();
        $approved = User::where('approved', true)->first();
        $checked = User::where('checked', true)->first();

        return view('kalibrasi.print-report-repair', compact('repair'), [
            'approved' => $approved,
            'checked' => $checked,
        ]);
    }

    public function updateMasterListPrint(Result $result, Request $request)
    {
        try {
            $request->validate([
                'is_approved' => 'nullable|boolean',
                'is_checked' => 'nullable|boolean',
            ]);

            if ($request->input('is_approved'))
                $result->is_approved = $request->input('is_approved');
            if ($request->input('is_checked'))
                $result->is_checked = $request->input('is_checked');
            $result->save();

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
