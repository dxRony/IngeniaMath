<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'Básico'],
            ['name' => 'Intermedio'],
            ['name' => 'Avanzado'],
            ['name' => 'Nivel Examen Real'],
        ];

        DB::table('exercise_levels')->insert($levels);
    }
}
