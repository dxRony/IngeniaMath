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
        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->id();

            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('total_exercises');
            $table->integer('total_correct')->nullable();
            $table->integer('total_incorrect')->nullable();
            $table->integer('total_time_seconds')->nullable();

            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mode_id')->constrained('session_modes')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
            $table->foreignId('initial_difficulty_id')->constrained('difficulty_levels')->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('practice_statuses')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_sessions');
    }
};
