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
        Schema::create('assessment_exercises', function (Blueprint $table) {
            $table->foreignId('assessment_id')->constrained('initial_assessments')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();

            $table->integer('order');
            $table->text('given_answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->boolean('is_correct')->nullable();

            $table->primary(['assessment_id', 'exercise_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_exercises');
    }
};
