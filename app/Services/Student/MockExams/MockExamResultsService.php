<?php

namespace App\Services\Student\MockExams;

use App\Models\MockExam;
use Illuminate\Support\Collection;

class MockExamResultsService
{
    /**
     * Genera el desglose de resultados por módulo temático.
     * Devuelve un array con: topic_name, total, correct, incorrect, percentage
     */
    public function getTopicBreakdown(MockExam $exam): Collection
    {
        $exam->load('exercises.exercise.subtopic.topic');

        return $exam->exercises
            ->groupBy(fn($ee) => $ee->exercise->subtopic->topic->id)
            ->map(function ($group) {
                $first = $group->first();
                $topic = $first->exercise->subtopic->topic;
                $total = $group->count();
                $correct = $group->where('is_correct', true)->count();

                return [
                    'topic_id'    => $topic->id,
                    'topic_name'  => $topic->name,
                    'total'       => $total,
                    'correct'     => $correct,
                    'incorrect'   => $total - $correct,
                    'percentage'  => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
                ];
            })
            ->sortBy('topic_id')
            ->values();
    }

    /**
     * Devuelve las preguntas incorrectas con su solución paso a paso.
     */
    public function getIncorrectQuestions(MockExam $exam): Collection
    {
        $exam->load([
            'exercises.exercise.subtopic.topic',
            'exercises.exercise.type',
            'exercises.exercise.steps',
            'exercises.exercise.multipleChoiceOptions',
        ]);

        return $exam->exercises
            ->where('is_correct', false)
            ->sortBy('order')
            ->values();
    }

    /**
     * Calcula la duración real del simulacro en minutos y segundos.
     */
    public function getDuration(MockExam $exam): array
    {
        if (!$exam->ended_at || !$exam->started_at) {
            return ['minutes' => 0, 'seconds' => 0, 'formatted' => '-'];
        }

        $totalSeconds = $exam->started_at->diffInSeconds($exam->ended_at);
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return [
            'minutes'   => $minutes,
            'seconds'   => $seconds,
            'formatted' => sprintf('%d min %d s', $minutes, $seconds),
        ];
    }

    /**
     * Devuelve la respuesta correcta de un ejercicio según su tipo,
     * para mostrar al estudiante en la lista de incorrectas.
     */
    public function getCorrectAnswerText(\App\Models\Exercise $exercise): string
    {
        return match ((int) $exercise->type_id) {
            // Opción múltiple: el option_text de la opción correcta
            1 => $exercise->multipleChoiceOptions
                ->firstWhere('is_correct', true)?->option_text ?? '-',

            // V/F, numérica, completar: el correct_answer directo
            default => $exercise->correct_answer ?? '-',
        };
    }
}
