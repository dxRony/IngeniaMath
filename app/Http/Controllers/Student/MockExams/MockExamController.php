<?php

namespace App\Http\Controllers\Student\MockExams;

use App\Http\Controllers\Controller;
use App\Models\ExamConfig;
use App\Models\MockExam;
use App\Services\Student\MockExams\MockExamFlowService;
use App\Services\Student\MockExams\MockExamGeneratorService;
use App\Services\Student\MockExams\MockExamResultsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MockExamController extends Controller
{
    public function __construct(
        private MockExamGeneratorService $generatorService,
        private MockExamFlowService $flowService,
        private MockExamResultsService $resultsService
    ) {}

    public function startForm(): View
    {
        $activeConfig = ExamConfig::with('topicDistributions.topic')
            ->where('is_active', true)
            ->first();

        return view('student.mock-exams.start', compact('activeConfig'));
    }

    public function start(): RedirectResponse
    {
        try {
            $mockExam = $this->generatorService->startNewMockExam();
        } catch (\RuntimeException $e) {
            return redirect()->route('student.mock-exams.start-form')
                ->withErrors(['mock_exam' => $e->getMessage()]);
        }

        return redirect()->route('student.mock-exams.take', [
            'id'    => $mockExam->id,
            'order' => 1,
        ]);
    }

    public function take(int $id, int $order): RedirectResponse|View
    {
        $exam = $this->loadOwnedExam($id);

        if (!$this->flowService->isExamStillActive($exam)) {
            return redirect()->route('student.mock-exams.results', $exam->id);
        }

        $nextUnanswered = $this->flowService->nextUnansweredOrder($exam);

        if ($nextUnanswered === null) {
            $this->flowService->finishExam($exam);
            return redirect()->route('student.mock-exams.results', $exam->id);
        }

        if ($order !== $nextUnanswered) {
            return redirect()->route('student.mock-exams.take', [
                'id'    => $exam->id,
                'order' => $nextUnanswered,
            ]);
        }

        $examExercise = $exam->exercises()
            ->with('exercise.type', 'exercise.subtopic.topic', 'exercise.level', 'exercise.multipleChoiceOptions')
            ->where('order', $order)
            ->firstOrFail();

        $totalQuestions = $exam->exercises()->count();
        $secondsRemaining = $this->flowService->secondsRemaining($exam);

        return view('student.mock-exams.take', [
            'exam'             => $exam,
            'examExercise'     => $examExercise,
            'order'            => $order,
            'totalQuestions'   => $totalQuestions,
            'secondsRemaining' => $secondsRemaining,
        ]);
    }

    public function answer(Request $request, int $id, int $order): RedirectResponse
    {
        $exam = $this->loadOwnedExam($id);

        if (!$this->flowService->isExamStillActive($exam)) {
            return redirect()->route('student.mock-exams.results', $exam->id);
        }

        $givenAnswer = $request->input('given_answer');

        if (is_array($givenAnswer)) {
            $givenAnswer = json_encode(array_values($givenAnswer));
        }

        $this->flowService->saveAnswer($exam, $order, $givenAnswer);

        $totalQuestions = $exam->exercises()->count();

        if ($order >= $totalQuestions) {
            $this->flowService->finishExam($exam);
            return redirect()->route('student.mock-exams.results', $exam->id);
        }

        return redirect()->route('student.mock-exams.take', [
            'id'    => $exam->id,
            'order' => $order + 1,
        ]);
    }

    public function finish(int $id): RedirectResponse
    {
        $exam = $this->loadOwnedExam($id);

        if (!$exam->is_completed) {
            $this->flowService->finishExam($exam);
        }

        return redirect()->route('student.mock-exams.results', $exam->id);
    }

    /**
     * Pantalla de resultados detallados del simulacro.
     */
    public function results(int $id): View
    {
        $exam = $this->loadOwnedExam($id);

        // Si por algún motivo no está completado y se accede a resultados,
        // forzamos su finalización
        if (!$exam->is_completed) {
            $this->flowService->finishExam($exam);
            $exam->refresh();
        }

        $topicBreakdown    = $this->resultsService->getTopicBreakdown($exam);
        $incorrectQuestions = $this->resultsService->getIncorrectQuestions($exam);
        $duration           = $this->resultsService->getDuration($exam);

        return view('student.mock-exams.results', [
            'exam'                => $exam,
            'topicBreakdown'      => $topicBreakdown,
            'incorrectQuestions'  => $incorrectQuestions,
            'duration'            => $duration,
            'resultsService'      => $this->resultsService,
        ]);
    }

    private function loadOwnedExam(int $id): MockExam
    {
        $exam = MockExam::with('config', 'exercises')
            ->where('id', $id)
            ->where('student_id', auth()->id())
            ->firstOrFail();

        return $exam;
    }
}
