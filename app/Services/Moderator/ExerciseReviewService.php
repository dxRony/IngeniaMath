<?php

namespace App\Services\Moderator;

use App\Models\Exercise;
use App\Models\ExerciseReview;
use App\Models\ExerciseStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExerciseReviewService
{

    public function getExercisesInReview(): Collection
    {
        $reviewStatus = ExerciseStatus::where('name', 'En revisión')->firstOrFail();

        return Exercise::with(['subtopic.topic', 'level', 'type', 'creator'])
            ->where('status_id', $reviewStatus->id)
            ->latest()
            ->get();
    }

    public function getExerciseForReview(int $exerciseId): Exercise
    {
        return Exercise::with([
            'subtopic.topic',
            'level',
            'type',
            'status',
            'creator',
            'steps',
            'multipleChoiceOptions',
        ])->findOrFail($exerciseId);
    }

    public function reviewExercise(Exercise $exercise, int $reviewerId, bool $isApproved, ?string $feedback): void
    {
        $inReviewStatus = ExerciseStatus::where('name', 'En revisión')->firstOrFail();
        $approvedStatus = ExerciseStatus::where('name', 'Aprobado')->firstOrFail();
        $draftStatus = ExerciseStatus::where('name', 'Borrador')->firstOrFail();

        if ((int) $exercise->status_id !== (int) $inReviewStatus->id) {
            abort(400, 'Solo se pueden revisar ejercicios que estén en revisión.');
        }

        DB::transaction(function () use ($exercise, $reviewerId, $isApproved, $feedback, $approvedStatus, $draftStatus) {
            ExerciseReview::create([
                'exercise_id' => $exercise->id,
                'reviewed_by' => $reviewerId,
                'is_approved' => $isApproved,
                'feedback' => $feedback,
                'reviewed_at' => now(),
            ]);
            $exercise->update(['status_id' => $isApproved ? $approvedStatus->id : $draftStatus->id,]);
        });
    }
}
