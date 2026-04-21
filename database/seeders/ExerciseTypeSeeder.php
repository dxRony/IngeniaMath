<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Opción múltiple'],
            ['name' => 'Verdadero/Falso'],
            ['name' => 'Respuesta numérica abierta'],
            ['name' => 'Completar espacios'],
        ];

        DB::table('exercise_types')->insert($types);
    }
}
