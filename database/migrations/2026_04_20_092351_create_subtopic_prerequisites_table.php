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
        Schema::create('subtopic_prerequisites', function (Blueprint $table) {
            $table->foreignId('subtopic_id')->constrained('subtopics')->cascadeOnDelete();
            $table->foreignId('prerequisite_id')->constrained('subtopics')->cascadeOnDelete();
            $table->primary(['subtopic_id', 'prerequisite_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subtopic_prerequisites');
    }
};
