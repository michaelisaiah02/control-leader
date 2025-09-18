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
        Schema::connection('mysql_control_leader')->create('questions', function (Blueprint $table) {
            $table->id();

            // 5 paket pertanyaan
            $table->enum('package', [
                'op_awal',        // operator awal shift
                'op_bekerja',     // operator saat bekerja
                'op_istirahat',   // operator setelah istirahat
                'op_akhir',       // operator akhir shift
                'leader',         // supervisor cek leader (sekali/hari, no shift split)
            ])->default('op_awal');

            $table->text('question_text'); // pertanyaan inti

            // tipe soal: A, B, atau C (lihat mockup kamu)
            $table->enum('answer_type', ['a', 'b', 'c'])->default('a');

            // pilihan jawaban (JSON array [{value,label}])
            $table->json('choices')->nullable();

            // kalau memilih value tertentu → wajib isi problem/countermeasure
            $table->json('require_problem_when')->nullable();

            // label field tambahan
            $table->string('problem_label')->nullable();
            $table->string('countermeasure_label')->nullable();

            // urutan tampil di checksheet
            $table->integer('display_order')->default(0);

            $table->boolean('is_active')->default(true)->comment('Set True untuk digunakan di checksheet');

            $table->timestamps();

            $table->index(['package', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
