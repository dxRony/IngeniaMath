<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::insert([
            [
                'name' => 'admin',
                'description' => 'System administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'moderator',
                'description' => 'Content reviewer and forum moderator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'tutor',
                'description' => 'Tutor or instructor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'student',
                'description' => 'Platform student',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
