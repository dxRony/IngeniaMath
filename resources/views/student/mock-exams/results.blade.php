@extends('student.main')

@section('content')
    <div class="container py-4" style="max-width: 960px;">

        @php
            $isPassed = $exam->score >= $exam->config->min_passing_score;
            $totalQuestions = $exam->exercises->count();
            $correctCount = $exam->exercises->where('is_correct', true)->count();
            $incorrectCount = $totalQuestions - $correctCount;
            $unansweredCount = $exam->exercises->whereNull('answered_at')->count();
        @endphp

        {{-- Encabezado con resultado principal --}}
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body text-center py-4">
                @if($isPassed)
                    <div style="font-size: 5rem; line-height: 1;">🎉</div>
                    <h2 class="text-success mb-3 mt-2">¡Aprobado!</h2>
                @else
                    <div style="font-size: 5rem; line-height: 1;">📚</div>
                    <h2 class="text-warning mb-3 mt-2">Sigue practicando</h2>
                @endif

                <div class="display-3 fw-bold {{ $isPassed ? 'text-success' : 'text-warning' }}">
                    {{ $exam->score }}<small class="fs-3">%</small>
                </div>

                {{-- Barra de comparativa con el puntaje mínimo --}}
                <div class="mx-auto mt-3" style="max-width: 480px;">
                    <div class="progress" style="height: 28px; position: relative;">
                        <div class="progress-bar {{ $isPassed ? 'bg-success' : 'bg-warning' }}"
                             role="progressbar"
                             style="width: {{ $exam->score }}%;">
                            <strong>{{ $exam->score }}%</strong>
                        </div>
                        {{-- Línea del puntaje mínimo --}}
                        <div style="position: absolute; left: {{ $exam->config->min_passing_score }}%; top: -4px; bottom: -4px; border-left: 3px dashed #dc3545;"></div>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Puntaje mínimo de aprobación: <strong>{{ $exam->config->min_passing_score }}%</strong>
                    </small>
                </div>
            </div>
        </div>

        {{-- Métricas generales --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small">Preguntas totales</div>
                        <h3 class="mb-0">{{ $totalQuestions }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small">Correctas</div>
                        <h3 class="mb-0 text-success">{{ $correctCount }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small">Incorrectas</div>
                        <h3 class="mb-0 text-danger">{{ $incorrectCount }}</h3>
                        @if($unansweredCount > 0)
                            <small class="text-muted">({{ $unansweredCount }} sin responder)</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small">Duración</div>
                        <h3 class="mb-0">{{ $duration['formatted'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Desglose por módulo --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Desglose por módulo temático</h5>
            </div>
            <div class="card-body">
                @foreach($topicBreakdown as $row)
                    @php
                        $rowPercentage = $row['percentage'];
                        $barColor = $rowPercentage >= 80 ? 'bg-success'
                                  : ($rowPercentage >= 50 ? 'bg-warning' : 'bg-danger');
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>{{ $row['topic_name'] }}</strong>
                            <span class="text-muted small">
                            {{ $row['correct'] }} / {{ $row['total'] }}
                            ({{ $rowPercentage }}%)
                        </span>
                        </div>
                        <div class="progress" style="height: 18px;">
                            <div class="progress-bar {{ $barColor }}" role="progressbar"
                                 style="width: {{ $rowPercentage }}%;">
                                @if($rowPercentage >= 15)
                                    <small>{{ $rowPercentage }}%</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Lista de preguntas incorrectas --}}
        @if($incorrectQuestions->count() > 0)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Preguntas incorrectas ({{ $incorrectQuestions->count() }})</h5>
                    <small class="text-muted">Revisa la solución de cada pregunta para reforzar tu aprendizaje</small>
                </div>
                <div class="card-body">
                    <div class="accordion" id="incorrectQuestionsAccordion">
                        @foreach($incorrectQuestions as $idx => $examExercise)
                            @php
                                $exercise = $examExercise->exercise;
                                $correctAnswerText = $resultsService->getCorrectAnswerText($exercise);
                                $accordionId = 'q' . $examExercise->order;
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $accordionId }}">
                                        <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 w-100 me-3">
                                            <span class="badge bg-danger">#{{ $examExercise->order }}</span>
                                            <span class="badge rounded-pill text-bg-light border text-secondary">
                                            {{ $exercise->subtopic->topic->name }}
                                        </span>
                                            <span class="text-truncate flex-grow-1">
                                            {!! \Illuminate\Support\Str::limit(strip_tags($exercise->statement), 80) !!}
                                        </span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $accordionId }}"
                                     class="accordion-collapse collapse"
                                     data-bs-parent="#incorrectQuestionsAccordion">
                                    <div class="accordion-body">
                                        {{-- Enunciado completo --}}
                                        <div class="mb-3">
                                            <h6 class="text-muted small text-uppercase">Enunciado</h6>
                                            <p class="mb-0">{!! $exercise->statement !!}</p>
                                        </div>

                                        @if($exercise->image_url)
                                            <img src="{{ Storage::url($exercise->image_url) }}"
                                                 alt="Imagen del ejercicio"
                                                 class="img-fluid rounded border mb-3"
                                                 style="max-height: 200px;">
                                        @endif

                                        {{-- Respuestas comparadas --}}
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-2">
                                                <h6 class="text-muted small text-uppercase">Tu respuesta</h6>
                                                <div class="alert alert-danger mb-0 py-2">
                                                    @if($examExercise->given_answer)
                                                        {{ $examExercise->given_answer }}
                                                    @else
                                                        <em class="text-muted">Sin respuesta</em>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <h6 class="text-muted small text-uppercase">Respuesta correcta</h6>
                                                <div class="alert alert-success mb-0 py-2">
                                                    <strong>{{ $correctAnswerText }}</strong>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Solución paso a paso --}}
                                        @if($exercise->steps->count() > 0)
                                            <h6 class="text-muted small text-uppercase mt-3">Solución paso a paso</h6>
                                            <ol class="ps-3">
                                                @foreach($exercise->steps->sortBy('step_number') as $step)
                                                    <li class="mb-2">
                                                        {!! $step->description !!}
                                                        @if($step->image_url)
                                                            <div class="mt-2">
                                                                <img src="{{ Storage::url($step->image_url) }}"
                                                                     alt="Paso {{ $step->step_number }}"
                                                                     class="img-fluid rounded border"
                                                                     style="max-height: 200px;">
                                                            </div>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ol>
                                        @endif

                                        {{-- Explicación conceptual --}}
                                        @if($exercise->concept_explanation)
                                            <h6 class="text-muted small text-uppercase mt-3">Explicación conceptual</h6>
                                            <div class="alert alert-info mb-0">
                                                {!! $exercise->concept_explanation !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-success text-center">
                <h4 class="mb-0">🌟 ¡Perfecto! Respondiste todas las preguntas correctamente</h4>
            </div>
        @endif

        {{-- Acciones --}}
        <div class="d-flex justify-content-center gap-2 mt-4">
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                Ir al dashboard
            </a>
            <a href="{{ route('student.mock-exams.start-form') }}" class="btn btn-success">
                 Hacer otro simulacro
            </a>
        </div>

    </div>
@endsection
