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
        Schema::create('master_lists', function (Blueprint $table) {
            $table->id();

            $table->string('type_id'); // Buat kolom dulu
            $table->foreign('type_id') // Baru buat foreign key-nya
                ->references('type_id')->on('equipments')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->string('id_num')->unique();
            $table->string('sn_num');
            $table->string('capacity');
            $table->decimal('accuracy', 10, 2);

            $table->foreignId('unit_id')->nullable()->constrained('units')
                ->onUpdate('cascade')->onDelete('set null');

            $table->string('brand');
            $table->enum('calibration_type', ['Internal', 'External']);
            $table->date('first_used');
            $table->string('rank');
            $table->integer('calibration_freq');
            $table->string('acceptance_criteria');
            $table->string('pic');
            $table->string('location');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_lists');
    }
};
