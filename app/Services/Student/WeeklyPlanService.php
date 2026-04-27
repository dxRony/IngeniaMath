<?php

namespace App\Services\Student;

use App\Models\Exercise;
use App\Models\LearningPath;
use App\Models\PathItem;
use App\Models\WeeklyPlan;
use App\Models\ExerciseStatus;
use Illuminate\Support\Facades\DB;

class WeeklyPlanService
{
    private const FALLBACK_MINUTES = 5;
    private const DAYS_PER_WEEK    = 7;

    public function generate(int $studentId, float $availableHours): WeeklyPlan
    {
        // Desactivar plan anterior si existe
        $activePath = LearningPath::where('student_id', $studentId)
            ->where('is_active', true)
            ->firstOrFail();

        WeeklyPlan::where('path_id', $activePath->id)->delete();

        // Crear nuevo plan
        $plan = WeeklyPlan::create([
            'path_id'         => $activePath->id,
            'week_start'      => now()->startOfWeek()->toDateString(),
            'available_hours' => $availableHours,
        ]);

        // Obtener subtemas pendientes en orden de ruta
        $pendingItems = PathItem::where('path_id', $activePath->id)
            ->where('is_completed', false)
            ->orderBy('order')
            ->get();

        if ($pendingItems->isEmpty()) {
            return $plan;
        }

        // Calcular minutos por día
        $totalMinutes   = $availableHours * 60;
        $minutesPerDay  = $totalMinutes / self::DAYS_PER_WEEK;

        // Precalcular avg_minutes por subtema
        $publishedStatusId = ExerciseStatus::where('name', 'Aprobado')->value('id');

        $avgMinutesBySubtopic = Exercise::where('status_id', $publishedStatusId)
            ->whereIn('subtopic_id', $pendingItems->pluck('subtopic_id'))
            ->groupBy('subtopic_id')
            ->select('subtopic_id', DB::raw('AVG(estimated_minutes) as avg_minutes'))
            ->pluck('avg_minutes', 'subtopic_id');

        // Distribuir subtemas en días
        $rows            = [];
        $subtopicQueue   = $pendingItems->values();
        $subtopicIndex   = 0;

        for ($day = 1; $day <= self::DAYS_PER_WEEK; $day++) {
            $remainingMinutes = $minutesPerDay;

            while ($subtopicIndex < $subtopicQueue->count() && $remainingMinutes > 0) {
                $item      = $subtopicQueue[$subtopicIndex];
                $avgMins   = $avgMinutesBySubtopic[$item->subtopic_id]
                    ?? self::FALLBACK_MINUTES;

                // Verificar si cabe al menos 1 ejercicio
                if ($avgMins > $remainingMinutes) {
                    break; // No cabe en este día, pasar al siguiente
                }

                $recommended      = (int) floor($remainingMinutes / $avgMins);
                $estimatedMinutes = (int) round($recommended * $avgMins);

                $rows[] = [
                    'weekly_plan_id'         => $plan->id,
                    'weekday'                => $day,
                    'subtopic_id'            => $item->subtopic_id,
                    'recommended_exercises'  => $recommended,
                    'estimated_minutes'      => $estimatedMinutes,
                ];

                $remainingMinutes -= $estimatedMinutes;
                $subtopicIndex++;
            }
        }

        if (!empty($rows)) {
            DB::table('plan_days')->insert($rows);
        }

        return $plan;
    }
}
