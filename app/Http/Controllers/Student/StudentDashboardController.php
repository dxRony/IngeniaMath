<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentExercise;
use App\Models\InitialAssessment;
use App\Models\LearningPath;
use App\Models\ModuleProgress;
use App\Models\MockExam;
use App\Models\PathItem;
use App\Models\PracticeSession;
use App\Models\Topic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function index(): View
    {
        $studentId = Auth::id();

        // --- Métricas numéricas ---
        $totalExercises = AssessmentExercise::where('student_id', $studentId) // via assessment
            ->join('initial_assessments', 'assessment_exercises.assessment_id', '=', 'initial_assessments.id')
            ->where('initial_assessments.student_id', $studentId)
            ->where('assessment_exercises.is_correct', '!=', null)
            ->count()
            + DB::table('session_exercises')
            ->join('practice_sessions', 'session_exercises.session_id', '=', 'practice_sessions.id')
            ->where('practice_sessions.student_id', $studentId)
            ->count();

        $totalMockExams   = MockExam::where('student_id', $studentId)->count();
        $avgMockScore     = MockExam::where('student_id', $studentId)->avg('score');

        // Racha de días consecutivos
        $streak = $this->calculateStreak($studentId);

        // Módulos completados vs pendientes
        $moduleStatusCounts = ModuleProgress::where('student_id', $studentId)
            ->select('topic_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('topic_id')
            ->pluck('max_id');

        $latestStatuses = ModuleProgress::whereIn('id', $moduleStatusCounts)
            ->with('masteryLevel')
            ->get();

        $completedModules = $latestStatuses->filter(
            fn($m) => $m->masteryLevel->name === 'Dominado'
        )->count();

        $pendingModules = $latestStatuses->filter(
            fn($m) => $m->masteryLevel->name !== 'Dominado'
        )->count();

        // --- Radar + Comparativa + Progreso en el tiempo ---
        $latestProgress = ModuleProgress::where('student_id', $studentId)
            ->select('topic_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('topic_id')
            ->pluck('max_id');

        $moduleScores = ModuleProgress::whereIn('id', $latestProgress)
            ->with(['topic:id,name', 'masteryLevel:id,name'])
            ->get()
            ->keyBy('topic_id');

        $topics = Topic::orderBy('order')->get();

        $radarLabels  = [];
        $radarData    = [];

        $moduleAccuracyRaw = DB::query()
            ->fromSub($this->studentExercisesBySubtopicQuery($studentId), 'all_exercises')
            ->join('subtopics as s', 'all_exercises.subtopic_id', '=', 's.id')
            ->join('topics as t', 's.topic_id', '=', 't.id')
            ->select(
                't.id as topic_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN all_exercises.is_correct = 1 THEN 1 ELSE 0 END) as correct')
            )
            ->groupBy('t.id')
            ->get()
            ->keyBy('topic_id');

        $barData = [];

        foreach ($topics as $topic) {
            $radarLabels[] = $this->shortName($topic->name);
            $score = $moduleScores[$topic->id]->score ?? 0;
            $totalAttempts = (int) ($moduleAccuracyRaw[$topic->id]->total ?? 0);
            $correctAttempts = (int) ($moduleAccuracyRaw[$topic->id]->correct ?? 0);
            $accuracy = $totalAttempts > 0 ? ($correctAttempts / $totalAttempts) * 100 : 0;

            $radarData[] = round($score, 1);
            $barData[]   = round($accuracy, 1);
        }

        // Progreso en el tiempo (agrupado por semana)
        $progressOverTime = ModuleProgress::where('student_id', $studentId)
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('WEEK(created_at) as week'),
                DB::raw('AVG(score) as avg_score'),
                DB::raw('MIN(created_at) as week_start')
            )
            ->groupBy('year', 'week')
            ->orderBy('week_start')
            ->get();

        $lineLabels = $progressOverTime->map(
            fn($r) =>
            \Carbon\Carbon::parse($r->week_start)->locale('es')->isoFormat('D MMM')
        )->toArray();
        $lineData = $progressOverTime->map(fn($r) => round($r->avg_score, 1))->toArray();

        // --- Top 3 módulos con mayor tasa de error ---
        $topErrors = DB::query()
            ->fromSub($this->studentExercisesBySubtopicQuery($studentId), 'all_exercises')
            ->join('subtopics as s', 'all_exercises.subtopic_id', '=', 's.id')
            ->join('topics as t', 's.topic_id', '=', 't.id')
            ->select(
                't.id',
                't.name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN all_exercises.is_correct = 0 THEN 1 ELSE 0 END) as errors')
            )
            ->groupBy('t.id', 't.name')
            ->orderByDesc('errors')
            ->limit(3)
            ->get()
            ->map(fn($r) => [
                'name'       => $r->name,
                'error_rate' => $r->total > 0 ? round(($r->errors / $r->total) * 100) : 0,
            ]);

        // --- Heatmap (últimos 90 días) ---
        $activityDays = DB::table('practice_sessions')
            ->where('student_id', $studentId)
            ->whereNotNull('ended_at')
            ->select(DB::raw('DATE(started_at) as day'), DB::raw('COUNT(*) as sessions'))
            ->groupBy('day')
            ->pluck('sessions', 'day');

        return view('student.index', compact(
            'totalExercises',
            'totalMockExams',
            'avgMockScore',
            'streak',
            'completedModules',
            'pendingModules',
            'topics',
            'moduleScores',
            'radarLabels',
            'radarData',
            'barData',
            'lineLabels',
            'lineData',
            'topErrors',
            'activityDays'
        ));
    }

    private function calculateStreak(int $studentId): int
    {
        $days = DB::table('practice_sessions')
            ->where('student_id', $studentId)
            ->whereNotNull('ended_at')
            ->select(DB::raw('DATE(started_at) as day'))
            ->distinct()
            ->orderByDesc('day')
            ->pluck('day')
            ->map(fn($d) => \Carbon\Carbon::parse($d));

        if ($days->isEmpty()) return 0;

        $streak = 1;
        for ($i = 0; $i < $days->count() - 1; $i++) {
            if ($days[$i]->diffInDays($days[$i + 1]) === 1) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    private function shortName(string $name): string
    {
        return match ($name) {
            'Números Reales'           => 'Números Reales',
            'Fundamentos de Álgebra'   => 'Álgebra',
            'Ecuaciones e Inecuaciones' => 'Ecuac.',
            'Funciones y Gráficas'     => 'Funciones',
            'Geometría Analítica'      => 'G.Analítica.',
            'Geometría'                => 'Geom.',
            'Trigonometría'            => 'Trig.',
            default                    => $name,
        };
    }

    private function studentExercisesBySubtopicQuery(int $studentId)
    {
        $assessmentExercises = DB::table('assessment_exercises as ae')
            ->join('initial_assessments as ia', 'ae.assessment_id', '=', 'ia.id')
            ->join('exercises as e', 'ae.exercise_id', '=', 'e.id')
            ->where('ia.student_id', $studentId)
            ->whereNotNull('ae.is_correct')
            ->select('e.subtopic_id', 'ae.is_correct');

        $sessionExercises = DB::table('session_exercises as se')
            ->join('practice_sessions as ps', 'se.session_id', '=', 'ps.id')
            ->join('exercises as e', 'se.exercise_id', '=', 'e.id')
            ->where('ps.student_id', $studentId)
            ->whereNotNull('se.is_correct')
            ->select('e.subtopic_id', 'se.is_correct');

        return $assessmentExercises->unionAll($sessionExercises);
    }
}
