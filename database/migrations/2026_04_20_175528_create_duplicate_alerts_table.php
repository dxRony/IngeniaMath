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
        Schema::create('duplicate_alerts', function (Blueprint $table) {
            $table->id();
            $table->decimal('similarity', 5, 2);
            $table->boolean('is_resolved')->default(false);

            $table->foreignId('new_exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('existing_exercise_id')->constrained('exercises')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duplicate_alerts');
    }
};
