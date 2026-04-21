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
        Schema::create('subtopic_level_progress', function (Blueprint $table) {
            $table->id();
            $table->integer('consecutive_correct')->default(0);
            $table->integer('consecutive_incorrect')->default(0);
            $table->integer('total_exercises')->default(0);
            $table->integer('total_correct')->default(0);
            $table->decimal('accuracy_percentage', 5, 2)->default(0.00);

            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subtopic_id')->constrained('subtopics')->cascadeOnDelete();
            $table->foreignId('current_difficulty_id')->constrained('difficulty_levels')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subtopic_level_progress');
    }
};
