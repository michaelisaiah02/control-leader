<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('employeeID', 5);
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('division_id')->nullable()->constrained('divisions');
            $table->enum('role', ['management', 'ypq', 'admin', 'guest', 'supervisor', 'leader', 'operator']);
            $table->char('superior_id', 5)->nullable();
            $table->string('password');
            $table->boolean('can_login')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('control_session_id', 100)->nullable()->index();
            $table->boolean('cl_in_progress')->default(false);
            $table->timestamp('cl_last_ping')->nullable();
            $table->timestamps();

            $table->unique('employeeID');

            $table->foreign('superior_id')->references('employeeID')->on('users')->nullOnDelete();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
