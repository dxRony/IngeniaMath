<?php

namespace App\Services\Student\MockExams;

use App\Models\Exercise;
use App\Models\MockExam;
use App\Models\MockExamExercise;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MockExamFlowService
{
    /**
     * Verifica si el simulacro sigue activo (no completado y dentro del tiempo).
     * Si el tiempo expiró, lo finaliza automáticamente.
     *
     * @return bool true si sigue activo, false si ya está terminado
     */
    public function isExamStillActive(MockExam $exam): bool
    {
        if ($exam->is_completed) {
            return false;
        }

        if ($this->hasTimeExpired($exam)) {
            $this->finishExam($exam);
            return false;
        }

        return true;
    }

    /**
     * Devuelve true si el tiempo del simulacro ya expiró.
     */
    public function hasTimeExpired(MockExam $exam): bool
    {
        $endsAt = $exam->started_at->copy()->addMinutes($exam->config->duration_minutes);
        return now()->greaterThanOrEqualTo($endsAt);
    }

    /**
     * Devuelve los segundos restantes del simulacro.
     * 0 si ya expiró.
     */
    public function secondsRemaining(MockExam $exam): int
    {
        $endsAt = $exam->started_at->copy()->addMinutes($exam->config->duration_minutes);
        $remaining = now()->diffInSeconds($endsAt, false);
        return max(0, (int) $remaining);
    }

    /**
     * Guarda la respuesta del estudiante para una pregunta específica.
     * Calcula is_correct comparando con el patrón según el tipo de ejercicio.
     */
    public function saveAnswer(MockExam $exam, int $order, ?string $givenAnswer): void
    {
        $examExercise = MockExamExercise::with('exercise.multipleChoiceOptions', 'exercise.type')
            ->where('exam_id', $exam->id)
            ->where('order', $order)
            ->firstOrFail();

        // Si ya fue respondida, no permitir cambiar (lineal hacia adelante)
        if ($examExercise->answered_at !== null) {
            return;
        }

        $isCorrect = $this->checkAnswer($examExercise->exercise, $givenAnswer);

        // Calcular cuántos segundos tomó (desde started_at del exam,
        // o desde la pregunta anterior si la hay, lo dejamos simple por ahora)
        $secondsTaken = $exam->started_at->diffInSeconds(now());

        MockExamExercise::where('exam_id', $exam->id)
            ->where('exercise_id', $examExercise->exercise_id)
            ->update([
                'given_answer'        => $givenAnswer,
                'is_correct'          => $isCorrect,
                'answered_at'         => now(),
                'resolution_seconds'  => (int) $secondsTaken,
            ]);
    }

    /**
     * Finaliza el simulacro: calcula score y marca como completado.
     * Las preguntas no respondidas quedan como incorrectas.
     */
    public function finishExam(MockExam $exam): MockExam
    {
        return DB::transaction(function () use ($exam) {
            // Marcar las no respondidas como incorrectas
            MockExamExercise::where('exam_id', $exam->id)
                ->whereNull('answered_at')
                ->update(['is_correct' => false]);

            // Calcular score: porcentaje de aciertos
            $total = MockExamExercise::where('exam_id', $exam->id)->count();
            $correct = MockExamExercise::where('exam_id', $exam->id)
                ->where('is_correct', true)
                ->count();

            $score = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

            $exam->update([
                'ended_at'     => now(),
                'score'        => $score,
                'is_completed' => true,
            ]);

            return $exam->fresh();
        });
    }

    /**
     * Devuelve el order de la siguiente pregunta sin responder, o null si todas respondidas.
     */
    public function nextUnansweredOrder(MockExam $exam): ?int
    {
        return MockExamExercise::where('exam_id', $exam->id)
            ->whereNull('answered_at')
            ->orderBy('order')
            ->value('order');
    }

    /**
     * Compara la respuesta del estudiante con la correcta según el tipo.
     */
    private function checkAnswer(Exercise $exercise, ?string $givenAnswer): bool
    {
        if ($givenAnswer === null || trim($givenAnswer) === '') {
            return false;
        }

        $given = trim($givenAnswer);
        $typeId = $exercise->type_id;

        return match ($typeId) {
            // 1: Opción múltiple - comparar con option_text de la opción correcta
            1 => $this->checkMultipleChoice($exercise, $given),

            // 2: Verdadero/Falso - normalizar 'true'/'false' a 'Verdadero'/'Falso'
            2 => $this->checkTrueFalse($exercise, $given),

            // 3: Numérica abierta - comparación de strings normalizados
            3 => $this->normalizeNumeric($given) === $this->normalizeNumeric($exercise->correct_answer ?? ''),

            // 4: Completar espacios - JSON o string simple según el caso
            4 => $this->checkFillBlank($exercise, $given),

            default => false,
        };
    }

    private function checkMultipleChoice(Exercise $exercise, string $givenText): bool
    {
        $correctOption = $exercise->multipleChoiceOptions
            ->firstWhere('is_correct', true);

        if (!$correctOption) {
            return false;
        }

        return trim($correctOption->option_text) === $givenText;
    }

    private function checkTrueFalse(Exercise $exercise, string $given): bool
    {
        // El form envía 'true'/'false', pero correct_answer puede ser 'Verdadero'/'Falso'
        $normalizedGiven = match (strtolower($given)) {
            'true', 'verdadero' => 'verdadero',
            'false', 'falso'    => 'falso',
            default             => strtolower($given),
        };

        $normalizedCorrect = strtolower(trim($exercise->correct_answer ?? ''));

        return $normalizedGiven === $normalizedCorrect;
    }

    private function checkFillBlank(Exercise $exercise, string $given): bool
    {
        // El compañero guarda las respuestas como JSON (array indexado por posición)
        // Pero si solo hay un espacio (caso común del seeder), correct_answer es un string simple
        $correct = trim($exercise->correct_answer ?? '');

        // Intentar decodificar el given como JSON (formato array)
        $decoded = json_decode($given, true);

        if (is_array($decoded)) {
            // Concatenar todas las respuestas del array y comparar con correct_answer
            $joined = implode('|', array_map('trim', $decoded));
            return strcasecmp($joined, $correct) === 0
                || strcasecmp(trim($decoded[0] ?? ''), $correct) === 0;
        }

        // Comparación directa de strings
        return strcasecmp($given, $correct) === 0;
    }

    /**
     * Normaliza un valor numérico: elimina espacios, comas, signos.
     * Ej: " 1,234.5 " -> "1234.5"
     */
    private function normalizeNumeric(string $value): string
    {
        return trim(str_replace([',', ' '], '', $value));
    }
}
