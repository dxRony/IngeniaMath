@extends('moderator.main')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Alerta de duplicado #{{ $alert->id }}</h1>
            <p class="text-muted mb-0">
                Similitud detectada: {{ $alert->similarity }}%
            </p>
        </div>

        <a href="{{ route('moderator.exercises.review.index') }}" class="btn btn-secondary">
            ← Volver a la lista
        </a>
    </div>

    <div class="alert alert-warning">
        Revisa ambos ejercicios. Si consideras que son duplicados:
        puedes rechazar el ejercicio nuevo o marcar la alerta como resuelta.
    </div>

    <div class="row g-4">
        {{-- nuevo ejercicio--}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Ejercicio nuevo #{{ $alert->newExercise->id }}</h4>

                    <p><strong>Estado:</strong> {{ $alert->newExercise->status->name }}</p>
                    <p>
                        <strong>Creador:</strong>
                        {{ $alert->newExercise->creator->first_name }}
                        {{ $alert->newExercise->creator->last_name }}
                    </p>

                    <p><strong>Módulo:</strong> {{ $alert->newExercise->subtopic->topic->name }}</p>
                    <p><strong>Subtema:</strong> {{ $alert->newExercise->subtopic->name }}</p>
                    <p><strong>Tipo:</strong> {{ $alert->newExercise->type->name }}</p>
                    <p><strong>Nivel:</strong> {{ $alert->newExercise->level->name }}</p>

                    <hr>

                    <p><strong>Enunciado:</strong></p>
                    <div class="border rounded p-3 bg-light math-content">
                        {!! nl2br(e($alert->newExercise->statement)) !!}
                    </div>
                </div>
            </div>
        </div>

        {{-- ejercicio existente --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Ejercicio existente #{{ $alert->existingExercise->id }}</h4>

                    <p><strong>Estado:</strong> {{ $alert->existingExercise->status->name }}</p>

                    <p><strong>Módulo:</strong> {{ $alert->existingExercise->subtopic->topic->name }}</p>
                    <p><strong>Subtema:</strong> {{ $alert->existingExercise->subtopic->name }}</p>
                    <p><strong>Tipo:</strong> {{ $alert->existingExercise->type->name }}</p>
                    <p><strong>Nivel:</strong> {{ $alert->existingExercise->level->name }}</p>

                    <hr>

                    <p><strong>Enunciado:</strong></p>
                    <div class="border rounded p-3 bg-light math-content">
                        {!! nl2br(e($alert->existingExercise->statement)) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- acciones --}}
    @if(!$alert->is_resolved)
        <div class="card shadow-sm mt-4">
            <div class="card-body">

                <div class="d-flex gap-2 flex-wrap">
                    {{-- resolver alerta --}}
                    <form action="{{ route('moderator.duplicate-alerts.resolve', $alert) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success">
                            Marcar alerta como resuelta
                        </button>
                    </form>

                    {{-- rechazar ejercicio nuevo --}}
                    @if($alert->newExercise->status->name === 'En revisión')
                        <form action="{{ route('moderator.duplicate-alerts.reject-new-exercise', $alert) }}"
                              method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger">
                                Rechazar ejercicio nuevo
                            </button>
                        </form>
                    @endif
                </div>

                <small class="text-muted d-block mt-2">
                    Rechazar el ejercicio lo regresará a borrador y resolverá esta alerta automáticamente.
                </small>
            </div>
        </div>
    @else
        <div class="alert alert-success mt-4">
            Esta alerta ya fue marcada como resuelta.
        </div>
    @endif
@endsection
