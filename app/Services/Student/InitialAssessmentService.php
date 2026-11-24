<?php

namespace App\Services\Student;

use App\Models\InitialAssessment;
use App\Models\Exercise;
use App\Models\ExerciseStatus;
use App\Models\Topic;
use App\Models\ExerciseLevel;
use App\Models\MasteryLevel;
use App\Models\AssessmentModuleResult;
use App\Models\ModuleProgress;
use Illuminate\Support\Facades\DB;

class InitialAssessmentService
{
    private const EXERCISES_PER_TOPIC = 4;

    public function assignExercises(InitialAssessment $assessment): void
    {
        $topics = Topic::orderBy('order')->get();
        $levels = ExerciseLevel::orderBy('id')->get();
        $order  = 1;
        $rows   = [];

        foreach ($topics as $topic) {
            $selectedIds = [];

            // Intentar seleccionar 1 ejercicio por nivel
            foreach ($levels as $level) {
                $exercise = Exercise::where('status_id', $this->publishedStatusId())
                    ->where('level_id', $level->id)
                    ->whereHas('subtopic', fn($q) => $q->where('topic_id', $topic->id))
                    ->whereNotIn('id', $selectedIds)
                    ->inRandomOrder()
                    ->first();

                if ($exercise) {
                    $selectedIds[] = $exercise->id;
                    $rows[] = [
                        'assessment_id' => $assessment->id,
                        'exercise_id'   => $exercise->id,
                        'order'         => $order++,
                        'given_answer'  => null,
                        'is_correct'    => null,
                    ];
                }
            }

            // Fallback: si faltan ejercicios para este módulo, completar con cualquier nivel
            $missing = self::EXERCISES_PER_TOPIC - count($selectedIds);
            if ($missing > 0) {
                $fallbacks = Exercise::where('status_id', $this->publishedStatusId())
                    ->whereHas('subtopic', fn($q) => $q->where('topic_id', $topic->id))
                    ->whereNotIn('id', $selectedIds)
                    ->inRandomOrder()
                    ->limit($missing)
                    ->get();

                foreach ($fallbacks as $exercise) {
                    $rows[] = [
                        'assessment_id' => $assessment->id,
                        'exercise_id'   => $exercise->id,
                        'order'         => $order++,
                        'given_answer'  => null,
                        'is_correct'    => null,
                    ];
                }
            }
        }

        $assessment->assessmentExercises()->createMany($rows);
    }

    public function grade(InitialAssessment $assessment, array $answers): void
    {
        $assessmentExercises = $assessment->assessmentExercises()
            ->with(['exercise.type', 'exercise.level', 'exercise.subtopic.topic'])
            ->get();

        // 1. Calificar cada ejercicio
        foreach ($assessmentExercises as $item) {
            $given     = $answers[$item->exercise_id] ?? null;
            $isCorrect = $this->checkAnswer($item->exercise, $given);

            DB::table('assessment_exercises')
                ->where('assessment_id', $item->assessment_id)
                ->where('exercise_id', $item->exercise_id)
                ->update([
                    'given_answer' => is_array($given) ? implode('|', $given) : $given,
                    'is_correct'   => $isCorrect,
                ]);
        }

        // 2. Refrescar para tener los resultados actualizados
        $assessmentExercises = $assessment->assessmentExercises()
            ->with(['exercise.level', 'exercise.subtopic.topic'])
            ->get();

        // 3. Calcular puntaje por módulo con pesos por nivel
        $byTopic = $assessmentExercises->groupBy(
            fn($item) => $item->exercise->subtopic->topic->id
        );

        foreach ($byTopic as $topicId => $items) {
            $totalWeight   = 0;
            $correctWeight = 0;

            foreach ($items as $item) {
                $weight      = $item->exercise->level->weight;
                $totalWeight += $weight;

                if ($item->is_correct) {
                    $correctWeight += $weight;
                }
            }

            $score = $totalWeight > 0
                ? round(($correctWeight / $totalWeight) * 100, 2)
                : 0;

            $masteryLevelId = $this->resolveMasteryLevel($score);

            AssessmentModuleResult::create([
                'assessment_id'    => $assessment->id,
                'topic_id'         => $topicId,
                'mastery_level_id' => $masteryLevelId,
                'score'            => $score,
            ]);

            ModuleProgress::create([
                'student_id'       => $assessment->student_id,
                'topic_id'         => $topicId,
                'mastery_level_id' => $masteryLevelId,
                'score'            => $score,
            ]);
        }

        // dd($assessmentExercises->toArray());
        // 4. Marcar diagnóstico como completado
        $assessment->update(['is_completed' => true]);
    }

    private function checkAnswer(Exercise $exercise, mixed $given): bool
    {
        if ($given === null) return false;

        $typeName = $exercise->type->name;
        $correct  = trim($exercise->correct_answer);

        return match ($typeName) {
            'Opción múltiple'          => trim($given) === $correct,
            'Verdadero/Falso'          => $this->mapAnswerGivenVF($given) === $correct,
            'Respuesta numérica abierta' => $this->compareNumeric($given, $correct),
            'Completar espacios'       => $this->compareFillBlank($given, $correct),
            default                    => false,
        };
    }

    private function mapAnswerGivenVF(mixed $given): string
    {
        return $given === 'true' ? 'Verdadero' : 'Falso';
    }

    private function compareNumeric(mixed $given, string $correct): bool
    {
        // Normalizar coma decimal y convertir a float
        $givenFloat   = (float) str_replace(',', '.', trim($given));
        $correctFloat = (float) str_replace(',', '.', $correct);

        // Tolerancia mínima para evitar problemas de precisión flotante
        return abs($givenFloat - $correctFloat) < 0.0001;
    }

    private function compareFillBlank(mixed $given, string $correct): bool
    {
        // given llega como array ["10", "11", "12"] desde el form
        // correct está guardado como "10|11|12"
        $correctParts = array_map('trim', explode('|', $correct));
        $givenParts   = is_array($given)
            ? array_map('trim', $given)
            : array_map('trim', explode('|', $given));

        if (count($givenParts) !== count($correctParts)) return false;

        foreach ($correctParts as $i => $part) {
            if (($givenParts[$i] ?? '') !== $part) return false;
        }

        return true;
    }

    private function resolveMasteryLevel(float $score): int
    {
        $level = MasteryLevel::where('min_threshold', '<=', $score)
            ->where('max_threshold', '>=', $score)
            ->first();

        return $level?->id ?? MasteryLevel::orderBy('min_threshold')->first()->id;
    }

    private static function publishedStatusId(): int
    {
        return ExerciseStatus::where('name', 'Aprobado')->value('id');
    }
}
