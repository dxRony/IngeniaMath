<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasteryLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'Dominado', 'min_threshold' => 80, 'max_threshold' => 100],
            ['name' => 'En desarrollo', 'min_threshold' => 50, 'max_threshold' => 79],
            ['name' => 'Deficiente', 'min_threshold' => 0, 'max_threshold' => 49],
        ];

        DB::table('mastery_levels')->insert($levels);
    }
}
