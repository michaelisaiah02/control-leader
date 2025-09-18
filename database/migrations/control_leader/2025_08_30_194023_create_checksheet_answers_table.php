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
        Schema::connection('mysql_control_leader')->create('checksheet_answers', function (Blueprint $table) {
            $table->id();

            // Relasi ke checksheet utama
            $table->foreignId('checksheet_id')
                ->constrained('checksheets')
                ->onDelete('cascade');

            // Relasi ke pertanyaan master (supaya tau pertanyaan mana, walaupun snapshot tetap disimpan)
            $table->foreignId('question_id')
                ->nullable()
                ->constrained('questions')
                ->onDelete('set null');

            // Snapshot data pertanyaan → aman kalau pertanyaan diubah / dihapus
            $table->text('question_text');
            $table->enum('answer_type', ['a', 'b', 'c'])->default('a');
            $table->json('choices')->nullable();

            // Jawaban utama
            $table->string('answer_value')->nullable();  // ex: 0,1,2
            $table->string('answer_label')->nullable();  // ex: "Operator mengikuti sampai selesai"

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
