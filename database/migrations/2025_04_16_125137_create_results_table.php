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
        Schema::create('results', function (Blueprint $table) {
            $table->id();

            // Foreign key ke master_lists
            $table->string('id_num');
            $table->foreign('id_num')->references('id_num')->on('master_lists')->onUpdate('cascade')->onDelete('restrict');

            $table->date('calibration_date')->default(now());
            $table->string('calibrator_equipment')->nullable()->default(null);
            $table->foreign('calibrator_equipment')->references('id_num')->on('master_lists')->onUpdate('cascade')->onDelete('restrict');

            $table->decimal('param_01', 10, 5)->nullable();
            $table->decimal('param_02', 10, 5)->nullable();
            $table->decimal('param_03', 10, 5)->nullable();
            $table->decimal('param_04', 10, 5)->nullable();
            $table->decimal('param_05', 10, 5)->nullable();
            $table->decimal('param_06', 10, 5)->nullable();
            $table->decimal('param_07', 10, 5)->nullable();
            $table->decimal('param_08', 10, 5)->nullable();
            $table->decimal('param_09', 10, 5)->nullable();
            $table->decimal('param_10', 10, 5)->nullable();

            $table->enum('judgement', ['OK', 'NG', 'Disposal']);
            $table->char('created_by', 5); // harus cocok dengan tipe employeeID
            $table->foreign('created_by')->references('employeeID')->on('users')->onDelete('cascade');
            $table->string('certificate')->nullable()->default(null);

            $table->boolean('is_approved')->default(false);
            $table->boolean('is_checked')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
