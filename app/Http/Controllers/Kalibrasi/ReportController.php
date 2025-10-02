<?php

namespace App\Http\Controllers\Kalibrasi;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\MasterList;
use App\Models\Repair;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function menu()
    {
        return view('kalibrasi.report.menu', [
            'title' => 'REPORT',
            'equipments' => Equipment::all(),
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $from = $request->input('date_from');
        $to = $request->input('date_to');
        $loc = $request->input('location');
        $cal = $request->input('calibration_type');
        $typeId = $request->input('type_id');
        $judg = $request->input('judgement');

        // Jika tombol Masterlist diklik
        if ($request->has('master_lists')) {
            $query = MasterList::query()
                ->with(['equipment', 'unit', 'latestResult']);

            // Filter tanggal first_used
            if ($from && $to) {
                $query->whereBetween('first_used', [$from, $to]);
            } elseif ($from) {
                $query->where('first_used', '>=', $from);
            } elseif ($to) {
                $query->where('first_used', '<=', $to);
            }

            if ($loc) {
                $query->where('location', $loc);
            }
            if ($cal) {
                $query->where('calibration_type', $cal);
            }
            if ($typeId) {
                $query->where('type_id', $typeId);
            }

            if ($judg) {
                $query->whereHas('latestResult', function ($q) use ($judg) {
                    $q->where('judgement', $judg);
                });
            }

            $results = $query->paginate(5)->withQueryString();

            return view('kalibrasi.report.masterlist', compact('results'))
                ->with('title', 'REPORT - MASTERLIST');
        }

        // Jika tombol Repair diklik
        if ($request->has('repairs')) {
            $query = Repair::query()->with(['masterList']);
            if ($from && $to) {
                $query->whereBetween('repair_date', [$from, $to]);
            } elseif ($from) {
                $query->where('repair_date', '>=', $from);
            } elseif ($to) {
                $query->where('repair_date', '<=', $to);
            }

            if ($loc) {
                $query->whereHas('masterList', function ($q) use ($loc) {
                    $q->where('location', $loc);
                });
            }
            if ($cal) {
                $query->whereHas('masterList', function ($q) use ($cal) {
                    $q->where('calibration_type', $cal);
                });
            }
            if ($typeId) {
                $query->whereHas('masterList', function ($q) use ($typeId) {
                    $q->where('type_id', $typeId);
                });
            }
            if ($judg) {
                $query->where('judgement', $judg);
            }

            $repairs = $query->paginate(5)->withQueryString();

            return view('kalibrasi.report.repair', compact('repairs'))
                ->with('title', 'REPORT - REPAIR');
        }

        return redirect()->back()->with('error', 'Select the type of report you want to view.');
    }
}
