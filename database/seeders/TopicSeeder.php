<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topics = [
            ['name' => 'Números Reales', 'description' => 'Propiedades y operaciones con números reales, intervalos, desigualdades y valor absoluto.', 'order' => 1],
            ['name' => 'Fundamentos de Álgebra', 'description' => 'Expresiones algebraicas, polinomios, factorización y operaciones fundamentales con términos algebraicos.', 'order' => 2],
            ['name' => 'Ecuaciones e Inecuaciones', 'description' => 'Resolución de ecuaciones lineales y cuadráticas, sistemas de ecuaciones e inecuaciones.', 'order' => 3],
            ['name' => 'Funciones y Gráficas', 'description' => 'Dominio, rango, operaciones con funciones, gráficas y análisis de comportamiento de funciones.', 'order' => 4],
            ['name' => 'Geometría Analítica', 'description' => 'Puntos, rectas, distancia, pendiente, circunferencias y cónicas en el plano cartesiano.', 'order' => 5],
            ['name' => 'Geometría', 'description' => 'Figuras geométricas, ángulos, perímetros, áreas, volúmenes y propiedades de triángulos.', 'order' => 6],
            ['name' => 'Trigonometría', 'description' => 'Funciones trigonométricas, identidades, ecuaciones trigonométricas y aplicaciones en triángulos.', 'order' => 7],
        ];

        DB::table('topics')->insert($topics);
    }
}
