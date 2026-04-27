<?php

namespace App\Services\Admin;

use App\Models\Exercise;
use App\Models\MockExam;
use App\Models\SessionExercise;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function getDashboardData(): array
    {
        //mandando informacion detallada para el dashboard
        return [
            'totalUsers' => User::count(),
            'totalSolvedExercises' => SessionExercise::whereNotNull('answered_at')->count(),
            'totalMockExams' => class_exists(MockExam::class)
                ? MockExam::count()
                : 0,
            'usersByRole' => $this->getUsersByRole(),
            'exerciseBankStatus' => $this->getExerciseBankStatus(),
            'recentSolvedExercises' => $this->getRecentSolvedExercises(),
        ];
    }

    //obteniendo usuarios por rol
    private function getUsersByRole()
    {
        return User::query()
            ->select(
                'roles.name as role_name',
                DB::raw('COUNT(users.id) as total')
            )
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->groupBy('roles.id', 'roles.name')
            ->orderByDesc('total')
            ->get();
    }

    //obteniendo el estado de los ejercicios en el banco
    private function getExerciseBankStatus()
    {
        return Exercise::query()
            ->select(
                'exercise_statuses.name as status_name',
                DB::raw('COUNT(exercises.id) as total')
            )
            ->join('exercise_statuses', 'exercise_statuses.id', '=', 'exercises.status_id')
            ->groupBy('exercise_statuses.id', 'exercise_statuses.name')
            ->orderBy('exercise_statuses.id')
            ->get();
    }

    //obteniendo los ejercicios recientemente resueltos (8)
    private function getRecentSolvedExercises()
    {
        return SessionExercise::query()
            ->with([
                'session.student',
                'exercise.subtopic.topic',
            ])
            ->whereNotNull('answered_at')
            ->latest('answered_at')
            ->limit(8)
            ->get();
    }
}
