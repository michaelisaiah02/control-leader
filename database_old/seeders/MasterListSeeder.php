<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipmentList = DB::table('equipments')->get(); // Ambil semua alat ukur
        $counterType = [];
        $units = DB::table('units')->pluck('id', 'symbol');

        foreach ($equipmentList as $alat) {
            $randomUnit = $units->random();
            // Hitung berapa kali tipe ini sudah digunakan
            $type = $alat->type_id;
            if (! isset($counterType[$type])) {
                $counterType[$type] = 1;
            } else {
                $counterType[$type]++;
            }

            // Format nomor ID: TIM-001, CAL-001, dll
            $noId = $type.'-'.str_pad($counterType[$type], 3, '0', STR_PAD_LEFT);

            DB::table('master_lists')->insert([
                'type_id' => $type,
                'id_num' => $noId,
                'sn_num' => 'SN-'.strtoupper(Str::random(5)),
                'capacity' => 'max 100',
                'accuracy' => '1',
                'unit_id' => $randomUnit,
                'brand' => 'ACME',
                'calibration_type' => ['Internal', 'External'][rand(0, 1)],
                'first_used' => Carbon::now()->setMonth(6),
                'rank' => 'A',
                'calibration_freq' => 6,
                'acceptance_criteria' => 'apa aja boleh',
                'pic' => fake('id_ID')->name(),
                'location' => 'Gudang A',
            ]);
        }
    }
}
