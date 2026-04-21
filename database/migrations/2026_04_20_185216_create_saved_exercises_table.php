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
        Schema::create('saved_exercises', function (Blueprint $table) {
            $table->id();
            $table->timestamp('saved_at');

            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('session_exercise_id')->constrained('session_exercises')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_exercises');
    }
};
