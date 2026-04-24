<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('question_replies', function (Blueprint $table) {
            $table->softDeletes();
        });

        DB::table('question_replies')
            ->where('is_deleted', true)
            ->update(['deleted_at' => now()]);

        Schema::table('question_replies', function (Blueprint $table) {
            $table->dropColumn('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_replies', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('is_accepted');
        });

        DB::table('question_replies')
            ->whereNotNull('deleted_at')
            ->update(['is_deleted' => true]);

        Schema::table('question_replies', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
