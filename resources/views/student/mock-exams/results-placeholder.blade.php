@extends('student.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Simulacro finalizado</h4>
                </div>
                <div class="card-body">
                    @php
                        $isPassed = $exam->score >= $exam->config->min_passing_score;
                    @endphp

                    <div class="text-center mb-4">
                        @if($isPassed)
                            <div class="display-1">🎉</div>
                            <h2 class="text-success mb-3">¡Aprobado!</h2>
                        @else
                            <div class="display-1">📚</div>
                            <h2 class="text-warning mb-3">Sigue practicando</h2>
                        @endif

                        <h3 class="mb-2">{{ $exam->score }} %</h3>
                        <p class="text-muted">
                            Puntaje mínimo de aprobación: {{ $exam->config->min_passing_score }}%
                        </p>
                    </div>

                    <div class="row text-center">
                        <div class="col-md-4">
                            <strong>Inicio:</strong><br>
                            <small>{{ $exam->started_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <div class="col-md-4">
                            <strong>Fin:</strong><br>
                            <small>{{ $exam->ended_at?->format('d/m/Y H:i') ?? '-' }}</small>
                        </div>
                        <div class="col-md-4">
                            <strong>Duración:</strong><br>
                            <small>
                                @if($exam->ended_at)
                                    {{ $exam->started_at->diffInMinutes($exam->ended_at) }} min
                                @else
                                    -
                                @endif
                            </small>
                        </div>
                    </div>

                    <hr>

                    <div class="alert alert-info">
                        <small>
                            <em>Esta es una vista temporal. En la próxima etapa veremos:</em><br>
                            - Desglose por módulo temático<br>
                            - Lista de preguntas incorrectas con solución paso a paso<br>
                            - Comparativa visual con el puntaje mínimo
                        </small>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-primary">
                            Volver al dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
