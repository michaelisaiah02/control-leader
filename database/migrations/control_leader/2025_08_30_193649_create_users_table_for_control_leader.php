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
            $table->enum('role', ['admin', 'guest', 'supervisor', 'leader', 'operator']);
            $table->boolean('can_login')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('control_session_id', 100)->nullable()->index();
            $table->boolean('cl_in_progress')->default(false);
            $table->timestamp('cl_last_ping')->nullable();
            $table->timestamps();

            $table->index('employeeID');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_control_leader')->dropIfExists('users');
    }
};
