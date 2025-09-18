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
        Schema::connection('mysql_control_leader')->create('checksheet_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('schedule_detail_id')->constrained('schedule_details')->cascadeOnDelete();
            $table->string('phase');                       // awal_shift|...
            $table->string('session_id')->nullable();      // buat single-device enforcement (opsional)
            $table->timestamp('started_at')->nullable();   // untuk stopwatch
            $table->timestamp('last_ping')->nullable();    // heartbeat
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'schedule_detail_id', 'phase']);
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
