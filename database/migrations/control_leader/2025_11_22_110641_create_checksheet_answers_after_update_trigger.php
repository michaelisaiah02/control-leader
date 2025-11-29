<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * Trigger AFTER UPDATE
         */

        DB::connection('mysql_control_leader')->unprepared("
            CREATE TRIGGER tr_checksheet_answers_after_update
            AFTER UPDATE ON checksheet_answers
            FOR EACH ROW
            BEGIN
                IF NEW.problem IS NOT NULL AND NEW.problem != '' THEN

                    UPDATE problems
                    SET 
                        problem = NEW.problem,
                        countermeasure = NEW.countermeasure,
                        updated_at = NOW()
                    WHERE checksheet_answer_id = NEW.id;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('mysql_control_leader')->unprepared("DROP TRIGGER IF EXISTS tr_checksheet_answers_after_update");
    }
};
