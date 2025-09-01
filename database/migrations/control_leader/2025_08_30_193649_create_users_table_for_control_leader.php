<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('mysql_control_leader')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('employeeID')->unique();
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->string('password');
            $table->enum('role', ['admin', 'guest', 'supervisor', 'leader']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_control_leader')->dropIfExists('users');
    }
};
