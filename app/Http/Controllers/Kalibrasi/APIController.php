<?php

namespace App\Http\Controllers\Kalibrasi;

use App\Models\MasterList;
use App\Models\Repair;
use App\Models\Result;

class APIController
{
    /**
     * Display a listing of the resource.
     */
    public function countEquipments($type_id)
    {
        $count = MasterList::where('type_id', $type_id)->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function getMasterList($id_num)
    {
        $data = MasterList::with(['equipment', 'unit', 'standard'])
            ->where('id_num', $id_num)
            ->first();
        if ($data->calibration_type === 'Internal') {
            $calibrator_equipments = MasterList::with('equipment')
                ->where('id_num', '!=', $id_num)
                ->get()
                ->map(function ($item) {
                    return [
                        'id_num' => $item->id_num,
                        'equipment_name' => $item->equipment->name ?? '(Unknown)',
                    ];
                });
        }
        if (! $data) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'sn_num' => $data->sn_num,
            'equipment_name' => $data->equipment->name ?? null,
            'capacity' => $data->capacity,
            'accuracy' => $data->accuracy,
            'unit' => $data->unit->symbol ?? null,
            'brand' => $data->brand,
            'location' => $data->location,
            'pic' => $data->pic,
            'calibration_type' => $data->calibration_type,
            'acceptance_criteria' => $data->acceptance_criteria,
            'calibrator_equipments' => $calibrator_equipments ?? '',
            'standard' => $data->standard,
        ]);
    }

    public function getActualValue($id)
    {
        $data = Result::where('id', $id)
            ->first();

        if (! $data) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'calibration_date' => $data->calibration_date->setTimezone('Asia/Jakarta')->format('Y-m-d'),
            'calibrator_equipment' => $data->calibrator_equipment,
            'judgement' => $data->judgement,
            'created_by' => $data->creator->name ?? null,
            'certificate' => $data->certificate,
            'param_01' => $data->param_01,
            'param_02' => $data->param_02,
            'param_03' => $data->param_03,
            'param_04' => $data->param_04,
            'param_05' => $data->param_05,
            'param_06' => $data->param_06,
            'param_07' => $data->param_07,
            'param_08' => $data->param_08,
            'param_09' => $data->param_09,
            'param_10' => $data->param_10,
        ]);
    }

    public function getRepairData($id)
    {
        $data = Repair::where('id', $id)->first();

        if (! $data) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'problem' => $data->problem,
            'problem_date' => $data->problem_date->setTimezone('Asia/Jakarta')->format('Y-m-d'),
            'repair_date' => $data->repair_date->setTimezone('Asia/Jakarta')->format('Y-m-d'),
            'countermeasure' => $data->countermeasure,
            'judgement' => $data->judgement,
            'pic_repair' => $data->pic_repair,
        ]);
    }
}
