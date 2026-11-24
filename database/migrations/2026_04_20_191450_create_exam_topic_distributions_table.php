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
        Schema::create('exam_topic_distributions', function (Blueprint $table) {
            $table->id();
            $table->integer('question_count');

            $table->foreignId('config_id')
                ->constrained('exam_configs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('topic_id')
                ->constrained('topics')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_topic_distributions');
    }
};
