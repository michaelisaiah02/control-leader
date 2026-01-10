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
            $table->foreignId('leader_id')->constrained('users');
            $table->foreignId('schedule_id')->constrained('schedules');
            $table->enum('type', ['awal_shift', 'saat_bekerja', 'setelah_istirahat', 'akhir_shift']);
            $table->unsignedInteger('stopwatch_duration')->comment('Durasi dalam detik');

            // Kolom untuk 4 jawaban Bagian A yang tetap. Anda bisa ganti namanya agar lebih deskriptif.
            $table->string('part_a_answer_1')->nullable();
            $table->string('part_a_answer_2')->nullable();
            $table->string('part_a_answer_3')->nullable();
            $table->string('part_a_answer_4')->nullable();

            $table->timestamps();
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
