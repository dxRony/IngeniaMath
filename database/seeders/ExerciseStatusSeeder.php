<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Borrador'],
            ['name' => 'En revisión'],
            ['name' => 'Aprobado'],
            ['name' => 'Publicado'],
            ['name' => 'Deshabilitado'],
        ];

        DB::table('exercise_statuses')->insert($statuses);
    }
}
