@extends('student.main')

@section('content')
    @php
        $typeName = mb_strtolower($exercise->type->name ?? '');
        $isAnswered = !is_null($sessionExercise->answered_at);

        $correctAnswerText = $exercise->correct_answer;

        if (str_contains($typeName, 'opción') || str_contains($typeName, 'opcion')) {
            $correctOption = $exercise->multipleChoiceOptions
                ->where('is_correct', true)
                ->first();

            $correctAnswerText = $correctOption?->option_text ?? 'No definida';
        }
    @endphp

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Resolver ejercicio</h2>
                <p class="text-muted mb-0">
                    Ejercicio #{{ $sessionExercise->order }} de la sesión.
                </p>
            </div>

            <a href="{{ route('student.practice.sessions.show', $session) }}" class="btn btn-outline-secondary">
                Volver a la sesión
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Revisa tu respuesta.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <strong>{{ $exercise->subtopic->name ?? 'Subtema no definido' }}</strong>
                <span>{{ $exercise->level->name ?? 'Nivel no definido' }}</span>
            </div>

            <div class="card-body">
                <h5 class="fw-bold mb-3">Enunciado</h5>

                <div class="mb-3">
                    {!! nl2br(e($exercise->statement)) !!}
                </div>

                @if ($exercise->image_url)
                    <div class="mb-3">
                        <img
                            src="{{ asset('storage/' . $exercise->image_url) }}"
                            alt="Imagen del ejercicio"
                            class="img-fluid rounded border"
                            style="max-height: 350px;"
                        >
                    </div>
                @endif

                <div class="mb-3">
                    <span class="badge bg-info text-dark">
                        Tipo: {{ $exercise->type->name ?? 'No definido' }}
                    </span>
                </div>
            </div>
        </div>

        @if (!$isAnswered)
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Tu respuesta</strong>
                </div>

                <div class="card-body">
                    <form
                        action="{{ route('student.practice.exercises.answer', [$session, $sessionExercise]) }}"
                        method="POST"
                    >
                        @csrf

                        @if (str_contains($typeName, 'opción') || str_contains($typeName, 'opcion'))
                            <div class="mb-4">
                                @foreach ($exercise->multipleChoiceOptions->sortBy('order') as $option)
                                    <div class="form-check mb-2">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="answer"
                                            id="option_{{ $option->id }}"
                                            value="{{ $option->id }}"
                                            required
                                            {{ old('answer') == $option->id ? 'checked' : '' }}
                                        >

                                        <label class="form-check-label" for="option_{{ $option->id }}">
                                            {{ $option->option_text }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                        @elseif (str_contains($typeName, 'verdadero') || str_contains($typeName, 'falso'))
                            <div class="mb-4">
                                <div class="form-check mb-2">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="answer"
                                        id="answer_true"
                                        value="Verdadero"
                                        required
                                        {{ old('answer') === 'Verdadero' ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="answer_true">
                                        Verdadero
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="answer"
                                        id="answer_false"
                                        value="Falso"
                                        required
                                        {{ old('answer') === 'Falso' ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="answer_false">
                                        Falso
                                    </label>
                                </div>
                            </div>

                        @elseif (str_contains($typeName, 'numérica') || str_contains($typeName, 'numerica'))
                            <div class="mb-4">
                                <label for="answer" class="form-label">Respuesta numérica</label>
                                <input
                                    type="number"
                                    step="any"
                                    name="answer"
                                    id="answer"
                                    class="form-control @error('answer') is-invalid @enderror"
                                    value="{{ old('answer') }}"
                                    required
                                >

                                @error('answer')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        @elseif (str_contains($typeName, 'completar') || str_contains($typeName, 'espacios'))
                            <div class="mb-4">
                                <label for="answer" class="form-label">Completar espacios</label>
                                <input
                                    type="text"
                                    name="answer"
                                    id="answer"
                                    class="form-control @error('answer') is-invalid @enderror"
                                    value="{{ old('answer') }}"
                                    required
                                >

                                @error('answer')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <small class="text-muted">
                                    Si hay varias respuestas, sepáralas con "|" SIN ESPACIOS.
                                </small>
                            </div>

                        @else
                            <div class="alert alert-warning">
                                Tipo de ejercicio no reconocido. Revisa el nombre del tipo en la base de datos.
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                Enviar respuesta
                            </button>
                        </div>
                    </form>
                    <form
                        action="{{ route('student.practice.exercises.save', [$session, $sessionExercise]) }}"
                        method="POST"
                        class="mt-3"
                    >
                        @csrf

                        <button type="submit" class="btn btn-outline-warning">
                            Guardar para después
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Resultado</strong>
                </div>

                <div class="card-body">
                    @if ($sessionExercise->is_correct)
                        <div class="alert alert-success">
                            Respuesta correcta.
                        </div>
                    @else
                        <div class="alert alert-danger">
                            Respuesta incorrecta.
                        </div>
                    @endif

                    <p>
                        <strong>Tu respuesta:</strong>
                        {{ $sessionExercise->given_answer }}
                    </p>

                    <p>
                        <strong>Respuesta correcta:</strong>
                        {{ $correctAnswerText }}
                    </p>

                    @if ($exercise->concept_explanation)
                        <hr>
                        <h5 class="fw-bold">Explicación conceptual</h5>
                        <p>{{ $exercise->concept_explanation }}</p>
                    @endif

                    @if ($exercise->steps->isNotEmpty())
                        <hr>
                        <h5 class="fw-bold">Solución paso a paso</h5>

                        @foreach ($exercise->steps->sortBy('step_number') as $step)
                            <div class="mb-3">
                                <strong>Paso {{ $step->step_number }}:</strong>
                                <p class="mb-2">{{ $step->description }}</p>

                                @if ($step->image_url)
                                    <img
                                        src="{{ asset('storage/' . $step->image_url) }}"
                                        alt="Imagen del paso"
                                        class="img-fluid rounded border"
                                        style="max-height: 250px;"
                                    >
                                @endif
                            </div>
                        @endforeach
                    @endif


                </div>
                <div class="d-flex justify-content-between mt-4">
                    <div>
                        @if ($previousExercise)
                            <a
                                href="{{ route('student.practice.exercises.show', [$session, $previousExercise]) }}"
                                class="btn btn-outline-secondary"
                            >
                                Anterior
                            </a>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        @if ($nextExercise)
                            <a
                                href="{{ route('student.practice.exercises.show', [$session, $nextExercise]) }}"
                                class="btn btn-primary"
                            >
                                Siguiente
                            </a>
                        @else
                            <a href="{{ route('student.practice.sessions.show', $session) }}" class="btn btn-success">
                                Finalizar revisión
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
