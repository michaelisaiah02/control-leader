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
        Schema::connection('mysql_control_leader')->create('checksheets', function (Blueprint $table) {
            $table->id();
            // Dulu FK ke users, tapi sekarang cukup dari schedule_id saja
            $table->foreignId('schedule_detail_id')->constrained('schedule_details');
            $table->enum('type', ['awal_shift', 'saat_bekerja', 'setelah_istirahat', 'akhir_shift']);
            $table->unsignedInteger('stopwatch_duration')->comment('Durasi dalam detik');

            // Kolom untuk 4 jawaban Bagian A yang tetap
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
