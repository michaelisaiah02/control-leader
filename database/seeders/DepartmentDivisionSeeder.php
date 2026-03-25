<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Division;
use Illuminate\Database\Seeder;

class DepartmentDivisionSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Warehouse',
            'Compound',
            'Hose',
            'Molding',
            'Weatherstrip',
            'Bushing',
        ];

        $divisions = [
            'Warehouse' => 'Warehouse',
            'Compound' => [
                'Weighing',
                'Mixing A',
                'Mixing B',
                'Lab',
            ],
            'Molding' => [
                'Cutting Compound',
                'Press Molding',
                'Finishing Molding',
                'QC Molding',
                'Packing Molding',
            ],
            'Hose' => [
                'Extrude',
                'Braiding',
                'Cutting Hose',
                'Tandem',
                'Mandreling',
                'Autoclave',
                'Cutting Finishing Hose',
                'Leak Test',
                'Washing Hose',
                'Pre-Inspection Hose',
                'Marking Hose',
                'Assy Hose',
                'Final Inspection Hose',
                'Packing Hose',
            ],
            'Bushing' => [
                'Washing',
                'Shotblast',
                'Apply Primer',
                'Apply Adhesive',
                'Hot Press',
                'Burry Removal',
                'Squeezing',
                'Grinding',
                'Washing Bushing',
                'Check Adhesive',
                'Final Inspection Bushing',
                'Packing Bushing',
            ],
            'Weatherstrip' => [
                'Injection PVC',
                'Mesin UHF',
                'Primer Adhesive',
                'Double Tape',
                'Cutting Weatherstrip',
                'QC Weatherstrip',
                'Packing Weatherstrip',
            ],
        ];

        foreach ($departments as $deptName) {
            $department = Department::create(['name' => $deptName]);

            if (isset($divisions[$deptName]) && is_array($divisions[$deptName])) {
                foreach ($divisions[$deptName] as $divName) {
                    Division::create([
                        'name' => $divName,
                        'department_id' => $department->id,
                    ]);
                }
            }
        }
    }
}
