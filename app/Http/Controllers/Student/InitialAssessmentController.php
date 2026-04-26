<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\AssessmentModuleResult;
use App\Services\Student\InitialAssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Models\InitialAssessment;

class InitialAssessmentController extends Controller
{
    public function __construct(
        private readonly InitialAssessmentService $initialAssessmentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $topics = Topic::all()->sortBy('order');
        $assessment = Auth::user()->initialAssessments->where('is_completed', false)->first();
        return view('student.initial-assessments.index', compact('topics', 'assessment'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Evitar que otro estudiante acceda al diagnóstico ajeno
        $initialAssessment = Auth::user()->initialAssessments->findOrFail($id);
        // dd($initialAssessment->toArray());

        if ($initialAssessment->student_id !== Auth::id()) {
            abort(403);
        }

        // Si ya está completado, redirigir al dashboard
        if ($initialAssessment->is_completed) {
            return redirect()->route('student.dashboard');
        }

        // Si ya tiene ejercicios asignados, no volver a generarlos
        if ($initialAssessment->assessmentExercises()->count() === 0) {
            $this->initialAssessmentService->assignExercises($initialAssessment);
        }

        $assessmentExercises = $initialAssessment->assessmentExercises()
            ->with(['exercise.type', 'exercise.multipleChoiceOptions'])
            ->orderBy('order')
            ->get();

        // dd($assessmentExercises->toArray());
        return view('student.initial-assessments.show', [
            'assessment'          => $initialAssessment,
            'assessmentExercises' => $assessmentExercises,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $assessment = InitialAssessment::findOrFail($id);

        if ($assessment->student_id !== Auth::id()) {
            abort(403);
        }

        if ($assessment->is_completed) {
            return redirect()->route('student.dashboard');
        }

        $this->initialAssessmentService->grade($assessment, $request->input('answers', []));

        return redirect()->route('initial-assessments.results', $assessment->id);
    }

    public function results(string $id)
    {
        $assessment = InitialAssessment::findOrFail($id);

        if ($assessment->student_id !== Auth::id()) {
            abort(403);
        }

        if (!$assessment->is_completed) {
            return redirect()->route('initial-assessments.show', $assessment->id);
        }

        $moduleResults = AssessmentModuleResult::where('assessment_id', $assessment->id)
            ->with(['topic', 'masteryLevel'])
            ->get();

        $totalExercises = $assessment->assessmentExercises()->count();
        $totalCorrect   = $assessment->assessmentExercises()->where('is_correct', true)->count();

        return view('student.initial-assessments.results', [
            'assessment'     => $assessment,
            'moduleResults'  => $moduleResults,
            'totalExercises' => $totalExercises,
            'totalCorrect'   => $totalCorrect,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
