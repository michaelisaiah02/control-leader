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
        Schema::connection('mysql_control_leader')->create('schedule_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_plan_id')->constrained('schedule_plans')->onDelete('cascade');
            $table->foreignId('target_leader_id')->nullable()->constrained('users');
            $table->string('target_operator_id')->nullable(); // ID Operator (input manual)
            $table->date('schedule_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_details');
    }
};
