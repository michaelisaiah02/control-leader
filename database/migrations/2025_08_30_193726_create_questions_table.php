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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            // 5 paket pertanyaan
            $table->enum('package', [
                'awal_shift',        // operator awal shift
                'saat_bekerja',     // operator saat bekerja
                'setelah_istirahat',   // operator setelah istirahat
                'akhir_shift',       // operator akhir shift
                'leader',         // supervisor cek leader (sekali/hari, no shift split)
            ])->default('awal_shift');

            $table->text('question_text'); // pertanyaan inti

            // pilihan jawaban (JSON array [{value,label}])
            $table->json('choices')->nullable();

            // true = wajib isi problem/countermeasure, false = tidak perlu
            $table->boolean('extra_fields');

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
