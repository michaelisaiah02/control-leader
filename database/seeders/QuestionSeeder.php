<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ControlLeader\Question;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('mysql_control_leader')->table('questions')->insert([
            [
                'package' => 'op_awal',
                'question_text' => 'Apakah operator ikut 5 Minute Talk sampai selesai?',
                'answer_type' => 'a',
                'choices' => json_encode([
                    ['value' => '0', 'label' => 'Tidak mengikuti 5 minutes talk'],
                    ['value' => '1', 'label' => 'Mengikuti namun tidak memperhatikan'],
                    ['value' => '2', 'label' => 'Mengikuti sampai selesai'],
                ]),
                'require_problem_when' => json_encode(['0', '1']),
                'problem_label' => 'Problem',
                'countermeasure_label' => 'Countermeasure',
                'display_order' => 1,
            ],
            [
                'package' => 'op_awal',
                'question_text' => 'Apakah operator melakukan 5R sebelum bekerja?',
                'answer_type' => 'b',
                'choices' => json_encode([
                    ['value' => '0', 'label' => 'Tidak melakukan 5R'],
                    ['value' => '1', 'label' => 'Melakukan 5R dengan baik'],
                ]),
                'require_problem_when' => json_encode(['0']),
                'problem_label' => 'Problem',
                'countermeasure_label' => 'Quick Action',
                'display_order' => 2,
            ],
            [
                'package' => 'leader',
                'question_text' => 'Check kehadiran leader',
                'answer_type' => 'c',
                'choices' => json_encode([
                    ['value' => '0', 'label' => 'Selalu absen'],
                    ['value' => '1', 'label' => 'Ada hari tidak hadir'],
                    ['value' => '2', 'label' => 'Hadir selalu'],
                ]),
                'display_order' => 3,
            ],
        ]);

    }
}
