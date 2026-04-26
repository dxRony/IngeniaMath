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
            ['name' => 'Básico', 'weight' => 1],
            ['name' => 'Intermedio', 'weight' => 2],
            ['name' => 'Avanzado', 'weight' => 3],
            ['name' => 'Nivel Examen Real', 'weight' => 4],
        ];

        DB::table('exercise_levels')->upsert($levels, ['name'], ['weight']);
    }
}
