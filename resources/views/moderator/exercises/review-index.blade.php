@extends('moderator.main')

@section('content')
    <h1 class="fw-bold mb-4">Ejercicios en revisión</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card shadow-sm">
        <div class="card-body">
            @if($exercises->isEmpty())
                <p class="mb-0">No hay ejercicios pendientes de revisión.</p>
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
                            <th>Creador</th>
                            <th></th>
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
                                <td>{{ $exercise->creator->first_name }} {{ $exercise->creator->last_name }}</td>
                                <td>
                                    <a href="{{ route('moderator.exercises.review.show', $exercise) }}" class="btn btn-sm btn-primary">
                                        Ver detalle
                                    </a>
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
