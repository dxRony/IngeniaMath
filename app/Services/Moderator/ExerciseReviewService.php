<?php

namespace App\Services\Moderator;

use App\Models\Exercise;
use App\Models\ExerciseReview;
use App\Models\ExerciseStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\DuplicateAlert;

class ExerciseReviewService
{

    public function getExercisesInReview(): Collection
    {
        //obteniendo estado en revision
        $reviewStatus = ExerciseStatus::where('name', 'En revisión')->firstOrFail();
        //retornando ejercicios en revision
        return Exercise::with(['subtopic.topic', 'level', 'type', 'creator'])
            ->where('status_id', $reviewStatus->id)->latest()->get();
    }

    public function getExerciseForReview(int $exerciseId): Exercise
    {
        //retornando ejercicio en revision por id
        return Exercise::with([
            'subtopic.topic',
            'level',
            'type',
            'status',
            'creator',
            'steps',
            'multipleChoiceOptions',
            'duplicateAlertsAsNew' => function ($query) {
                $query->where('is_resolved', false)->with('existingExercise.subtopic.topic');
            },
        ])->findOrFail($exerciseId);
    }

    public function reviewExercise(Exercise $exercise, int $reviewerId, bool $isApproved, ?string $feedback): void
    {
        //obteniendo estados de  revision de ejercicios
        $inReviewStatus = ExerciseStatus::where('name', 'En revisión')->firstOrFail();
        $approvedStatus = ExerciseStatus::where('name', 'Aprobado')->firstOrFail();
        $draftStatus = ExerciseStatus::where('name', 'Borrador')->firstOrFail();

        if ((int) $exercise->status_id !== (int) $inReviewStatus->id) {
            abort(400, 'Solo se pueden revisar ejercicios que estén en revisión.');
        }
        if ($isApproved) {
            //validando que no se apruebe un ejercicio duplicado posiblemente
            $hasPendingDuplicates = DuplicateAlert::where('new_exercise_id', $exercise->id)->where('is_resolved', false)->exists();

            if ($hasPendingDuplicates) {
                abort(400, 'No puedes aprobar este ejercicio mientras tenga alertas de duplicado pendientes.');
            }
        }

        //creando revision del ejercicio
        DB::transaction(function () use ($exercise, $reviewerId, $isApproved, $feedback, $approvedStatus, $draftStatus) {
            ExerciseReview::create([
                'exercise_id' => $exercise->id,
                'reviewed_by' => $reviewerId,
                'is_approved' => $isApproved,
                'feedback' => $feedback,
                'reviewed_at' => now(),
            ]);
            //actualizando ejercicio
            $exercise->update([
                'status_id' => $isApproved ? $approvedStatus->id : $draftStatus->id,
            ]);
        });
    }

    public function getAlertDetail(int $alertId): DuplicateAlert
    {
        //metodo para obtener el detalle de la alerta de duplocado
        return DuplicateAlert::with([
            'newExercise.subtopic.topic',
            'newExercise.level',
            'newExercise.type',
            'newExercise.status',
            'newExercise.creator',
            'newExercise.steps',
            'newExercise.multipleChoiceOptions',
            'existingExercise.subtopic.topic',
            'existingExercise.level',
            'existingExercise.type',
            'existingExercise.status',
            'existingExercise.creator',
            'existingExercise.steps',
            'existingExercise.multipleChoiceOptions',
        ])->findOrFail($alertId);
    }

    public function markAsResolved(DuplicateAlert $duplicateAlert): void
    {
        //marcar alerta como resuelta
        $duplicateAlert->update([
            'is_resolved' => true,
        ]);
    }

    public function rejectNewExerciseFromAlert(DuplicateAlert $duplicateAlert, int $reviewerId): void
    {
        //obteniendo alerta de duplicado
        $duplicateAlert->load('newExercise');
        //estado en borrador
        $draftStatus = ExerciseStatus::where('name', 'Borrador')->firstOrFail();

        DB::transaction(function () use ($duplicateAlert, $reviewerId, $draftStatus) {
            //creando revision con rechazo por duplicado
            ExerciseReview::create([
                'exercise_id' => $duplicateAlert->newExercise->id,
                'reviewed_by' => $reviewerId,
                'is_approved' => false,
                'feedback' => 'El ejercicio fue rechazado por posible duplicado detectado en el banco.',
                'reviewed_at' => now(),
            ]);
            //pasando ejercicio a borrador
            $duplicateAlert->newExercise->update([
                'status_id' => $draftStatus->id,
            ]);
            //solventando alerta de duplicado
            DuplicateAlert::where('new_exercise_id', $duplicateAlert->newExercise->id)
                ->update([
                    'is_resolved' => true,
                ]);
        });
    }
}
