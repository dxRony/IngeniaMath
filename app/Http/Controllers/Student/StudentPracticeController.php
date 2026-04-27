<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StartFreePracticeRequest;
use App\Models\ExerciseLevel;
use App\Models\PracticeSession;
use App\Models\SessionExercise;
use App\Models\Topic;
use App\Services\Student\PracticeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\Student\AnswerPracticeExerciseRequest;
use App\Models\SessionMode;


class StudentPracticeController extends Controller
{
    public function __construct(
        private readonly PracticeService $practiceService
    )
    {
    }

    public function index(): View
    {
        //obteniendo modulos con subtemas y niveles de ejericcio y modos de practica
        return view('student.practice.index', [
            'topics' => Topic::with('subtopics')->orderBy('order')->get(),
            'levels' => ExerciseLevel::orderBy('id')->get(),
            'modes' => SessionMode::orderBy('id')->get(),
        ]);
    }

    public function showSessions(): View
    {
        //obteniendo sessiones del usuario
        $sessions = $this->practiceService->getStudentSessions(auth()->id());
        return view('student.practice.sessions', [
            'sessions' => $sessions,
        ]);
    }

    public function start(StartFreePracticeRequest $request): RedirectResponse
    {
        //iniciando sesion de practica segun el modo
        $session = $this->practiceService->startPractice(data: $request->validated(), studentId: auth()->id()
        );

        return redirect()->route('student.practice.sessions.show', $session)
            ->with('success', 'Práctica iniciada correctamente.');
    }

    public function showSession(PracticeSession $session): View
    {
        //obteniendo datos de la session
        $sessionData = $this->practiceService->getSessionData($session, auth()->id());
        return view('student.practice.session', $sessionData);
    }

    public function showExercise(PracticeSession $session, SessionExercise $sessionExercise): View
    {
        //obteniendo datos del ejercicio de la sesion
        $exerciseData = $this->practiceService->getExerciseData(
            session: $session, sessionExercise: $sessionExercise, studentId: auth()->id());
        return view('student.practice.exercise', $exerciseData);
    }

    public function answer(AnswerPracticeExerciseRequest $request, PracticeSession $session, SessionExercise $sessionExercise): RedirectResponse
    {
        //manejando respuesta del ejercicio
        $this->practiceService->answerExercise(
            session: $session,
            sessionExercise: $sessionExercise,
            answer: $request->validated()['answer'],
            studentId: auth()->id()
        );

        return redirect()->route('student.practice.exercises.show', [$session, $sessionExercise])
            ->with('success', 'Respuesta registrada correctamente.');
    }

    public function saveExercise(PracticeSession $session, SessionExercise $sessionExercise): RedirectResponse
    {
        //guardando ejercicio para despues
        $this->practiceService->saveExercise(session: $session, sessionExercise: $sessionExercise, studentId: auth()->id());

        return redirect()->route('student.practice.exercises.show', [$session, $sessionExercise])
            ->with('success', 'Ejercicio guardado correctamente.');
    }
}
