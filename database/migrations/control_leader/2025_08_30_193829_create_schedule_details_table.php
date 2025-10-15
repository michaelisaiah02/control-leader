<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mysql_control_leader')->create('schedule_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_plan_id')->constrained('schedule_plans')->onDelete('cascade');
            $table->foreignId('target_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('target_leader_id')->nullable()->constrained('users');
            $table->string('division')->nullable();
            $table->date('scheduled_date');
            $table->timestamps();

            $table->index(['schedule_plan_id', 'scheduled_date'], 'sd_plan_date_idx');
        });

        // Backfill target_user_id dari employeeID
        $users = DB::connection('mysql_control_leader')->table('users')
            ->where('role', 'Operator')->pluck('id', 'employeeID'); // [empID => user_id]

        $rows = DB::connection('mysql_control_leader')->table('schedule_details')
            ->select('id', 'target_operator_id')->get();

        foreach ($rows as $r) {
            if (!$r->target_operator_id)
                continue;
            $uid = $users[$r->target_operator_id] ?? null;
            if ($uid) {
                DB::connection('mysql_control_leader')->table('schedule_details')
                    ->where('id', $r->id)->update(['target_user_id' => $uid]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_details');
    }
};
