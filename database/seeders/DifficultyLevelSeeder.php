<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DifficultyLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modes = [
            ['name' => 'Fácil'],
            ['name' => 'Intermedio'],
            ['name' => 'Difícil'],
        ];

        DB::table('difficulty_levels')->insert($modes);
    }
}
