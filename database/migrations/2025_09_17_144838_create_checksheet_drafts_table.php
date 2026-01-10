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
        Schema::create('checksheet_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('schedule_plan_id')->constrained('schedule_plans')->cascadeOnDelete();
            $table->string('phase');                       // awal_shift|...
            $table->string('session_id')->nullable();      // buat single-device enforcement (opsional)
            $table->timestamp('started_at')->nullable();   // untuk stopwatch
            $table->json('payload')->nullable();        // simpan jawaban sementara
            $table->timestamp('last_ping')->nullable();    // heartbeat
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'schedule_plan_id', 'phase'], 'draft_unique_user_plan_phase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checksheet_drafts');
    }
};
