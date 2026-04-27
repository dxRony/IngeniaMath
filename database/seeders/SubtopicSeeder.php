<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubtopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subtopics = [
            // 1. Números Reales
            [
                'topic_id' => 1,
                'name' => 'Valor absoluto',
                'description' => 'Propiedades, ecuaciones e inecuaciones con valor absoluto.',
                'order' => 1,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 1,
                'name' => 'Racionalización',
                'description' => 'Técnicas de racionalización de denominadores con radicales.',
                'order' => 2,
                'complexity_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 1,
                'name' => 'Proporcionalidad',
                'description' => 'Razones, proporciones y variación directa e inversa.',
                'order' => 3,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 1,
                'name' => 'Porcentajes',
                'description' => 'Cálculo e interpretación de porcentajes en distintos contextos.',
                'order' => 4,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 1,
                'name' => 'MCD y MCM',
                'description' => 'Máximo común divisor y mínimo común múltiplo.',
                'order' => 5,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 2. Fundamentos de Álgebra
            [
                'topic_id' => 2,
                'name' => 'Productos notables',
                'description' => 'Identidades algebraicas básicas y su aplicación.',
                'order' => 1,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 2,
                'name' => 'Factorización',
                'description' => 'Métodos de factorización de expresiones algebraicas.',
                'order' => 2,
                'complexity_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 2,
                'name' => 'Fracciones algebraicas',
                'description' => 'Operaciones y simplificación de fracciones algebraicas.',
                'order' => 3,
                'complexity_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 2,
                'name' => 'Teorema del residuo',
                'description' => 'Aplicación del teorema del residuo en polinomios.',
                'order' => 4,
                'complexity_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 3. Ecuaciones e Inecuaciones
            [
                'topic_id' => 3,
                'name' => 'Ecuaciones de primer grado',
                'description' => 'Resolución de ecuaciones lineales en una variable.',
                'order' => 1,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 3,
                'name' => 'Ecuaciones cuadráticas',
                'description' => 'Resolución por factorización, fórmula general y completación de cuadrados.',
                'order' => 2,
                'complexity_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 3,
                'name' => 'Ecuaciones reducibles a cuadráticas',
                'description' => 'Transformación y resolución de ecuaciones reducibles.',
                'order' => 3,
                'complexity_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 3,
                'name' => 'Inecuaciones',
                'description' => 'Resolución e interpretación de inecuaciones algebraicas.',
                'order' => 4,
                'complexity_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 4. Funciones y Gráficas
            [
                'topic_id' => 4,
                'name' => 'Dominio y rango',
                'description' => 'Determinación del dominio y rango de funciones.',
                'order' => 1,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 4,
                'name' => 'Función lineal',
                'description' => 'Pendiente, intersecciones y representación gráfica.',
                'order' => 2,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 4,
                'name' => 'Función cuadrática',
                'description' => 'Análisis y gráfica de parábolas.',
                'order' => 3,
                'complexity_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 4,
                'name' => 'Composición de funciones',
                'description' => 'Operación y evaluación de funciones compuestas.',
                'order' => 4,
                'complexity_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 5. Geometría Analítica
            [
                'topic_id' => 5,
                'name' => 'Ecuación de la recta',
                'description' => 'Formas de la ecuación de la recta y su interpretación.',
                'order' => 1,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 5,
                'name' => 'Distancia y punto medio',
                'description' => 'Cálculo de distancia entre puntos y punto medio.',
                'order' => 2,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 5,
                'name' => 'Circunferencia',
                'description' => 'Ecuación ordinaria y general de la circunferencia.',
                'order' => 3,
                'complexity_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 5,
                'name' => 'Parábola',
                'description' => 'Elementos y ecuaciones de la parábola.',
                'order' => 4,
                'complexity_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 6. Geometría
            [
                'topic_id' => 6,
                'name' => 'Teorema de Pitágoras',
                'description' => 'Aplicaciones del teorema de Pitágoras en triángulos rectángulos.',
                'order' => 1,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 6,
                'name' => 'Semejanza de triángulos',
                'description' => 'Criterios de semejanza y aplicaciones.',
                'order' => 2,
                'complexity_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 6,
                'name' => 'Áreas y perímetros',
                'description' => 'Cálculo de áreas y perímetros de figuras planas.',
                'order' => 3,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 6,
                'name' => 'Volúmenes',
                'description' => 'Cálculo de volúmenes de sólidos geométricos.',
                'order' => 4,
                'complexity_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 7. Trigonometría
            [
                'topic_id' => 7,
                'name' => 'Conversión grados-radianes',
                'description' => 'Conversión entre grados y radianes.',
                'order' => 1,
                'complexity_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 7,
                'name' => 'Funciones trigonométricas',
                'description' => 'Definición y uso de seno, coseno, tangente y otras funciones.',
                'order' => 2,
                'complexity_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'topic_id' => 7,
                'name' => 'Resolución de triángulos',
                'description' => 'Aplicación de razones trigonométricas y leyes del seno y coseno.',
                'order' => 3,
                'complexity_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('subtopics')->insert($subtopics);
    }
}
