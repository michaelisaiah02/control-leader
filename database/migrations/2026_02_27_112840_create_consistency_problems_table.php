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
        Schema::create('consistency_problems', function (Blueprint $table) {
            $table->id();
            // Yang ngecek (Leader/Supervisor) -> diambil dari schedule_plans.scheduler_id
            $table->char('user_id', 5);
            // Yang dicek (Operator/Leader) -> diambil dari schedule_details.target_user_id
            $table->char('inferior_id', 5);

            $table->enum('role_type', ['leader', 'supervisor']);
            $table->enum('remark', ['Miss', 'Late', 'Advanced']);

            // Konteks jadwal biar gampang di-trace
            $table->foreignId('schedule_detail_id')->nullable()->constrained('schedule_details')->nullOnDelete();

            $table->text('problem')->nullable();
            $table->text('countermeasure')->nullable();
            $table->enum('status', ['open', 'close', 'delay', 'follow_up_1', 'follow_up_1_delay'])->default('open');
            $table->date('due_date');
            $table->timestamps();

            // Relasi ke users
            $table->foreign('user_id')->references('employeeID')->on('users')->cascadeOnDelete();
            $table->foreign('inferior_id')->references('employeeID')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consistency_problems');
    }
};
