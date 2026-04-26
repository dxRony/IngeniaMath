<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\ExerciseLevel;
use App\Models\ExerciseType;
use App\Models\Subtopic;
use App\Models\Topic;
use App\Services\Tutor\ExerciseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class TutorExerciseController extends Controller
{
    public function __construct(private ExerciseService $exerciseService)
    {
    }

    public function index(): View
    {
        $exercises = $this->exerciseService->getTutorExercises(auth()->id());
        return view('tutor.exercises.index', compact('exercises'));
    }

    public function create(): View
    {
        $topics = Topic::orderBy('order')->get();
        $subtopics = Subtopic::with('topic')->orderBy('topic_id')->orderBy('order')->get();
        $levels = ExerciseLevel::all();
        $types = ExerciseType::all();
        $relatedExercises = Exercise::where('created_by', auth()->id())
            ->orderByDesc('id')->get();

        return view('tutor.exercises.create', compact(
            'topics', 'subtopics', 'levels', 'types', 'relatedExercises'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validateExerciseRequest($request);
        $result = $this->exerciseService->createExercise($request, auth()->id());
        $duplicates = $result['duplicates'];
        $message = 'Ejercicio creado en borrador.';

        if (!empty($duplicates)) {
            $message .= ' Advertencia: se detectaron posibles ejercicios duplicados.';
        }
        return redirect()
            ->route('tutor.exercises.index')
            ->with('success', $message)
            ->with('duplicate_alerts', $duplicates);
    }

    public function submitReview(Exercise $exercise): RedirectResponse
    {
        $this->exerciseService->submitToReview($exercise, auth()->id());
        return redirect()
            ->route('tutor.exercises.index')
            ->with('success', 'Ejercicio enviado a revisión.');
    }

    public function edit(Exercise $exercise): View
    {
        if ((int)$exercise->created_by !== (int)auth()->id()) {
            abort(403, 'No puedes editar un ejercicio que no te pertenece.');
        }
        if ($exercise->status->name !== 'Borrador') {
            abort(403, 'Solo puedes editar ejercicios en borrador.');
        }
        $topics = Topic::orderBy('order')->get();
        $subtopics = Subtopic::with('topic')->orderBy('topic_id')->orderBy('order')->get();
        $levels = ExerciseLevel::all();
        $types = ExerciseType::all();
        $relatedExercises = Exercise::where('created_by', auth()->id())
            ->where('id', '!=', $exercise->id)->orderByDesc('id')->get();

        $exercise->load([
            'steps',
            'multipleChoiceOptions',
            'subtopic.topic',
            'relatedExercises',
        ]);

        return view('tutor.exercises.edit', compact(
            'exercise',
            'topics',
            'subtopics',
            'levels',
            'types',
            'relatedExercises'
        ));
    }

    public function update(Request $request, Exercise $exercise): RedirectResponse
    {
        if ((int)$exercise->created_by !== (int)auth()->id()) {
            abort(403, 'No puedes actualizar un ejercicio que no te pertenece.');
        }
        if ($exercise->status->name !== 'Borrador') {
            abort(403, 'Solo puedes actualizar ejercicios en borrador.');
        }
        $this->validateExerciseRequest($request, true);
        $result = $this->exerciseService->updateExercise($request, $exercise, auth()->id());
        $duplicates = $result['duplicates'];
        $message = 'Ejercicio actualizado.';

        if (!empty($duplicates)) {
            $message .= ' Advertencia: se detectaron posibles ejercicios duplicados.';
        }
        return redirect()
            ->route('tutor.exercises.index')
            ->with('success', $message)
            ->with('duplicate_alerts', $duplicates);
    }

    public function show(Exercise $exercise): View
    {
        if ((int)$exercise->created_by !== (int)auth()->id()) {
            abort(403, 'No puedes ver un ejercicio que no te pertenece.');
        }
        $exercise->load([
            'subtopic.topic',
            'level',
            'type',
            'status',
            'steps',
            'multipleChoiceOptions',
            'reviews.reviewer',
        ]);
        return view('tutor.exercises.show', compact('exercise'));
    }

    public function reviews(Exercise $exercise): View
    {
        return view('tutor.exercises.reviews', compact('exercise'));
    }

    private function validateExerciseRequest(Request $request, bool $isUpdate = false): void
    {
        $request->validate([
            'subtopic_id' => 'required|exists:subtopics,id',
            'level_id' => 'required|exists:exercise_levels,id',
            'type_id' => 'required|exists:exercise_types,id',
            'statement' => 'required|string',
            'image_file' => 'nullable|image|max:2048',
            'estimated_minutes' => 'required|integer|min:1',
            'concept_explanation' => 'required|string',
            'steps' => 'required|array|min:1',
            'steps.*.id' => 'nullable|integer',
            'steps.*.description' => 'required|string',
            'steps.*.existing_image_url' => 'nullable|string',
            'steps.*.image_file' => 'nullable|image|max:2048',
            'options' => 'nullable|array',
            'options.*.option_text' => 'nullable|string',
            'correct_option' => 'nullable|integer|min:0',
            'true_false_answer' => 'nullable|in:Verdadero,Falso',
            'blank_answers' => 'nullable|array|min:1',
            'blank_answers.*' => 'nullable|string',
            'correct_answer' => 'nullable|string',
            'related_exercises' => 'nullable|array',
            'related_exercises.*' => 'integer|exists:exercises,id',
        ]);
        $type = ExerciseType::find($request->type_id);

        if (!$type) {
            return;
        }
        switch ($type->name) {
            case 'Opción múltiple':
                $request->validate([
                    'options' => 'required|array|min:2',
                    'options.*.option_text' => 'required|string',
                    'correct_option' => 'required|integer|min:0',
                ]);
                break;

            case 'Verdadero/Falso':
                $request->validate([
                    'true_false_answer' => 'required|in:Verdadero,Falso',
                ]);
                break;

            case 'Respuesta numérica abierta':
                $request->validate([
                    'correct_answer' => 'required|string',
                ]);
                break;

            case 'Completar espacios':
                $request->validate([
                    'blank_answers' => 'required|array|min:1',
                    'blank_answers.*' => 'required|string',
                ]);
                break;

            default:
                $request->validate([
                    'correct_answer' => 'required|string',
                ]);
                break;
        }
    }
}
