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
            $table->foreignId('schedule_plan_id')->constrained('schedule_plans')
                ->cascadeOnDelete();
            $table->unsignedInteger('stopwatch_duration')->comment('Durasi dalam detik')->nullable();
            $table->string('phase')->default('awal_shift')
                ->comment('Fase checksheet: awal_shift, bekerja, istirahat, akhir_shift, leader');
            $table->integer('score')->unsigned()->comment('Skor checksheet (jumlah poin benar)')->default(0);

            // opsional: simpan juga 'scheduled_target' biar report makin jelas
            $table->string('scheduled_target')->nullable()
                ->comment('Snapshot target yang dijadwalkan: "id - nama"');

            // Kolom untuk 4 jawaban Bagian A yang tetap
            $table->integer('shift', false, true)
                ->comment('Shift: 1, 2, 3');
            $table->foreignId('target')->constrained('users')->cascadeOnDelete();
            $table->string('division');
            $table->string('attendance');
            $table->string('condition')->nullable();

            $table->boolean('has_replacement')->default(false)
                ->comment('Apakah ada operator pengganti untuk checksheet ini?');
            // Apakah ini checksheet sebagai operator pengganti?
            $table->boolean('replacement')->default(false)
                ->comment('false = scheduled/original; true = replacement (yang dinilai)');
            $table->unsignedBigInteger('replacement_of_id')->nullable()
                ->comment('ID checksheet parent (scheduled). Null bila scheduled/hadir');

            // Operator Pengganti
            $table->string('replacement_name')->nullable();
            $table->string('replacement_division')->nullable();
            $table->string('replacement_condition')->nullable();

            $table->timestamps();

            $table->index(['phase']);
            $table->index('replacement_of_id');
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
