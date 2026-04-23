@extends('tutor.main')

@section('content')
<div class="card shadow-sm mt-4">
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

                <p class="mb-0">
                    <strong>Comentario:</strong><br>
                    {{ $review->feedback ?: 'Sin observaciones.' }}
                </p>
            </div>
        @empty
            <p class="mb-0">Este ejercicio aún no tiene revisiones registradas.</p>
        @endforelse

            <a href="{{ route('tutor.exercises.index') }}" class="btn btn-secondary">Regresar</a>

    </div>
</div>
@endsection
