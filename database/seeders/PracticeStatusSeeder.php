<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PracticeStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'En curso'],
            ['name' => 'Finalizada'],
            ['name' => 'Abandonada'],
        ];

        DB::table('practice_statuses')->insert($statuses);
    }
}
