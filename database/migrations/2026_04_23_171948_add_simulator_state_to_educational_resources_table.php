<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educational_resources', function (Blueprint $table) {

            // LONGTEXT porque los estados de GeoGebra pueden pesar bastante
            $table->longText('simulator_state')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('educational_resources', function (Blueprint $table) {
            $table->dropColumn('simulator_state');
        });
    }
};
