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
        Schema::create('session_topic_summaries', function (Blueprint $table) {
            $table->id();
            $table->integer('total_exercises');
            $table->integer('total_correct');
            $table->integer('total_incorrect');

            $table->foreignId('session_id')->constrained('practice_sessions')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_topic_summaries');
    }
};
