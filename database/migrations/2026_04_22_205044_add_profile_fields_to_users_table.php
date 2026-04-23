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
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_url')->nullable()->after('is_active');
            $table->string('phone_number', 30)->nullable()->after('profile_photo_url');
            $table->string('gender', 30)->nullable()->after('phone_number');
            $table->date('birth_date')->nullable()->after('gender');
            $table->text('bio')->nullable()->after('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_photo_url',
                'phone_number',
                'gender',
                'birth_date',
                'bio',
            ]);
        });
    }
};
