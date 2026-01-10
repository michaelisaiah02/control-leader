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
        Schema::connection('mysql_control_leader')->create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('users'); // FK ke tabel users
            // $table->foreignId('department_id')->constrained('departments'); // FK ke tabel departments
            $table->tinyInteger('shift'); // 1, 2, atau 3
            $table->date('scheduled_date');
            $table->timestamps();

            $table->unique(['operator_id', 'scheduled_date']); // Mencegah 1 operator dijadwalkan dua kali di hari yang sama
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
