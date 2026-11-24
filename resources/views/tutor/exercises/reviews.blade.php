@extends('tutor.main')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Revisiones de ejercicio #{{ $exercise->id }}</h1>
            <p class="text-muted mb-0">Estado actual: {{ $exercise->status->name }}</p>
        </div>

        <a href="{{ route('tutor.exercises.index') }}" class="btn btn-secondary">
            ← Volver a la lista
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="fw-bold mb-3">Historial de revisiones</h4>

            @forelse($exercise->reviews->sortByDesc('reviewed_at') as $review)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>
                            {{ $review->is_approved ? 'Aprobado' : 'Rechazado' }}
                        </strong>
                        <small class="text-muted">
                            {{ $review->reviewed_at?->format('d/m/Y H:i') }}
                        </small>
                    </div>

                    <p class="mb-1">
                        <strong>Revisor:</strong>
                        {{ $review->reviewer->first_name }} {{ $review->reviewer->last_name }}
                    </p>

                    <div class="math-content">
                        <strong>Comentario:</strong><br>
                        {!! nl2br(e($review->feedback ?: 'Sin observaciones.')) !!}
                    </div>
                </div>
            @empty
                <p class="mb-0">Este ejercicio aún no tiene revisiones registradas.</p>
            @endforelse
        </div>
    </div>

@endsection
