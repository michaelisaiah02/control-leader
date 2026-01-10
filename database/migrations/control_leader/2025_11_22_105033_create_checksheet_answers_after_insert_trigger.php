<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * Trigger AFTER INSERT
         */

        DB::connection('mysql_control_leader')->unprepared("
            CREATE TRIGGER tr_checksheet_answers_after_insert
            AFTER INSERT ON checksheet_answers
            FOR EACH ROW
            BEGIN
                DECLARE v_leader_name VARCHAR(255);
                DECLARE v_operator_id CHAR(5);
                DECLARE v_operator_name VARCHAR(255);

                IF NEW.problem IS NOT NULL AND NEW.problem != '' THEN
                    -- Ambil leader dari schedule plan
                    SELECT u.name INTO v_leader_name
                    FROM checksheets c
                    JOIN schedule_plans sp ON sp.id = c.schedule_plan_id
                    JOIN users u ON u.employeeID = sp.scheduler_id
                    WHERE c.id = NEW.checksheet_id
                    LIMIT 1;

                    -- Ambil operator yang dinilai
                    SELECT u.employeeID, u.name INTO v_operator_id, v_operator_name
                    FROM checksheets c
                    JOIN users u ON u.employeeID = c.target
                    WHERE c.id = NEW.checksheet_id
                    LIMIT 1;

                    INSERT INTO problems (
                        checksheet_answer_id,
                        leader_name,
                        operator_id,
                        operator_name,
                        problem,
                        countermeasure,
                        status,
                        created_at,
                        updated_at
                    ) VALUES (
                        NEW.id,
                        v_leader_name,
                        v_operator_id,
                        v_operator_name,
                        NEW.problem,
                        NEW.countermeasure,
                        'Open',
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('mysql_control_leader')->unprepared("DROP TRIGGER IF EXISTS tr_checksheet_answers_after_insert");
    }
};
