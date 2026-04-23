@extends('tutor.main')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Mis ejercicios</h1>
        <a href="{{ route('tutor.exercises.create') }}" class="btn btn-primary">
            Nuevo ejercicio
        </a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('duplicate_alerts'))
        <div class="alert alert-warning">
            <strong>Posibles ejercicios duplicados detectados:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('duplicate_alerts') as $duplicate)
                    <li>
                        Ejercicio #{{ $duplicate['exercise_id'] }} con similitud de {{ $duplicate['similarity'] }}%
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if($exercises->isEmpty())
                <p class="mb-0">Aún no has creado ejercicios.</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Módulo</th>
                            <th>Subtema</th>
                            <th>Nivel</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Última revisión</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($exercises as $exercise)
                            @php
                                $latestReview = $exercise->reviews->sortByDesc('reviewed_at')->first();
                            @endphp
                            <tr>
                                <td>{{ $exercise->id }}</td>
                                <td>{{ $exercise->subtopic->topic->name }}</td>
                                <td>{{ $exercise->subtopic->name }}</td>
                                <td>{{ $exercise->level->name }}</td>
                                <td>{{ $exercise->type->name }}</td>
                                <td>
                                    @if($exercise->status->name === 'Aprobado')
                                        <span class="badge bg-success">Aprobado</span>
                                    @elseif($exercise->status->name === 'En revisión')
                                        <span class="badge bg-warning text-dark">En revisión</span>
                                    @elseif($exercise->status->name === 'Borrador')
                                        <span class="badge bg-secondary">Borrador</span>
                                    @elseif($exercise->status->name === 'Publicado')
                                        <span class="badge bg-primary">Publicado</span>
                                    @else
                                        <span class="badge bg-dark">{{ $exercise->status->name }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latestReview)
                                        <div>
                                            <strong>{{ $latestReview->is_approved ? 'Aprobado' : 'Rechazado' }}</strong><br>
                                            <small
                                                class="text-muted">{{ $latestReview->reviewed_at?->format('d/m/Y H:i') }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">Sin revisión</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('tutor.exercises.show', $exercise) }}"
                                           class="btn btn-sm btn-success">
                                            Ver ejercicio
                                        </a>
                                        <a href="{{ route('tutor.exercises.reviews', $exercise) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            Ver revisiones
                                        </a>
                                        @if($exercise->status->name === 'Borrador')
                                            <a href="{{ route('tutor.exercises.edit', $exercise) }}"
                                               class="btn btn-sm btn-primary">
                                                Editar
                                            </a>
                                            <form action="{{ route('tutor.exercises.submitReview', $exercise) }}"
                                                  method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    Enviar a revisión
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
