<?php

namespace App\Services\Tutor;

use App\Models\Exercise;
use App\Models\ExerciseStatus;
use App\Models\ExerciseType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExerciseService
{
    public function getTutorExercises(int $userId): Collection
    {
        return Exercise::with([
            'subtopic.topic',
            'level',
            'type',
            'status',
            'reviews' => function ($query) {
                $query->latest('reviewed_at');
            }
        ])->where('created_by', $userId)->latest()->get();
    }

    public function createExercise(Request $request, int $userId): Exercise
    {
        $draftStatus = ExerciseStatus::where('name', 'Borrador')->firstOrFail();
        $multipleChoiceType = ExerciseType::where('name', 'Opción múltiple')->first();

        return DB::transaction(function () use ($request, $userId, $draftStatus, $multipleChoiceType) {
            $imagePath = null;

            if ($request->hasFile('image_file')) {
                $imagePath = $request->file('image_file')->store('exercises', 'public');
            }
            $exercise = Exercise::create([
                'subtopic_id' => $request->subtopic_id,
                'level_id' => $request->level_id,
                'type_id' => $request->type_id,
                'status_id' => $draftStatus->id,
                'statement' => $request->statement,
                'image_url' => $imagePath,
                'correct_answer' => $this->resolveCorrectAnswer($request),
                'concept_explanation' => $request->concept_explanation,
                'estimated_minutes' => $request->estimated_minutes,
                'created_by' => $userId,
            ]);

            if ($request->has('steps') && is_array($request->steps)) {
                foreach ($request->steps as $index => $step) {
                    $description = trim($step['description'] ?? '');

                    if ($description === '') {
                        continue;
                    }
                    $stepImagePath = null;

                    if ($request->hasFile("steps.$index.image_file")) {
                        $stepImagePath = $request->file("steps.$index.image_file")->store('exercise-steps', 'public');
                    }
                    $exercise->steps()->create([
                        'step_number' => $index + 1,
                        'description' => $description,
                        'image_url' => $stepImagePath,
                    ]);
                }
            }
            if ($multipleChoiceType && (int) $request->type_id === (int) $multipleChoiceType->id) {
                $correctOptionIndex = $request->correct_option;

                if ($request->has('options') && is_array($request->options)) {
                    foreach ($request->options as $index => $option) {
                        $optionText = trim($option['option_text'] ?? '');

                        if ($optionText === '') {
                            continue;
                        }
                        $exercise->multipleChoiceOptions()->create([
                            'option_text' => $optionText,
                            'is_correct' => ((int) $correctOptionIndex === (int) $index),
                            'order' => $index + 1,
                        ]);
                    }
                }
            }
            $this->syncRelatedExercises($exercise, $request->related_exercises ?? []);
            return $exercise;
        });
    }

    public function submitToReview(Exercise $exercise, int $userId): void
    {
        if ((int)$exercise->created_by !== $userId) {
            abort(403, 'No puedes enviar a revisión un ejercicio que no te pertenece.');
        }
        $draftStatus = ExerciseStatus::where('name', 'Borrador')->firstOrFail();
        $reviewStatus = ExerciseStatus::where('name', 'En revisión')->firstOrFail();

        if ((int)$exercise->status_id !== (int)$draftStatus->id) {
            abort(400, 'Solo los ejercicios en borrador pueden enviarse a revisión.');
        }
        $exercise->update(['status_id' => $reviewStatus->id,]);
    }

    public function updateExercise(Request $request, Exercise $exercise, int $userId): Exercise
    {
        if ((int) $exercise->created_by !== $userId) {
            abort(403, 'No puedes actualizar un ejercicio que no te pertenece.');
        }

        return DB::transaction(function () use ($request, $exercise) {
            $imagePath = $exercise->image_url;

            if ($request->hasFile('image_file')) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file('image_file')->store('exercises', 'public');
            }
            $exercise->update([
                'subtopic_id' => $request->subtopic_id,
                'level_id' => $request->level_id,
                'type_id' => $request->type_id,
                'statement' => $request->statement,
                'image_url' => $imagePath,
                'correct_answer' => $this->resolveCorrectAnswer($request),
                'concept_explanation' => $request->concept_explanation,
                'estimated_minutes' => $request->estimated_minutes,
            ]);
            $oldSteps = $exercise->steps()->get();
            $exercise->multipleChoiceOptions()->delete();
            $exercise->steps()->delete();

            if ($request->has('steps') && is_array($request->steps)) {
                foreach ($request->steps as $index => $step) {
                    $description = trim($step['description'] ?? '');

                    if ($description === '') {
                        continue;
                    }
                    $existingImagePath = $step['existing_image_url'] ?? null;
                    $stepImagePath = $existingImagePath;

                    if ($request->hasFile("steps.$index.image_file")) {
                        if ($existingImagePath) {
                            Storage::disk('public')->delete($existingImagePath);
                        }
                        $stepImagePath = $request->file("steps.$index.image_file")->store('exercise-steps', 'public');
                    }
                    $exercise->steps()->create([
                        'step_number' => $index + 1,
                        'description' => $description,
                        'image_url' => $stepImagePath,
                    ]);
                }
            }
            $multipleChoiceType = ExerciseType::where('name', 'Opción múltiple')->first();

            if ($multipleChoiceType && (int) $request->type_id === (int) $multipleChoiceType->id) {
                $correctOptionIndex = $request->correct_option;

                if ($request->has('options') && is_array($request->options)) {
                    foreach ($request->options as $index => $option) {
                        $optionText = trim($option['option_text'] ?? '');

                        if ($optionText === '') {
                            continue;
                        }
                        $exercise->multipleChoiceOptions()->create([
                            'option_text' => $optionText,
                            'is_correct' => ((int) $correctOptionIndex === (int) $index),
                            'order' => $index + 1,
                        ]);
                    }
                }
            }
            $this->syncRelatedExercises($exercise, $request->related_exercises ?? []);
            return $exercise;
        });
    }

    private function resolveCorrectAnswer(Request $request): string
    {
        $type = ExerciseType::find($request->type_id);

        if (!$type) {
            return (string) ($request->correct_answer ?? '');
        }
        return match ($type->name) {
            'Verdadero/Falso' => (string) $request->true_false_answer,
            'Completar espacios' => implode('|', array_map('trim', $request->blank_answers ?? [])),
            default => (string) $request->correct_answer,
        };
    }

    private function syncRelatedExercises(Exercise $exercise, array $relatedIds): void
    {
        if (empty($relatedIds)) {
            $exercise->relatedExercises()->sync([]);
            return;
        }
        $validIds = Exercise::query()
            ->whereIn('id', $relatedIds)
            ->where('id', '!=', $exercise->id)
            ->where('subtopic_id', $exercise->subtopic_id)
            ->where('created_by', $exercise->created_by)
            ->pluck('id')
            ->toArray();
        $exercise->relatedExercises()->sync($validIds);
    }
}
