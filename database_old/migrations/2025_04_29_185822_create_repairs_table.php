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
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->string('id_num');
            $table->foreign('id_num')->references('id_num')->on('master_lists')->onUpdate('cascade')->onDelete('restrict');
            $table->date('problem_date');
            $table->date('repair_date');
            $table->string('problem');
            $table->text('countermeasure');
            $table->string('pic_repair');
            $table->enum('judgement', ['OK', 'NG', 'Disposal']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
