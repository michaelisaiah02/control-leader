<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ControlLeader\Question;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // database/seeders/QuestionSeeder.php
        Question::updateOrCreate(
            ['question_code' => '5_MIN_TALK_ATTENDANCE'],
            [
                'question_text' => 'Apakah operator ikut 5 Minute Talk sampai selesai?',
                'options' => json_encode([
                    '0' => 'Operator tidak mengikuti 5 minutes talk',
                    '1' => 'Operator mengikuti namun tidak memperhatikan',
                    '2' => 'Operator mengikuti sampai selesai',
                ]),
                'display_order' => 1,
                'is_active' => true,
            ]
        );
        Question::updateOrCreate(
            ['question_code' => 'TOOLS_CONDITION'],
            [
                'question_text' => 'Bagaimana kondisi alat kerja operator?',
                'options' => json_encode([
                    '0' => 'Operator tidak mengikuti 5 minutes talk',
                    '1' => 'Operator mengikuti namun tidak memperhatikan',
                    '2' => 'Operator mengikuti sampai selesai',
                ]),
                'display_order' => 2,
                'is_active' => true,
            ]
        );
    }
}
