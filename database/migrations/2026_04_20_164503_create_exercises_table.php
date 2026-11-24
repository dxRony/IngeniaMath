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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->text('statement');
            $table->string('image_url', 255)->nullable();
            $table->text('correct_answer');
            $table->integer('estimated_minutes');
            $table->text('concept_explanation');
            $table->foreignId('subtopic_id')->constrained('subtopics')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('exercise_levels')->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('exercise_types')->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('exercise_statuses')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
