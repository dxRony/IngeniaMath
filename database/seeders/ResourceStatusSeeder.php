<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = [
            ['name' => 'Pendiente de revisión'],
            ['name' => 'Aprobado'],
            ['name' => 'Rechazado'],
        ];

        DB::table('resource_statuses')->insert($status);
    }
}
