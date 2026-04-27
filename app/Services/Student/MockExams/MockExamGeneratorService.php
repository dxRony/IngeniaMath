<?php

namespace App\Services\Student\MockExams;

use App\Models\Exercise;
use App\Models\ExamConfig;
use App\Models\MockExam;
use App\Models\MockExamExercise;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MockExamGeneratorService
{
    /**
     * Inicia un nuevo simulacro para el estudiante autenticado.
     * Si tiene uno en curso, lo finaliza automáticamente antes.
     *
     */
    public function startNewMockExam(): MockExam
    {
        $studentId = Auth::id();

        // Cerrar cualquier simulacro abandonado del estudiante
        $this->closeAbandonedExams($studentId);

        //Verificar que existe una config activa
        $config = ExamConfig::with('topicDistributions')
            ->where('is_active', true)
            ->first();

        if (!$config) {
            throw new \RuntimeException('No hay una configuración de simulacro activa. Contacta al administrador.');
        }

        if ($config->topicDistributions->isEmpty()) {
            throw new \RuntimeException('La configuración activa no tiene distribución temática definida.');
        }

        //Seleccionar aleatoriamente los ejercicios respetando la distribución
        $selectedExerciseIds = $this->selectExercisesByDistribution($config);

        if ($selectedExerciseIds->count() !== $config->total_questions) {
            throw new \RuntimeException(
                "No hay suficientes ejercicios disponibles. Se necesitan {$config->total_questions} pero solo se obtuvieron {$selectedExerciseIds->count()}."
            );
        }

        // Crear el simulacro y sus preguntas dentro de una transacción
        return DB::transaction(function () use ($studentId, $config, $selectedExerciseIds) {
            $mockExam = MockExam::create([
                'student_id'   => $studentId,
                'config_id'    => $config->id,
                'started_at'   => now(),
                'is_completed' => false,
            ]);

            // Mezclamos todas las preguntas (no agrupadas por topic) para que el estudiante
            // no vea todas las de un módulo seguidas
            $shuffled = $selectedExerciseIds->shuffle();

            foreach ($shuffled as $index => $exerciseId) {
                MockExamExercise::create([
                    'exam_id'     => $mockExam->id,
                    'exercise_id' => $exerciseId,
                    'order'       => $index + 1,
                ]);
            }

            return $mockExam->load('exercises');
        });
    }

    /**
     * Finaliza simulacros abandonados (no completados) del estudiante.
     * Las preguntas sin responder se cuentan como incorrectas.
     */
    public function closeAbandonedExams(int $studentId): void
    {
        $abandoned = MockExam::where('student_id', $studentId)
            ->where('is_completed', false)
            ->get();

        foreach ($abandoned as $exam) {
            DB::transaction(function () use ($exam) {
                // Las preguntas no respondidas quedan como incorrectas (is_correct = false)
                MockExamExercise::where('exam_id', $exam->id)
                    ->whereNull('answered_at')
                    ->update(['is_correct' => false]);

                // Calcular score final con lo que respondió
                $totalQuestions = MockExamExercise::where('exam_id', $exam->id)->count();
                $correctAnswers = MockExamExercise::where('exam_id', $exam->id)
                    ->where('is_correct', true)
                    ->count();

                $score = $totalQuestions > 0
                    ? round(($correctAnswers / $totalQuestions) * 100, 2)
                    : 0;

                $exam->update([
                    'ended_at'     => now(),
                    'score'        => $score,
                    'is_completed' => true,
                ]);
            });
        }
    }

    /**
     * Selecciona aleatoriamente los IDs de ejercicios respetando
     * la distribución temática de la configuración.
     *
     */
    private function selectExercisesByDistribution(ExamConfig $config): \Illuminate\Support\Collection
    {
        $allIds = collect();

        foreach ($config->topicDistributions as $distribution) {
            $topicId = $distribution->topic_id;
            $count = $distribution->question_count;

            $ids = Exercise::whereIn('status_id', [3, 4]) // Aprobado o Publicado
            ->whereHas('subtopic', function ($query) use ($topicId) {
                $query->where('topic_id', $topicId);
            })
                ->inRandomOrder()
                ->limit($count)
                ->pluck('id');

            $allIds = $allIds->merge($ids);
        }

        return $allIds;
    }
}
