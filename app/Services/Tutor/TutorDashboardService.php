<?php

namespace App\Services\Tutor;

use App\Models\Exercise;
use App\Models\SessionExercise;
use Illuminate\Support\Facades\DB;

class TutorDashboardService
{
    public function getDashboardData(int $tutorId): array
    {
        //devolviendo totales de ejerciciios del tutor, reportes
        return [
            'totalExercises' => $this->getTotalExercises($tutorId),
            'approvedExercises' => $this->getExercisesByStatus($tutorId, 'Aprobado'),
            'reviewExercises' => $this->getExercisesByStatus($tutorId, 'En revisión'),
            'draftExercises' => $this->getExercisesByStatus($tutorId, 'Borrador'),
            'groupStats' => $this->getGroupStats($tutorId),
            'modulesWithHighestError' => $this->getModulesWithHighestError($tutorId),
            'exercisesWithHighestError' => $this->getExercisesWithHighestError($tutorId),
        ];
    }

    private function getTotalExercises(int $tutorId): int
    {
        //total de ejercicios
        return Exercise::where('created_by', $tutorId)->count();
    }

    private function getExercisesByStatus(int $tutorId, string $statusName): int
    {
        //obteniendo ejercicios por estado $statusName
        return Exercise::where('created_by', $tutorId)->whereHas('status', function ($query) use ($statusName) {
                $query->where('name', $statusName);
            })->count();
    }

    private function getGroupStats(int $tutorId): array
    {
        //obteniendo ejercicios ya resueltos con el total de aciertos/fallos
        $query = SessionExercise::query()
            ->whereHas('exercise', function ($query) use ($tutorId) {
                $query->where('created_by', $tutorId);
            })->whereNotNull('answered_at');
        //juntanto totales
        $totalAnswers = (clone $query)->count();
        $correctAnswers = (clone $query)->where('is_correct', true)->count();
        $incorrectAnswers = (clone $query)->where('is_correct', false)->count();
        //retornando datos
        return [
            'total_answers' => $totalAnswers,
            'correct_answers' => $correctAnswers,
            'incorrect_answers' => $incorrectAnswers,
            'accuracy_percentage' => $totalAnswers > 0
                ? round(($correctAnswers / $totalAnswers) * 100, 2)
                : 0,
            'error_percentage' => $totalAnswers > 0
                ? round(($incorrectAnswers / $totalAnswers) * 100, 2)
                : 0,
        ];
    }

    //obteniendo modulos con mas respuestas incorrectas
    private function getModulesWithHighestError(int $tutorId)
    {
        return SessionExercise::query()
            ->select(
                'topics.name as topic_name',
                DB::raw('COUNT(session_exercises.id) as total_answers'),
                DB::raw('SUM(CASE WHEN session_exercises.is_correct = 0 THEN 1 ELSE 0 END) as total_errors'),
                DB::raw('ROUND((SUM(CASE WHEN session_exercises.is_correct = 0 THEN 1 ELSE 0 END) / COUNT(session_exercises.id)) * 100, 2) as error_percentage')
            )->join('exercises', 'exercises.id', '=', 'session_exercises.exercise_id')
            ->join('subtopics', 'subtopics.id', '=', 'exercises.subtopic_id')
            ->join('topics', 'topics.id', '=', 'subtopics.topic_id')
            ->where('exercises.created_by', $tutorId)
            ->whereNotNull('session_exercises.answered_at')
            ->groupBy('topics.id', 'topics.name')
            ->orderByDesc('error_percentage')
            ->limit(5)
            ->get();
    }

    //obteniendo ejercicios con mas respuestas incorrectas
    private function getExercisesWithHighestError(int $tutorId)
    {
        return SessionExercise::query()
            ->select(
                'exercises.id',
                'exercises.statement',
                'subtopics.name as subtopic_name',
                'topics.name as topic_name',
                DB::raw('COUNT(session_exercises.id) as total_answers'),
                DB::raw('SUM(CASE WHEN session_exercises.is_correct = 0 THEN 1 ELSE 0 END) as total_errors'),
                DB::raw('ROUND((SUM(CASE WHEN session_exercises.is_correct = 0 THEN 1 ELSE 0 END) / COUNT(session_exercises.id)) * 100, 2) as error_percentage')
            )
            ->join('exercises', 'exercises.id', '=', 'session_exercises.exercise_id')
            ->join('subtopics', 'subtopics.id', '=', 'exercises.subtopic_id')
            ->join('topics', 'topics.id', '=', 'subtopics.topic_id')
            ->where('exercises.created_by', $tutorId)
            ->whereNotNull('session_exercises.answered_at')
            ->groupBy(
                'exercises.id',
                'exercises.statement',
                'subtopics.name',
                'topics.name'
            )
            ->having('total_answers', '>', 0)
            ->orderByDesc('error_percentage')
            ->limit(10)
            ->get();
    }
}
