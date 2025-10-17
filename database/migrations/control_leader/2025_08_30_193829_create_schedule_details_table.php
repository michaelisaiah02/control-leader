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
            $table->string('division')->nullable();
            $table->date('scheduled_date');
            $table->timestamps();

            $table->index(['schedule_plan_id', 'scheduled_date'], 'sd_plan_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_details');
    }
};
