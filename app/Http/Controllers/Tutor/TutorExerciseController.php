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

class TutorExerciseController extends Controller
{
    public function __construct(
        private ExerciseService $exerciseService
    ) {
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

        return view('tutor.exercises.create', compact('topics', 'subtopics', 'levels', 'types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'subtopic_id' => 'required|exists:subtopics,id',
            'level_id' => 'required|exists:exercise_levels,id',
            'type_id' => 'required|exists:exercise_types,id',
            'statement' => 'required|string',
            'image_file' => 'nullable|image|max:2048',
            'correct_answer' => 'required|string',
            'estimated_minutes' => 'required|integer|min:1',

            'steps' => 'nullable|array|min:1',
            'steps.*.description' => 'nullable|string',
            'steps.*.image_file' => 'nullable|image|max:2048',

            'options' => 'nullable|array',
            'options.*.option_text' => 'nullable|string',
            'correct_option' => 'nullable|integer',
        ]);
        $this->exerciseService->createExercise($request, auth()->id());

        return redirect()
            ->route('tutor.exercises.index')
            ->with('success', 'Ejercicio creado en borrador correctamente.');
    }

    public function submitReview(Exercise $exercise): RedirectResponse
    {
        $this->exerciseService->submitToReview($exercise, auth()->id());
        return redirect()
            ->route('tutor.exercises.index')
            ->with('success', 'Ejercicio enviado a revisión correctamente.');
    }
}
