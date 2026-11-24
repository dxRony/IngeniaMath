<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubtopicPrerequisiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SubtopicPrerequisiteSeeder
        $prerequisites = [
            // Álgebra
            ['subtopic_id' => 7,  'prerequisite_id' => 6],   // Factorización → Productos notables
            ['subtopic_id' => 8,  'prerequisite_id' => 7],   // Fracciones alg. → Factorización
            ['subtopic_id' => 9,  'prerequisite_id' => 8],   // Teorema residuo → Fracciones alg.

            // Ecuaciones
            ['subtopic_id' => 11, 'prerequisite_id' => 6],   // Ec. cuadráticas → Productos notables
            ['subtopic_id' => 12, 'prerequisite_id' => 11],  // Ec. reducibles → Ec. cuadráticas
            ['subtopic_id' => 13, 'prerequisite_id' => 10],  // Inecuaciones → Ec. primer grado

            // Funciones
            ['subtopic_id' => 16, 'prerequisite_id' => 11],  // Función cuadrática → Ec. cuadráticas
            ['subtopic_id' => 17, 'prerequisite_id' => 15],  // Composición → Función lineal

            // Geometría Analítica
            ['subtopic_id' => 18, 'prerequisite_id' => 15],  // Ec. recta → Función lineal
            ['subtopic_id' => 19, 'prerequisite_id' => 18],  // Distancia y p. medio → Ec. recta
            ['subtopic_id' => 20, 'prerequisite_id' => 18],  // Circunferencia → Ec. recta
            ['subtopic_id' => 21, 'prerequisite_id' => 16],  // Parábola → Función cuadrática

            // Geometría
            ['subtopic_id' => 23, 'prerequisite_id' => 22],  // Semejanza → Pitágoras
            ['subtopic_id' => 25, 'prerequisite_id' => 24],  // Volúmenes → Áreas y perímetros

            // Trigonometría
            ['subtopic_id' => 28, 'prerequisite_id' => 27],  // Resolución triángulos → Func. trig.

            // Números Reales
            ['subtopic_id' => 2,  'prerequisite_id' => 5],   // Racionalización → MCD y MCM
        ];

        DB::table('subtopic_prerequisites')->insert($prerequisites);
    }
}
