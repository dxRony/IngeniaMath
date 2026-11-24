@extends('student.main')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Sesión de práctica - {{ $session->mode->name ?? 'No definido' }}</h2>
                <p class="text-muted mb-0">
                    Revisa los ejercicios cargados para esta práctica.
                </p>
            </div>
            <a href="{{ route('student.practice.sessions') }}" class="btn btn-outline-secondary">
                Regresar
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Módulo</small>
                        <h6 class="fw-bold mb-0">{{ $session->topic->name ?? 'No definido' }}</h6>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Nivel</small>
                        <h6 class="fw-bold mb-0">{{ $session->initialDifficulty->name ?? 'No definido' }}</h6>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Estado</small>
                        <h6 class="fw-bold mb-0">{{ $session->status->name ?? 'No definido' }}</h6>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Progreso</small>
                        <h6 class="fw-bold mb-0">
                            {{ $sessionExercises->whereNotNull('answered_at')->count() }}
                            /
                            {{ $sessionExercises->count() }}
                        </h6>
                    </div>
                </div>
            </div>
        </div>

        @php
            $firstPendingExercise = $sessionExercises->firstWhere('answered_at', null);
        @endphp

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>Ejercicios de la sesión</strong>

                @if ($firstPendingExercise)
                    <a
                        href="{{ route('student.practice.exercises.show', [$session, $firstPendingExercise]) }}"
                        class="btn btn-light btn-sm"
                    >
                        Continuar práctica
                    </a>
                @else
                    <span class="badge bg-success">Todos respondidos</span>
                @endif
            </div>

            <div class="card-body">
                @if ($sessionExercises->isEmpty())
                    <div class="alert alert-warning mb-0">
                        No hay ejercicios asociados a esta sesión.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Subtema</th>
                                <th>Tipo</th>
                                <th>Nivel</th>
                                <th>Estado</th>
                                <th class="text-end">Acción</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($sessionExercises as $sessionExercise)
                                <tr>
                                    <td>{{ $sessionExercise->order }}</td>

                                    <td>
                                        {{ $sessionExercise->exercise->subtopic->name ?? 'Sin subtema' }}
                                    </td>

                                    <td>
                                        {{ $sessionExercise->exercise->type->name ?? 'Sin tipo' }}
                                    </td>

                                    <td>
                                        {{ $sessionExercise->exercise->level->name ?? 'Sin nivel' }}
                                    </td>

                                    <td>
                                        @if (is_null($sessionExercise->answered_at))
                                            <span class="badge bg-secondary">Pendiente</span>
                                        @elseif ($sessionExercise->is_correct)
                                            <span class="badge bg-success">Correcto</span>
                                        @else
                                            <span class="badge bg-danger">Incorrecto</span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        @if (is_null($sessionExercise->answered_at))
                                            <a
                                                href="{{ route('student.practice.exercises.show', [$session, $sessionExercise]) }}"
                                                class="btn btn-sm btn-primary"
                                            >
                                                Resolver
                                            </a>
                                        @else
                                            <a
                                                href="{{ route('student.practice.exercises.show', [$session, $sessionExercise]) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                            >
                                                Ver
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
