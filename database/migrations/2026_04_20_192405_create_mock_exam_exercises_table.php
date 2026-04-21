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
        Schema::create('mock_exam_exercises', function (Blueprint $table) {
            $table->foreignId('exam_id')->constrained('mock_exams')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();

            $table->integer('order');
            $table->text('given_answer')->nullable();
            $table->integer('resolution_seconds')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->boolean('is_correct')->nullable();

            $table->primary(['exam_id', 'exercise_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mock_exam_exercises');
    }
};
