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
        Schema::create('assessment_module_results', function (Blueprint $table) {
            $table->id();
            $table->decimal('score', 5, 2);

            $table->foreignId('assessment_id')->constrained('initial_assessments')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
            $table->foreignId('mastery_level_id')->constrained('mastery_levels')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_module_results');
    }
};
