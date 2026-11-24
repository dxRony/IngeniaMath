<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\DuplicateAlert;
use App\Models\EducationalResource;
use App\Models\Exercise;
use App\Models\ExerciseStatus;
use App\Models\Thread;
use Illuminate\View\View;

class ModeratorDashboardController extends Controller
{
    public function index(): View
    {
        $exerciseReviewStatusId = ExerciseStatus::where('name', 'En revisión')->value('id');
        $resourcePendingStatusId = 1;

        $pendingExercises = Exercise::with(['subtopic.topic', 'creator'])
            ->where('status_id', $exerciseReviewStatusId)
            ->latest()
            ->take(5)
            ->get();

        $pendingResources = EducationalResource::with(['type', 'subtopics.topic', 'creator'])
            ->where('status_id', $resourcePendingStatusId)
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'pendingExercises' => Exercise::where('status_id', $exerciseReviewStatusId)->count(),
            'pendingResources' => EducationalResource::where('status_id', $resourcePendingStatusId)->count(),
            'pendingDuplicates' => DuplicateAlert::where('is_resolved', false)->count(),
            'openThreads' => Thread::where('is_closed', false)->count(),
        ];

        return view('moderator.index', compact('stats', 'pendingExercises', 'pendingResources'));
    }
}
