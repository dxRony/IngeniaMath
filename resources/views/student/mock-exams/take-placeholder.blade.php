@php use App\Models\Exercise; @endphp
@extends('student.main')

@section('content')
    <div class="alert alert-info">
        <h4>Simulacro generado correctamente</h4>
        <p>ID del simulacro: <strong>{{ $mockExam->id }}</strong></p>
        <p>Iniciado: {{ $mockExam->started_at }}</p>
        <p>Total de preguntas: {{ $mockExam->exercises->count() }}</p>
        <hr>
        <p class="mb-0"><em>La pantalla del examen se construirá en la siguiente etapa.</em></p>
    </div>

    <h5>Preguntas seleccionadas (debug)</h5>
    <ol>
        @foreach ($mockExam->exercises->sortBy('order') as $examExercise)
            @php
                $exercise = Exercise::with('subtopic.topic')->find($examExercise->exercise_id);
            @endphp
            <li>
                <strong>{{ $exercise?->subtopic?->topic?->name ?? '?' }}</strong>
                → {{ $exercise?->subtopic?->name ?? '?' }}
                <br>
                <small>{{ Str::limit($exercise?->statement ?? '?', 100) }}</small>
            </li>
        @endforeach
    </ol>

    <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">Volver al dashboard</a>
@endsection
