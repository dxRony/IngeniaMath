<?php

namespace App\Services\Tutor;

use App\Models\Exercise;
use App\Models\ExerciseStatus;
use App\Models\ExerciseType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\DuplicateAlert;

class ExerciseService
{
    public function getTutorExercises(int $userId): Collection
    {
        //obteniendo ejercicios del tutor
        return Exercise::with(['subtopic.topic', 'level', 'type', 'status', 'reviews' => function ($query) {
            $query->latest('reviewed_at');
        }
        ])->where('created_by', $userId)->latest()->get();
    }

    public function createExercise(Request $request, int $userId): array
    {
        //creando borrador de ejercicio
        $draftStatus = ExerciseStatus::where('name', 'Borrador')->firstOrFail();
        $multipleChoiceType = ExerciseType::where('name', 'Opción múltiple')->first();

        return DB::transaction(function () use ($request, $userId, $draftStatus, $multipleChoiceType) {
            $imagePath = null;
            //guardando imagen si la hay
            if ($request->hasFile('image_file')) {
                $imagePath = $request->file('image_file')->store('exercises', 'public');
            }
            //registrando ejercicio
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
            //guardando paso de resolucion
            if ($request->has('steps') && is_array($request->steps)) {
                //recorriendo pasos
                foreach ($request->steps as $index => $step) {
                    //guardando descripcion del paso
                    $description = trim($step['description'] ?? '');
                    if ($description === '') {
                        continue;
                    }
                    $stepImagePath = null;
                    //guardando imagen
                    if ($request->hasFile("steps.$index.image_file")) {
                        $stepImagePath = $request->file("steps.$index.image_file")->store('exercise-steps', 'public');
                    }
                    //registrando paso del ejercicio
                    $exercise->steps()->create([
                        'step_number' => $index + 1,
                        'description' => $description,
                        'image_url' => $stepImagePath,
                    ]);
                }
            }
            //si el tipo es de opcion multipl
            if ($multipleChoiceType && (int)$request->type_id === (int)$multipleChoiceType->id) {
                //registrando respuesta correcta
                $correctOptionIndex = $request->correct_option;

                if ($request->has('options') && is_array($request->options)) {
                    //recorriendo opciones
                    foreach ($request->options as $index => $option) {
                        //obteniendo texto de opcion
                        $optionText = trim($option['option_text'] ?? '');

                        if ($optionText === '') {
                            continue;
                        }
                        //registrando respuesta de opcion multiple
                        $exercise->multipleChoiceOptions()->create([
                            'option_text' => $optionText,
                            'is_correct' => ((int)$correctOptionIndex === (int)$index),
                            'order' => $index + 1,
                        ]);
                    }
                }
            }
            //registrando ejercicios relacionados si los hay
            $this->syncRelatedExercises($exercise, $request->related_exercises ?? []);
            //detectando posibles duplicados
            $duplicates = $this->detectAndStoreDuplicateAlerts($exercise);

            return [
                'exercise' => $exercise,
                'duplicates' => $duplicates,
            ];
        });
    }

    public function submitToReview(Exercise $exercise, int $userId): void
    {
        //validando autoria de ejercicio
        if ((int)$exercise->created_by !== $userId) {
            abort(403, 'No puedes enviar a revisión un ejercicio que no te pertenece.');
        }
        //obteniendo estados de revision
        $draftStatus = ExerciseStatus::where('name', 'Borrador')->firstOrFail();
        $reviewStatus = ExerciseStatus::where('name', 'En revisión')->firstOrFail();

        if ((int)$exercise->status_id !== (int)$draftStatus->id) {
            //valiando que los ejercicio con estado borrador sean revisados
            abort(400, 'Solo los ejercicios en borrador pueden enviarse a revisión.');
        }
        //actualizando estado de ejercicio
        $exercise->update(['status_id' => $reviewStatus->id,]);
    }

    public function updateExercise(Request $request, Exercise $exercise, int $userId): array
    {
        //validando autoria del ejercicio
        if ((int)$exercise->created_by !== $userId) {
            abort(403, 'No puedes actualizar un ejercicio que no te pertenece.');
        }
        return DB::transaction(function () use ($request, $exercise) {
            $imagePath = $exercise->image_url;
            //registrando imagen del ejercicio
            if ($request->hasFile('image_file')) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file('image_file')->store('exercises', 'public');
            }
            //actualizando ejercicio
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
            //borrando informacion previa del ejercicio
            $exercise->multipleChoiceOptions()->delete();
            $exercise->steps()->delete();
            //recorriendo pasos de la resolucion
            if ($request->has('steps') && is_array($request->steps)) {
                foreach ($request->steps as $index => $step) {
                    //guardando descripcion del paso
                    $description = trim($step['description'] ?? '');

                    if ($description === '') {
                        continue;
                    }
                    //guardando imagen actual del paso
                    $existingImagePath = $step['existing_image_url'] ?? null;
                    $stepImagePath = $existingImagePath;
                    //validando si hay imagen en request para sobreescribirla
                    if ($request->hasFile("steps.$index.image_file")) {
                        if ($existingImagePath) {
                            Storage::disk('public')->delete($existingImagePath);
                        }
                        $stepImagePath = $request->file("steps.$index.image_file")->store('exercise-steps', 'public');
                    }
                    //registrando paso del ejercicio
                    $exercise->steps()->create([
                        'step_number' => $index + 1,
                        'description' => $description,
                        'image_url' => $stepImagePath,
                    ]);
                }
            }
            //si el tipo es de opcion multiple, se recorren las opciones
            $multipleChoiceType = ExerciseType::where('name', 'Opción múltiple')->first();
            if ($multipleChoiceType && (int)$request->type_id === (int)$multipleChoiceType->id) {
                $correctOptionIndex = $request->correct_option;

                if ($request->has('options') && is_array($request->options)) {
                    foreach ($request->options as $index => $option) {
                        //obteniendo texto de la opcion
                        $optionText = trim($option['option_text'] ?? '');

                        if ($optionText === '') {
                            continue;
                        }
                        //registrando opcion multiple del ejercicio
                        $exercise->multipleChoiceOptions()->create([
                            'option_text' => $optionText,
                            'is_correct' => ((int)$correctOptionIndex === (int)$index),
                            'order' => $index + 1,
                        ]);
                    }
                }
            }
            //relacionando ejercicio si los hay
            $this->syncRelatedExercises($exercise, $request->related_exercises ?? []);
            //validando posible duplicado
            DuplicateAlert::where('new_exercise_id', $exercise->id)->delete();
            $duplicates = $this->detectAndStoreDuplicateAlerts($exercise);
            return ['exercise' => $exercise, 'duplicates' => $duplicates,];
        });
    }

    private function resolveCorrectAnswer(Request $request): string
    {
        //obteniendo tipo de ejercicio
        $type = ExerciseType::find($request->type_id);

        if (!$type) {
            return (string)($request->correct_answer ?? '');
        }
        return match ($type->name) {
            //obteniendo respuesta vf o completar espacios del ejercicio
            'Verdadero/Falso' => (string)$request->true_false_answer,
            'Completar espacios' => implode('|', array_map('trim', $request->blank_answers ?? [])),
            default => (string)$request->correct_answer,
        };
    }

    private function syncRelatedExercises(Exercise $exercise, array $relatedIds): void
    {
        //validando que hayan ejercicios relacionados
        if (empty($relatedIds)) {
            $exercise->relatedExercises()->sync([]);
            return;
        }
        //obteniendo ids para relacionar
        $validIds = Exercise::query()
            ->whereIn('id', $relatedIds)
            ->where('id', '!=', $exercise->id)
            ->where('subtopic_id', $exercise->subtopic_id)
            ->where('created_by', $exercise->created_by)
            ->pluck('id')
            ->toArray();
        //relacionando ejercicios
        $exercise->relatedExercises()->sync($validIds);
    }

    private function detectAndStoreDuplicateAlerts(Exercise $exercise): array
    {
        //obteniendo subtema del ejercicio
        $exercise->load('subtopic.topic');
        $topicId = $exercise->subtopic->topic_id;
        //obteniendo y normalizando enunciado del ejercicio
        $newStatement = $this->normalizeText($exercise->statement);
        //obteniendo posibles duplicados, ejercicios del mismo subtema
        $possibleDuplicates = Exercise::query()
            ->with('subtopic')
            ->where('id', '!=', $exercise->id)
            ->whereHas('subtopic', function ($query) use ($topicId) {$query->where('topic_id', $topicId);})->get();
        $foundDuplicates = [];
        //recorriendo los posibles duplicados
        foreach ($possibleDuplicates as $existingExercise) {
            //obteniendo y normalizando enunciado
            $existingStatement = $this->normalizeText($existingExercise->statement);
            //evaluando similaridad
            similar_text($newStatement, $existingStatement, $similarity);
            //evaluando segun valor obtenido
            if ($similarity >= 70) {
                //si es de mas de 70% se crea la alerta de duplicado
                DuplicateAlert::updateOrCreate(
                    [
                        'new_exercise_id' => $exercise->id,
                        'existing_exercise_id' => $existingExercise->id,
                    ],
                    [
                        'similarity' => round($similarity, 2),
                        'created_at' => now(),
                        'is_resolved' => false,
                    ]
                );
                //almacenando duplicados
                $foundDuplicates[] = [
                    'exercise_id' => $existingExercise->id,
                    'similarity' => round($similarity, 2),
                ];
            }
        }
        return $foundDuplicates;
    }

    private function normalizeText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}
