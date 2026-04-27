<?php

namespace App\Http\Controllers\Student\MockExams;

use App\Http\Controllers\Controller;
use App\Services\Student\MockExams\MockExamStatsService;
use Illuminate\View\View;

class MockExamHistoryController extends Controller
{
    public function __construct(
        private MockExamStatsService $statsService
    ) {}

    public function index(): View
    {
        $studentId = auth()->id();

        $history       = $this->statsService->getStudentHistory($studentId);
        $evolutionData = $this->statsService->getEvolutionData($studentId);
        $generalStats  = $this->statsService->getGeneralStats($studentId);
        $mostImproved  = $this->statsService->getMostImprovedTopic($studentId);
        $weakestTopic  = $this->statsService->getWeakestTopic($studentId);

        return view('student.mock-exams.history', [
            'history'        => $history,
            'evolutionData'  => $evolutionData,
            'generalStats'   => $generalStats,
            'mostImproved'   => $mostImproved,
            'weakestTopic'   => $weakestTopic,
        ]);
    }
}
