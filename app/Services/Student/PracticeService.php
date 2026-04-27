<?php

namespace App\Services\Student;

use App\Models\Exercise;
use App\Models\ExerciseLevel;
use App\Models\MasteryLevel;
use App\Models\ModuleProgress;
use App\Models\PracticeSession;
use App\Models\PracticeStatus;
use App\Models\SessionExercise;
use App\Models\SessionMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\SavedExercise;
use App\Models\SessionTopicSummary;
use App\Models\SubtopicLevelProgress;
use App\Models\LearningPath;
use App\Models\InitialAssessment;

class PracticeService
{
    public function startPractice(array $data, int $studentId): PracticeSession
    {
        //obtemoemdp el id del modo de sesion
        $mode = SessionMode::findOrFail($data['mode_id']);

        if ($mode->name === 'Libre') {
            //si es modo libre se inicia
            return $this->startFreePractice($data, $studentId);
        }

        if ($mode->name === 'Guiada') {
            //si es modo guiada se inicia
            return $this->startGuidedPractice($data, $studentId);
        }
        //si no se reconoce modo se aborta
        throw ValidationException::withMessages([
            'mode_id' => 'El tipo de práctica seleccionado no es válido.',
        ]);
    }

    public function startFreePractice(array $data, int $studentId): PracticeSession
    {
        return DB::transaction(function () use ($data, $studentId) {
            //iniciando session libre
            $mode = SessionMode::where('name', 'Libre')->firstOrFail();
            //obteniendo estatus de la ptracita
            $status = PracticeStatus::where('name', 'En curso')->firstOrFail();
            //obteniendo ejercicios
            $exercises = $this->getFreePracticeExercises($data);
            //validando que haya ejercicios para la practica
            if ($exercises->count() < (int)$data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'No hay suficientes ejercicios disponibles con los filtros seleccionados.',
                ]);
            }
            //guardando session con sus datos
            $session = PracticeSession::create([
                'student_id' => $studentId,
                'mode_id' => $mode->id,
                'topic_id' => $data['topic_id'],
                'initial_difficulty_id' => $data['level_id'],
                'started_at' => now(),
                'ended_at' => null,
                'total_exercises' => $exercises->count(),
                'total_correct' => 0,
                'total_incorrect' => 0,
                'total_time_seconds' => 0,
                'status_id' => $status->id,
            ]);
            //guardando ejercicios de la session
            foreach ($exercises as $index => $exercise) {
                SessionExercise::create([
                    'session_id' => $session->id,
                    'exercise_id' => $exercise->id,
                    'order' => $index + 1,
                    'given_answer' => null,
                    'is_correct' => null,
                    'resolution_seconds' => null,
                    'answered_at' => null,
                ]);
            }
            //retornando ejercicios de la sesion
            return $session->load([
                'sessionExercises.exercise.subtopic.topic',
                'sessionExercises.exercise.level',
                'sessionExercises.exercise.type',
            ]);
        });
    }

    public function startGuidedPractice(array $data, int $studentId): PracticeSession
    {
        return DB::transaction(function () use ($data, $studentId) {
            //obteniendo modo y estado de la sesion
            $mode = SessionMode::where('name', 'Guiada')->firstOrFail();
            $status = PracticeStatus::where('name', 'En curso')->firstOrFail();
            //obteniendo ruta de aprendizaje del estudiante activa
            $learningPath = LearningPath::where('student_id', $studentId)
                ->where('is_active', true)
                ->with([
                    'items.subtopic.topic',
                    'items.subtopic.exercises.level',
                    'items.subtopic.exercises.status',
                ])->latest('created_at')->first();
            //si no tiene una ruta activa se retorna y no se puede realizar practica guiada
            if (!$learningPath) {
                throw ValidationException::withMessages([
                    'mode_id' => 'No tienes una ruta de aprendizaje activa. Primero debes generar una ruta.',
                ]);
            }
            //obteniendo ejercicios para la sesion guiada
            $exercises = $this->getGuidedPracticeExercises(
                learningPath: $learningPath,
                studentId: $studentId,
                quantity: (int)$data['quantity']
            );
            //si ya no hay ejercicios disponibles, se retorna
            if ($exercises->isEmpty()) {
                throw ValidationException::withMessages([
                    'quantity' => 'No se encontraron ejercicios disponibles para tu ruta de aprendizaje.',
                ]);
            }
            //obteniendo primer ejercicio, modulo y dificultad
            $firstExercise = $exercises->first();
            $topicId = $firstExercise->subtopic->topic_id;
            $initialLevelId = $firstExercise->level_id;
            //guardando sesion
            $session = PracticeSession::create([
                'student_id' => $studentId,
                'mode_id' => $mode->id,
                'topic_id' => $topicId,
                'initial_difficulty_id' => $initialLevelId,
                'started_at' => now(),
                'ended_at' => null,
                'total_exercises' => $exercises->count(),
                'total_correct' => 0,
                'total_incorrect' => 0,
                'total_time_seconds' => 0,
                'status_id' => $status->id,
            ]);
            //creando ejercicios de la sesion
            foreach ($exercises as $index => $exercise) {
                SessionExercise::create([
                    'session_id' => $session->id,
                    'exercise_id' => $exercise->id,
                    'order' => $index + 1,
                    'given_answer' => null,
                    'is_correct' => null,
                    'resolution_seconds' => null,
                    'answered_at' => null,
                ]);
            }
            //retornando con la sesion guiada
            return $session->load([
                'mode',
                'topic',
                'initialDifficulty',
                'status',
                'sessionExercises.exercise.subtopic.topic',
                'sessionExercises.exercise.level',
                'sessionExercises.exercise.type',
            ]);
        });
    }

    private function getGuidedPracticeExercises(LearningPath $learningPath, int $studentId, int $quantity)
    {
        //obteniendo ejercicios segun la ruta de aprendizaje (no completados)
        $pathItems = $learningPath->items()->with('subtopic.topic')
            ->where('is_completed', false)->orderBy('order')->get();
        //si no hay se obtienen los completados
        if ($pathItems->isEmpty()) {
            $pathItems = $learningPath->items()->with('subtopic.topic')->orderBy('order')->get();
        }
        //obteniendo los ids de los subtemas
        $subtopicIds = $pathItems->pluck('subtopic_id')->unique()->values();
        //obteniendo el progreso historico del subtema
        $progressBySubtopic = SubtopicLevelProgress::where('student_id', $studentId)
            ->whereIn('subtopic_id', $subtopicIds)->get()->keyBy('subtopic_id');
        //ordenando los subtemas por porcentaje de precision
        $orderedSubtopicIds = $subtopicIds
            ->sortBy(function ($subtopicId) use ($progressBySubtopic, $studentId) {
                $progress = $progressBySubtopic->get($subtopicId);
                return $progress?->accuracy_percentage
                    ?? $this->getStudentSubtopicAccuracy($studentId, $subtopicId);
            })->values();
        //creando coleccion de ejercicios
        $selectedExercises = collect();

        foreach ($orderedSubtopicIds as $subtopicId) {
            //si hay menos ejercicios que los pedidos se retorna
            if ($selectedExercises->count() >= $quantity) {
                break;
            }
            //obteniendo progreso por submodulo
            $progress = $progressBySubtopic->get($subtopicId);
            //dificultad
            $levelId = $progress?->current_difficulty_id
                ?? ExerciseLevel::orderBy('id')->value('id');
            //determinando cuantos ejercicios falta para llegar a los solicitados
            $needed = $quantity - $selectedExercises->count();
            //obteniendo ejercicios para completar los solicitados
            $exercises = $this->queryAvailableGuidedExercises(
                studentId: $studentId,
                subtopicId: $subtopicId,
                levelId: $levelId,
                limit: $needed
            );
            //juntando los ejercicios con los obtenidos
            $selectedExercises = $selectedExercises->merge($exercises);
        }
        //retornando los ejercicios para la practica guiada
        return $selectedExercises->unique('id')->take($quantity)->values();
    }

    private function queryAvailableGuidedExercises(int $studentId, int $subtopicId, int $levelId, int $limit)
    {
        //obteniendo ejercicios que no hayan sido respondidos en los 7 dias anteriores por el estudiante
        $recentCorrectExerciseIds = SessionExercise::query()
            ->where('is_correct', true)->where('answered_at', '>=', now()->subDays(7))
            ->whereHas('session', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })->pluck('exercise_id')->toArray();
        //obteniendo niveles de dificultad
        $levelIds = ExerciseLevel::orderBy('id')->pluck('id')->toArray();
        //ordenando los niveles de dificultad
        $orderedLevelIds = collect($levelIds)->sortBy(fn($id) => abs($id - $levelId))->values()->toArray();
        //retornando ejercicios que no hayan sido respondidos
        return Exercise::query()
            ->with([
                'subtopic.topic',
                'level',
                'type',
                'multipleChoiceOptions',
                'steps',
            ])->where('subtopic_id', $subtopicId)->whereIn('level_id', $orderedLevelIds)
            ->whereNotIn('id', $recentCorrectExerciseIds)
            ->whereHas('status', function ($query) {
                $query->whereIn('name', ['Aprobado', 'Publicado']);
            })
            ->orderByRaw('FIELD(level_id, ' . implode(',', $orderedLevelIds) . ')')
            ->inRandomOrder()->limit($limit)->get();
    }

    public function getSessionData(PracticeSession $session, int $studentId): array
    {
        //validando usuario
        $this->validateSessionOwner($session, $studentId);
        //cargando datos de la sesion
        $session->load([
            'mode',
            'topic',
            'initialDifficulty',
            'status',
            'sessionExercises.exercise.subtopic.topic',
            'sessionExercises.exercise.level',
            'sessionExercises.exercise.type',
        ]);
        //retornando con sesion y ejercicios
        return [
            'session' => $session,
            'sessionExercises' => $session->sessionExercises->sortBy('order'),
        ];
    }

    private function getFreePracticeExercises(array $data)
    {
        //retornando ejercicios aprobados aleatorios en base al subtema
        return Exercise::query()
            ->with([
                'subtopic.topic',
                'level',
                'type',
                'multipleChoiceOptions',
                'steps',
            ])
            //filtrando por nivel
            ->where('level_id', $data['level_id'])
            //por tipo
            ->whereHas('subtopic', function ($query) use ($data) {
                $query->where('topic_id', $data['topic_id']);
                if (!empty($data['subtopic_id'])) {
                    $query->where('id', $data['subtopic_id']);
                }
                //con status
            })->whereHas('status', function ($query) {
                $query->whereIn('name', ['Aprobado', 'Publicado']);
            })
            //aleatorios
            ->inRandomOrder()->limit((int)$data['quantity'])->get();
    }

    //metodo para validar estudiante de la sesion con sesion de practica
    private function validateSessionOwner(PracticeSession $session, int $studentId): void
    {
        if ((int)$session->student_id !== $studentId) {
            abort(403, 'No tienes permiso para acceder a esta práctica.');
        }
    }

    public function getExerciseData(PracticeSession $session, SessionExercise $sessionExercise, int $studentId): array
    {
        $this->validateSessionOwner($session, $studentId);
        //validando id de la sesion del ejercicio requerida con la sesion actual
        if ((int)$sessionExercise->session_id !== (int)$session->id) {
            abort(404);
        }
        //cargado ejericio seleccionado
        $sessionExercise->load([
            'exercise.subtopic.topic',
            'exercise.level',
            'exercise.type',
            'exercise.multipleChoiceOptions',
            'exercise.steps',
        ]);
        //ejercicio previo
        $previousExercise = SessionExercise::where('session_id', $session->id)
            ->where('order', '<', $sessionExercise->order)
            ->orderByDesc('order')
            ->first();
        //siguiente ejercicio
        $nextExercise = SessionExercise::where('session_id', $session->id)
            ->where('order', '>', $sessionExercise->order)
            ->orderBy('order')
            ->first();

        return [
            'session' => $session,
            'sessionExercise' => $sessionExercise,
            'exercise' => $sessionExercise->exercise,
            'previousExercise' => $previousExercise,
            'nextExercise' => $nextExercise,
        ];
    }

    public function answerExercise(PracticeSession $session, SessionExercise $sessionExercise, mixed $answer, int $studentId): SessionExercise
    {
        return DB::transaction(function () use ($session, $sessionExercise, $answer, $studentId) {
            //validando sesion de practica y ejerciio de la sesion
            $this->validateSessionOwner($session, $studentId);
            $this->validateSessionExerciseBelongsToSession($session, $sessionExercise);
            //validando que el ejercicio no haya sido respondido
            if (!is_null($sessionExercise->answered_at)) {
                throw ValidationException::withMessages([
                    'answer' => 'Este ejercicio ya fue respondido.',
                ]);
            }
            //cargando ejericio
            $sessionExercise->load([
                'exercise.type',
                'exercise.subtopic.topic',
                'exercise.multipleChoiceOptions',
            ]);
            $exercise = $sessionExercise->exercise;
            //verificando answer
            $isCorrect = $this->checkAnswer($exercise, $answer);
            //normalizando entrada de respuesta
            $normalizedAnswer = $this->normalizeGivenAnswer($exercise, $answer);
            //obteniendo tiempo de la respuesta
            $resolutionSeconds = $this->calculateResolutionSeconds($session, $sessionExercise);
            //actualizando ejercicio en base a la respuesta
            $sessionExercise->update([
                'given_answer' => $normalizedAnswer,
                'is_correct' => $isCorrect,
                'resolution_seconds' => $resolutionSeconds,
                'answered_at' => now(),
            ]);
            //actualizando session de practica
            $this->updateSessionTotals($session);
            //evaluando progreso en subtema
            $this->updateSubtopicProgress($studentId, $exercise, $isCorrect);
            $this->updateSessionTopicSummary($session, $exercise);
            $this->finishSessionIfCompleted($session);
            return $sessionExercise->refresh();
        });
    }

    public function saveExercise(PracticeSession $session, SessionExercise $sessionExercise, int $studentId): SavedExercise
    {
        return DB::transaction(function () use ($session, $sessionExercise, $studentId) {
            //valiando session de preactica
            $this->validateSessionOwner($session, $studentId);
            $this->validateSessionExerciseBelongsToSession($session, $sessionExercise);
            //guardando ejercicio
            return SavedExercise::firstOrCreate(
                [
                    'student_id' => $studentId,
                    'session_exercise_id' => $sessionExercise->id,
                ],
                [
                    'saved_at' => now(),
                ]
            );
        });
    }

    private function validateSessionExerciseBelongsToSession(
        PracticeSession $session,
        SessionExercise $sessionExercise
    ): void {
        if ((int)$sessionExercise->session_id !== (int)$session->id) {
            abort(404);
        }
    }

    private function checkAnswer(Exercise $exercise, mixed $answer): bool
    {
        //obteniendo tipo de ejercicio
        $typeName = mb_strtolower($exercise->type->name ?? '');
        //si es de opcion multiplñe
        if (str_contains($typeName, 'opción') || str_contains($typeName, 'opcion')) {
            return $this->checkMultipleChoiceAnswer($exercise, $answer);
        }
        //si es v/f
        if (str_contains($typeName, 'verdadero') || str_contains($typeName, 'falso')) {
            return $this->normalizeText($answer) === $this->normalizeText($exercise->correct_answer);
        }
        //si es numerica abierta
        if (str_contains($typeName, 'numérica') || str_contains($typeName, 'numerica')) {
            return $this->checkNumericAnswer($exercise->correct_answer, $answer);
        }
        //si es de completar espacios
        if (str_contains($typeName, 'completar') || str_contains($typeName, 'espacios')) {
            return $this->checkTextAnswer($exercise->correct_answer, $answer);
        }
        //validando respuesta con tipo de dato
        return $this->normalizeText($answer) === $this->normalizeText($exercise->correct_answer);
    }

    private function checkMultipleChoiceAnswer(Exercise $exercise, mixed $answer): bool
    {
        //verificando si la respuesta es correcta en las multiples respuestas
        return $exercise->multipleChoiceOptions()->where('id', $answer)
            ->where('is_correct', true)->exists();
    }

    private function checkNumericAnswer(mixed $correctAnswer, mixed $givenAnswer): bool
    {
        //verificando si la respuestas es correcta
        if (!is_numeric($correctAnswer) || !is_numeric($givenAnswer)) {
            return false;
        }
        return abs((float)$correctAnswer - (float)$givenAnswer) < 0.0001;
    }

    private function checkTextAnswer(mixed $correctAnswer, mixed $givenAnswer): bool
    {
        return $this->normalizeText($correctAnswer) === $this->normalizeText($givenAnswer);
    }

    private function normalizeText(mixed $value): string
    {
        return mb_strtolower(trim((string)$value));
    }

    private function normalizeGivenAnswer(Exercise $exercise, mixed $answer): string
    {
        //normalizando tipo de ejercicio
        $typeName = mb_strtolower($exercise->type->name ?? '');
        //normalizando respuestas de ejercicio multiple
        if (str_contains($typeName, 'opción') || str_contains($typeName, 'opcion')) {
            $option = $exercise->multipleChoiceOptions()->where('id', $answer)->first();
            //retornando respuesta correcta encontrada
            return $option?->option_text ?? (string)$answer;
        }
        //retornando respuesta normalizada
        return trim((string)$answer);
    }

    private function calculateResolutionSeconds(PracticeSession $session, SessionExercise $sessionExercise): int
    {
        //obteniendo tiempo de respuesta anterior
        $previousAnsweredAt = SessionExercise::where('session_id', $session->id)
            ->whereNotNull('answered_at')->orderByDesc('answered_at')->value('answered_at');
        //obteniendo tiempo de inicio de respuesta
        $startTime = $previousAnsweredAt ?? $session->started_at ?? now();
        //retornando diferencia de tiempos
        return max(1, now()->diffInSeconds($startTime));
    }

    private function updateSessionTotals(PracticeSession $session): void
    {
        //actualizando datos de la sesion de practica
        //ejercicios resueltos
        $answeredExercises = SessionExercise::where('session_id', $session->id)->whereNotNull('answered_at');
        //ejercicios correctos
        $totalCorrect = (clone $answeredExercises)->where('is_correct', true)->count();
        //ejercicios incorrectos
        $totalIncorrect = (clone $answeredExercises)->where('is_correct', false)->count();
        //tiempo de respueta
        $totalTimeSeconds = (clone $answeredExercises)->sum('resolution_seconds');
        //actualizando sesion
        $session->update([
            'total_correct' => $totalCorrect,
            'total_incorrect' => $totalIncorrect,
            'total_time_seconds' => $totalTimeSeconds,
            'ended_at' => now(),
        ]);
    }

    private function updateSubtopicProgress(int $studentId, Exercise $exercise, bool $isCorrect): void
    {
        //creando o actualizando avance en el subtema
        $progress = SubtopicLevelProgress::firstOrCreate(
            [
                'student_id' => $studentId,
                'subtopic_id' => $exercise->subtopic_id,
            ],
            [
                'current_difficulty_id' => $exercise->level_id,
                'consecutive_correct' => 0,
                'consecutive_incorrect' => 0,
                'total_exercises' => 0,
                'total_correct' => 0,
                'accuracy_percentage' => 0,
            ]
        );
        $totalExercises = $progress->total_exercises + 1;
        $totalCorrect = $progress->total_correct + ($isCorrect ? 1 : 0);
        //actualizando progreso ****************************************
        $progress->update([
            'current_difficulty_id' => $this->calculateCurrentDifficultyId($progress, $exercise, $isCorrect),
            'consecutive_correct' => $isCorrect ? $progress->consecutive_correct + 1 : 0,
            'consecutive_incorrect' => $isCorrect ? 0 : $progress->consecutive_incorrect + 1,
            'total_exercises' => $totalExercises,
            'total_correct' => $totalCorrect,
            'accuracy_percentage' => round(($totalCorrect / $totalExercises) * 100, 2),
        ]);
    }

    //calculando el progreso en la dificultad
    private function calculateCurrentDifficultyId(SubtopicLevelProgress $progress, Exercise $exercise, bool $isCorrect): int
    {
        //subiendo dificultad con 3 correctas consecutivas
        if ($isCorrect && ($progress->consecutive_correct + 1) >= 3) {
            return $this->getNextExerciseLevelId($exercise->level_id) ?? $exercise->level_id;
        }
        $totalAfterAnswer = $progress->total_exercises + 1;
        $correctAfterAnswer = $progress->total_correct + ($isCorrect ? 1 : 0);
        $incorrectAfterAnswer = $totalAfterAnswer - $correctAfterAnswer;
        $incorrectPercentage = ($incorrectAfterAnswer / $totalAfterAnswer) * 100;
        //si falla mas del 60% baja la dificultad
        if ($incorrectPercentage > 60) {
            return $this->getPreviousExerciseLevelId($exercise->level_id) ?? $exercise->level_id;
        }
        //retornando dificultad
        return $progress->current_difficulty_id ?? $exercise->level_id;
    }

    private function getNextExerciseLevelId(int $currentLevelId): ?int
    {
        return ExerciseLevel::where('id', '>', $currentLevelId)->orderBy('id')->value('id');
    }

    private function getPreviousExerciseLevelId(int $currentLevelId): ?int
    {
        return ExerciseLevel::where('id', '<', $currentLevelId)->orderByDesc('id')->value('id');
    }

    private function updateSessionTopicSummary(PracticeSession $session, Exercise $exercise): void
    {
        $topicId = $exercise->subtopic->topic_id;

        $summary = SessionTopicSummary::firstOrCreate(
            [
                'session_id' => $session->id,
                'topic_id' => $topicId,
            ],
            [
                'total_exercises' => 0,
                'total_correct' => 0,
                'total_incorrect' => 0,
            ]
        );

        $answeredExercises = SessionExercise::query()
            ->where('session_id', $session->id)
            ->whereNotNull('answered_at')
            ->whereHas('exercise.subtopic', function ($query) use ($topicId) {
                $query->where('topic_id', $topicId);
            });

        $summary->update([
            'total_exercises' => (clone $answeredExercises)->count(),
            'total_correct' => (clone $answeredExercises)->where('is_correct', true)->count(),
            'total_incorrect' => (clone $answeredExercises)->where('is_correct', false)->count(),
        ]);
    }

    public function getStudentSessions(int $studentId)
    {
        return PracticeSession::with([
            'mode',
            'topic',
            'initialDifficulty',
            'status',
            'sessionExercises',
        ])->where('student_id', $studentId)->latest('started_at')->get();
    }

    private function finishSessionIfCompleted(PracticeSession $session): void
    {
        //obteniendo total de ejercicios de la sesion
        $totalExercises = SessionExercise::where('session_id', $session->id)->count();
        //obteniendo los ejercisios respondidos de la sesion
        $answeredExercises = SessionExercise::where('session_id', $session->id)->whereNotNull('answered_at')->count();
        //si no se han completado los ejercicios no pasa nada
        if ($totalExercises === 0 || $answeredExercises < $totalExercises) {
            return;
        }
        //obteniendo estado finalizada
        $finishedStatus = PracticeStatus::where('name', 'Finalizada')->firstOrFail();
        //si ya se resolvieron los ejercicios, se actualiza el estado de la sesion
        $session->update(['status_id' => $finishedStatus->id, 'ended_at' => now(),]);

        $completedSession = $session->fresh();
        $score = $completedSession->total_exercises > 0
            ? round(($completedSession->total_correct / $completedSession->total_exercises) * 100, 2)
            : 0;

        $masteryLevelId = $this->resolveMasteryLevel($score);

        ModuleProgress::create([
            'student_id' => $completedSession->student_id,
            'topic_id' => $completedSession->topic_id,
            'mastery_level_id' => $masteryLevelId,
            'score' => $score,
        ]);
    }

    private function resolveMasteryLevel(float $score): int
    {
        $level = MasteryLevel::where('min_threshold', '<=', $score)
            ->where('max_threshold', '>=', $score)
            ->first();

        return $level?->id ?? MasteryLevel::orderBy('min_threshold')->first()->id;
    }

    private function getStudentSubtopicAccuracy(int $studentId, int $subtopicId): float
    {
        $answered = SessionExercise::query()
            ->whereNotNull('answered_at')
            ->whereHas('session', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->whereHas('exercise', function ($query) use ($subtopicId) {
                $query->where('subtopic_id', $subtopicId);
            });

        $total = (clone $answered)->count();

        if ($total === 0) {
            return 0;
        }

        $correct = (clone $answered)
            ->where('is_correct', true)
            ->count();

        return round(($correct / $total) * 100, 2);
    }
}
