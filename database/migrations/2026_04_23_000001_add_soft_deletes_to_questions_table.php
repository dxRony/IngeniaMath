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
        Schema::table('questions', function (Blueprint $table) {
            $table->softDeletes();
        });

        DB::table('questions')
            ->where('is_deleted', true)
            ->update(['deleted_at' => now()]);

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('resource_url');
        });

        DB::table('questions')
            ->whereNotNull('deleted_at')
            ->update(['is_deleted' => true]);

        Schema::table('questions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
