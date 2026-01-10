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
        Schema::connection('mysql_control_leader')->create('checksheet_answers', function (Blueprint $table) {
            $table->id();

            // Relasi ke checksheet utama
            $table->foreignId('checksheet_id')
                ->constrained('checksheets')
                ->onDelete('cascade');

            // Snapshot data pertanyaan → aman kalau pertanyaan diubah / dihapus
            $table->text('question_text');
            $table->json('choices')->nullable()->comment('Pilihan jawaban dalam JSON array');

            // Jawaban utama
            $table->integer('answer_value', false, true)->nullable()
                ->comment('Jawaban pilihan sesuai index array, ex: 0,1,2');

            // Extra field kalau required
            $table->text('problem')->nullable();
            $table->text('countermeasure')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checksheet_answers');
    }
};
