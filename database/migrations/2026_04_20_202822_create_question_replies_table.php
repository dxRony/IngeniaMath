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
        Schema::create('question_replies', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->string('resource_url', 255)->nullable();
            $table->boolean('is_accepted')->default(false);
            $table->boolean('is_deleted')->default(false);

            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_replies');
    }
};
