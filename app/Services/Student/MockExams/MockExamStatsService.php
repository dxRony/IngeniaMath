<?php

namespace App\Services\Student\MockExams;

use App\Models\MockExam;
use App\Models\Topic;
use Illuminate\Support\Collection;

class MockExamStatsService
{
    /**
     * Devuelve el listado de simulacros completados del estudiante,
     * ordenados del más reciente al más antiguo.
     */
    public function getStudentHistory(int $studentId): Collection
    {
        return MockExam::with('config')
            ->where('student_id', $studentId)
            ->where('is_completed', true)
            ->orderBy('started_at', 'desc')
            ->get();
    }

    /**
     * Devuelve los datos para la gráfica de evolución del puntaje total.
     * Formato: [['date' => '...', 'score' => 75.5, 'min' => 61], ...]
     * Ordenados cronológicamente (del más antiguo al más reciente).
     */
    public function getEvolutionData(int $studentId): array
    {
        $exams = MockExam::with('config')
            ->where('student_id', $studentId)
            ->where('is_completed', true)
            ->orderBy('started_at', 'asc')
            ->get();

        return $exams->map(fn($exam) => [
            'date'  => $exam->started_at->format('d/m/Y H:i'),
            'score' => (float) $exam->score,
            'min'   => (float) $exam->config->min_passing_score,
        ])->toArray();
    }

    /**
     * Calcula el topic con mayor mejora usando regresión lineal por topic.
     * Necesita al menos 2 simulacros para detectar tendencia.
     *
     */
    public function getMostImprovedTopic(int $studentId): ?array
    {
        $byTopic = $this->getScoresByTopic($studentId);

        if ($byTopic->isEmpty()) {
            return null;
        }

        // Necesitamos al menos 2 puntos por topic para calcular la pendiente
        $candidates = $byTopic->filter(fn($scores) => count($scores) >= 2);

        if ($candidates->isEmpty()) {
            return null;
        }

        $bestTopic = null;
        $bestSlope = -INF;

        foreach ($candidates as $topicData) {
            $slope = $this->calculateSlope($topicData['scores']);

            if ($slope > $bestSlope) {
                $bestSlope = $slope;
                $bestTopic = [
                    'topic_id'    => $topicData['topic_id'],
                    'topic_name'  => $topicData['topic_name'],
                    'slope'       => round($slope, 2),
                    'first_score' => round(reset($topicData['scores']), 1),
                    'last_score'  => round(end($topicData['scores']), 1),
                    'samples'     => count($topicData['scores']),
                ];
            }
        }

        // Si la mejor pendiente es negativa o cero, no hay mejora real
        if ($bestTopic && $bestTopic['slope'] <= 0) {
            return null;
        }

        return $bestTopic;
    }

    /**
     * Calcula el topic con menor promedio histórico de aciertos.
     *
     */
    public function getWeakestTopic(int $studentId): ?array
    {
        // Suma de correctas y totales por topic, en TODOS los simulacros completados
        $totals = \DB::table('mock_exam_exercises as mee')
            ->join('mock_exams as me', 'mee.exam_id', '=', 'me.id')
            ->join('exercises as ex', 'mee.exercise_id', '=', 'ex.id')
            ->join('subtopics as sub', 'ex.subtopic_id', '=', 'sub.id')
            ->join('topics as t', 'sub.topic_id', '=', 't.id')
            ->where('me.student_id', $studentId)
            ->where('me.is_completed', true)
            ->whereNotNull('mee.answered_at')
            ->selectRaw('t.id as topic_id, t.name as topic_name,
                        count(mee.exercise_id) as total,
                        sum(case when mee.is_correct = 1 then 1 else 0 end) as correct')
            ->groupBy('t.id', 't.name')
            ->get();

        if ($totals->isEmpty()) {
            return null;
        }

        $worst = $totals->map(function ($row) {
            $percentage = $row->total > 0 ? ($row->correct / $row->total) * 100 : 0;
            return [
                'topic_id'        => $row->topic_id,
                'topic_name'      => $row->topic_name,
                'percentage'      => round($percentage, 1),
                'total_attempts'  => (int) $row->total,
                'total_correct'   => (int) $row->correct,
            ];
        })->sortBy('percentage')->first();

        return $worst;
    }

    /**
     * Estadística general del estudiante.
     */
    public function getGeneralStats(int $studentId): array
    {
        $exams = MockExam::where('student_id', $studentId)
            ->where('is_completed', true)
            ->get();

        if ($exams->isEmpty()) {
            return [
                'total_exams'   => 0,
                'avg_score'     => 0,
                'best_score'    => 0,
                'passed_count'  => 0,
            ];
        }

        $passed = $exams->filter(fn($e) => $e->score >= $e->config->min_passing_score)->count();

        return [
            'total_exams'  => $exams->count(),
            'avg_score'    => round($exams->avg('score'), 1),
            'best_score'   => round($exams->max('score'), 1),
            'passed_count' => $passed,
        ];
    }

    /**
     * Devuelve los puntajes por topic a lo largo del tiempo.
     * Para cada topic, una serie de scores ordenados cronológicamente.
     *
     */
    private function getScoresByTopic(int $studentId): Collection
    {
        $rawData = \DB::table('mock_exam_exercises as mee')
            ->join('mock_exams as me', 'mee.exam_id', '=', 'me.id')
            ->join('exercises as ex', 'mee.exercise_id', '=', 'ex.id')
            ->join('subtopics as sub', 'ex.subtopic_id', '=', 'sub.id')
            ->join('topics as t', 'sub.topic_id', '=', 't.id')
            ->where('me.student_id', $studentId)
            ->where('me.is_completed', true)
            ->selectRaw('me.id as exam_id, me.started_at,
                        t.id as topic_id, t.name as topic_name,
                        count(mee.exercise_id) as total,
                        sum(case when mee.is_correct = 1 then 1 else 0 end) as correct')
            ->groupBy('me.id', 'me.started_at', 't.id', 't.name')
            ->orderBy('me.started_at', 'asc')
            ->get();

        // Agrupar por topic, generando un array de scores ordenados cronológicamente
        return $rawData->groupBy('topic_id')->map(function ($rows) {
            $first = $rows->first();
            return [
                'topic_id'   => $first->topic_id,
                'topic_name' => $first->topic_name,
                'scores'     => $rows->map(fn($r) => $r->total > 0
                    ? ($r->correct / $r->total) * 100
                    : 0
                )->values()->toArray(),
            ];
        });
    }

    /**
     * Calcula la pendiente de una recta de regresión lineal.
     * Recibe un array de y-values; los x-values son su índice (0, 1, 2, ...)
     */
    private function calculateSlope(array $yValues): float
    {
        $n = count($yValues);

        if ($n < 2) {
            return 0;
        }

        $xValues = range(0, $n - 1);

        $sumX  = array_sum($xValues);
        $sumY  = array_sum($yValues);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $xValues[$i] * $yValues[$i];
            $sumX2 += $xValues[$i] ** 2;
        }

        $denominator = ($n * $sumX2) - ($sumX ** 2);

        if ($denominator == 0) {
            return 0;
        }

        return (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
    }
}
