<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Videos'],
            ['name' => 'Documentos PDF'],
            ['name' => 'Flashcards'],
            ['name' => 'Simulador Visual'],
        ];

        DB::table('resource_types')->insert($types);
    }
}
