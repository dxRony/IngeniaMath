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
                'name' => 'Admin',
                'lastname' => 'User',
                'email' => 'admin@ingeniamath.com',
                'password' => Hash::make('123456'),
                'active' => true,
            ],
            [
                'role_id' => 2,
                'name' => 'Moderator',
                'lastname' => 'User',
                'email' => 'moderator@ingeniamath.com',
                'password' => Hash::make('123456'),
                'active' => true,
            ],
            [
                'role_id' => 3,
                'name' => 'Tutor',
                'lastname' => 'User',
                'email' => 'tutor@ingeniamath.com',
                'password' => Hash::make('123456'),
                'active' => true,
            ],
            [
                'role_id' => 4,
                'name' => 'Student',
                'lastname' => 'User',
                'email' => 'student@ingeniamath.com',
                'password' => Hash::make('123456'),
                'active' => true,
            ],
        ]);
    }
}
