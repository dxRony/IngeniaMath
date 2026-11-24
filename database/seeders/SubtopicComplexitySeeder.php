<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubtopicComplexitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $complexities = [
            ['name' => 'Básico'],
            ['name' => 'Intermedio'],
            ['name' => 'Avanzado'],
        ];

        DB::table('subtopic_complexities')->insert($complexities);
    }
}
