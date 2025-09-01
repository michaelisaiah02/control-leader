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
            $table->foreignId('checksheet_id')->constrained('checksheets')->onDelete('cascade');
            // FK ini tetap penting untuk menghubungkan ke konsep pertanyaan
            $table->foreignId('question_id')->constrained('questions');
            $table->string('answer');

            // Snapshot dari teks pertanyaan saat dijawab
            $table->text('question_text_snapshot');
            // Snapshot dari opsi jawaban saat itu (jika ada)
            $table->json('question_options_snapshot')->nullable();

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
