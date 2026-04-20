<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::insert([
            [
                'role_id' => 1,
                'first_name' => 'Admin',
                'last_name' => 'User',
                'name' => 'Admin User',
                'email' => 'admin@ingeniamath.com',
                'password' => Hash::make('123456'),
                'is_active' => true,
            ],
            [
                'role_id' => 2,
                'first_name' => 'Moderator',
                'last_name' => 'User',
                'name' => 'Moderator User',
                'email' => 'moderator@ingeniamath.com',
                'password' => Hash::make('123456'),
                'is_active' => true,
            ],
            [
                'role_id' => 3,
                'first_name' => 'Tutor',
                'last_name' => 'User',
                'name' => 'Tutor User',
                'email' => 'tutor@ingeniamath.com',
                'password' => Hash::make('123456'),
                'is_active' => true,
            ],
            [
                'role_id' => 4,
                'first_name' => 'Student',
                'last_name' => 'User',
                'name' => 'Student User',
                'email' => 'student@ingeniamath.com',
                'password' => Hash::make('123456'),
                'is_active' => true,
            ],
        ]);
    }
}
