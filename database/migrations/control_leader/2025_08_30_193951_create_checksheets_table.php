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
        Schema::connection('mysql_control_leader')->create('checksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_plan_id')
                ->constrained('schedule_plans')
                ->cascadeOnDelete();
            $table->unsignedInteger('stopwatch_duration')->comment('Durasi dalam detik');
            $table->string('phase')->default('awal_shift');

            // Kolom untuk 4 jawaban Bagian A yang tetap
            $table->string('shift');
            $table->string('target')->comment('Format: id - nama');
            $table->string('division');
            $table->string('attendance');

            // Kondisi Target
            $table->string('condition')->nullable();

            // Operator Pengganti
            $table->string('replacement_name')->nullable();
            $table->string('replacement_division')->nullable();
            $table->string('replacement_condition')->nullable();

            $table->timestamps();

            $table->index(['phase']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checksheets');
    }
};
