<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Services\Moderator\ExerciseReviewService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\DuplicateAlert;

class ModeratorExerciseReviewController extends Controller
{
    public function __construct(
        private ExerciseReviewService $exerciseReviewService
    ) {
    }

    public function index(): View
    {
        $exercises = $this->exerciseReviewService->getExercisesInReview();
        return view('moderator.exercises.review-index', compact('exercises'));
    }

    public function show(Exercise $exercise): View
    {
        $exercise = $this->exerciseReviewService->getExerciseForReview($exercise->id);
        return view('moderator.exercises.review-show', compact('exercise'));
    }

    public function review(Request $request, Exercise $exercise): RedirectResponse
    {
        $request->validate([
            'decision' => 'required|in:approve,reject',
            'feedback' => 'nullable|string',
        ]);

        $isApproved = $request->decision === 'approve';

        $this->exerciseReviewService->reviewExercise(
            $exercise,
            auth()->id(),
            $isApproved,
            $request->feedback
        );
        return redirect()
            ->route('moderator.exercises.review.index')
            ->with('success', $isApproved
                ? 'Ejercicio aprobado correctamente.'
                : 'Ejercicio rechazado y devuelto a borrador.');
    }

    public function showDuplicate(DuplicateAlert $duplicateAlert): View
    {
        $alert = $this->exerciseReviewService->getAlertDetail($duplicateAlert->id);
        return view('moderator.duplicate-alerts.show', compact('alert'));
    }

    public function resolve(DuplicateAlert $duplicateAlert): RedirectResponse
    {
        $this->exerciseReviewService->markAsResolved($duplicateAlert);

        return redirect()
            ->route('moderator.exercises.review.index')
            ->with('success', 'Alerta marcada como resuelta.');
    }

    public function rejectNewExercise(DuplicateAlert $duplicateAlert): RedirectResponse
    {
        $this->exerciseReviewService->rejectNewExerciseFromAlert($duplicateAlert, auth()->id());

        return redirect()->route('moderator.exercises.review.index')
            ->with('success', 'Ejercicio nuevo rechazado por duplicado y alerta resuelta.');
    }

}
