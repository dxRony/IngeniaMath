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
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($exercises as $exercise)
                            <tr>
                                <td>{{ $exercise->id }}</td>
                                <td>{{ $exercise->subtopic->topic->name }}</td>
                                <td>{{ $exercise->subtopic->name }}</td>
                                <td>{{ $exercise->level->name }}</td>
                                <td>{{ $exercise->type->name }}</td>
                                <td>{{ $exercise->status->name }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @if($exercise->status->name === 'Borrador')
                                            <a href="{{ route('tutor.exercises.edit', $exercise) }}" class="btn btn-sm btn-primary">
                                                Editar
                                            </a>

                                            <form action="{{ route('tutor.exercises.submitReview', $exercise) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    Enviar a revisión
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">Sin acciones</span>
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
