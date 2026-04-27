@extends('moderator.main')

@section('content')
    <div class="mb-4">
        <h1 class="fw-bold text-primary mb-1">Bienvenido, Moderador</h1>
        <p class="text-muted mb-0">Panel de revisión y supervisión del contenido de la plataforma.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="mb-4">
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <small class="text-muted d-block mb-1">Ejercicios en revisión</small>
                        <h2 class="fw-bold text-primary mb-0">{{ $stats['pendingExercises'] }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <small class="text-muted d-block mb-1">Recursos pendientes</small>
                        <h2 class="fw-bold text-success mb-0">{{ $stats['pendingResources'] }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <small class="text-muted d-block mb-1">Alertas de duplicado</small>
                        <h2 class="fw-bold text-danger mb-0">{{ $stats['pendingDuplicates'] }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <small class="text-muted d-block mb-1">Hilos abiertos</small>
                        <h2 class="fw-bold text-warning mb-0">{{ $stats['openThreads'] }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-light border-0">
                        <strong>Revisión de ejercicios</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Gestiona ejercicios nuevos, rechazos y alertas de duplicado.</p>
                        <a href="{{ route('moderator.exercises.review.index') }}" class="btn btn-outline-primary btn-sm">Ir a revisión</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-light border-0">
                        <strong>Revisión de recursos</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Aprueba o rechaza recursos educativos pendientes.</p>
                        <a href="{{ route('moderator.resources.index') }}" class="btn btn-outline-success btn-sm">Ver recursos</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-light border-0">
                        <strong>Ejercicios en espera</strong>
                    </div>
                    <div class="card-body">
                        @forelse($pendingExercises as $exercise)
                            <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                                <div>
                                    <div class="fw-medium">{{ $exercise->subtopic?->topic?->name ?? 'Sin módulo' }}</div>
                                    <small class="text-muted">{{ $exercise->subtopic?->name ?? 'Sin subtema' }}</small>
                                </div>
                                <a href="{{ route('moderator.exercises.review.show', $exercise) }}" class="btn btn-sm btn-outline-primary">Revisar</a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No hay ejercicios pendientes de revisión.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-light border-0">
                        <strong>Recursos pendientes</strong>
                    </div>
                    <div class="card-body">
                        @forelse($pendingResources as $resource)
                            <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                                <div>
                                    <div class="fw-medium">{{ $resource->title }}</div>
                                    <small class="text-muted">{{ $resource->type?->name ?? 'Sin tipo' }}</small>
                                </div>
                                <a href="{{ route('moderator.resources.show', $resource->id) }}" class="btn btn-sm btn-outline-success">Revisar</a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No hay recursos pendientes de revisión.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
