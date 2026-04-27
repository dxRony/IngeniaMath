<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subtopic_level_progress', function (Blueprint $table) {
            $table->dropForeign(['current_difficulty_id']);
        });

        Schema::table('subtopic_level_progress', function (Blueprint $table) {
            $table->foreign('current_difficulty_id')
                ->references('id')
                ->on('exercise_levels')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subtopic_level_progress', function (Blueprint $table) {
            $table->dropForeign(['current_difficulty_id']);
        });

        Schema::table('subtopic_level_progress', function (Blueprint $table) {
            $table->foreign('current_difficulty_id')
                ->references('id')
                ->on('difficulty_levels')
                ->cascadeOnDelete();
        });
    }
};
