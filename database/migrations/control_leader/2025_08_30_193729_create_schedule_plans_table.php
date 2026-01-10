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
        Schema::connection('mysql_control_leader')->create('schedule_plans', function (Blueprint $table) {
            $table->id();
            $table->char('scheduler_id', 5);
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->enum('type', ['leader_checks_operator', 'supervisor_checks_leader']);
            $table->timestamps();

            $table->foreign('scheduler_id')
                ->references('employeeID')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unique(['scheduler_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_plans');
    }
};
