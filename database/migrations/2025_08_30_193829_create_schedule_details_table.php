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
        Schema::create('schedule_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_plan_id')->constrained('schedule_plans')->cascadeOnDelete()->cascadeOnUpdate();
            $table->char('target_user_id', 5);
            $table->string('division')->nullable();
            $table->string('shift')->nullable();
            $table->date('scheduled_date');
            $table->timestamps();

            $table->foreign('target_user_id')
                ->references('employeeID')
                ->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['target_user_id', 'scheduled_date'], 'sd_user_date_unique');
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
