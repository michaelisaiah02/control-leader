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
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_answer_id')->constrained('checksheet_answers')->cascadeOnDelete();
            $table->string('leader_name');            // dari users
            $table->string('operator_id', 5);         // employeeID
            $table->string('operator_name');          // dari users
            $table->text('problem')->nullable();
            $table->text('countermeasure')->nullable();
            $table->enum('status', ['open', 'close', 'delay', 'follow_up_1', 'follow_up_1_delay'])->default('open');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};
