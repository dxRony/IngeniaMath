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
        Schema::create('mastery_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->integer('min_threshold')->nullable();
            $table->integer('max_threshold')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mastery_levels');
    }
};
