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
        Schema::connection('mysql_control_leader')->create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            // Opsi untuk menyimpan pilihan jawaban, misal: {"0": "Tidak mengikuti", "1": "Tidak memperhatikan", "2": "Mengikuti sampai selesai"}
            $table->json('options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
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
