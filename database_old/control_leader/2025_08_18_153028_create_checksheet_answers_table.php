<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/control_leader/....php
    public function up(): void
    {
        Schema::connection('mysql_control_leader')->create('checksheet_answers', function (Blueprint $table) {
            $table->id();
            // onDelete('cascade') berarti jika sebuah checksheet dihapus, semua jawabannya akan ikut terhapus.
            $table->foreignId('checksheet_id')->constrained('checksheets')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions');
            $table->string('answer'); // Menyimpan pilihan jawaban, misal: "0", "1", "2"
            $table->text('problem')->nullable(); // Diisi jika jawaban "0" atau "1"
            $table->text('countermeasure')->nullable(); // Diisi jika jawaban "0" atau "1"
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
