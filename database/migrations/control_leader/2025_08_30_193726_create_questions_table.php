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
            $table->string('question_code')->unique()->comment('Kode unik untuk konsep pertanyaan');
            $table->text('question_text');
            $table->json('options')->nullable();
            $table->integer('display_order')->default(0)->comment('Urutan tampil di checksheet');
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
