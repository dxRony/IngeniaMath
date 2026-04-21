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
        Schema::create('path_items', function (Blueprint $table) {
            $table->id();
            $table->integer('order');
            $table->boolean('is_completed')->default(false);

            $table->foreignId('path_id')->constrained('learning_paths')->cascadeOnDelete();
            $table->foreignId('subtopic_id')->constrained('subtopics')->cascadeOnDelete();
            $table->foreignId('mastery_level_id')->constrained('mastery_levels')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('path_items');
    }
};
