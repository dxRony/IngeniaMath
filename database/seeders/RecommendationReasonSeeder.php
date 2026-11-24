<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecommendationReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recommendations = [
            ['name' => 'Menores aciertos'],
            ['name' => 'Progreso nivel'],
            ['name' => 'Refuerzo fundamentos'],
            ['name' => 'Evitar repeticion'],
        ];

        DB::table('recommendation_reasons')->insert($recommendations);
    }
}
