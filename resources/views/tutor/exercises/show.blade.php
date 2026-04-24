@extends('tutor.main')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Detalle de ejercicio #{{ $exercise->id }}</h1>
            <p class="text-muted mb-0">Estado actual: {{ $exercise->status->name }}</p>
        </div>

        <a href="{{ route('tutor.exercises.index') }}" class="btn btn-secondary">
            ← Volver a la lista
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="fw-bold mb-3">Información general</h4>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <p class="mb-1"><strong>Módulo:</strong> {{ $exercise->subtopic->topic->name }}</p>
                </div>
                <div class="col-md-6 mb-2">
                    <p class="mb-1"><strong>Subtema:</strong> {{ $exercise->subtopic->name }}</p>
                </div>
                <div class="col-md-6 mb-2">
                    <p class="mb-1"><strong>Nivel:</strong> {{ $exercise->level->name }}</p>
                </div>
                <div class="col-md-6 mb-2">
                    <p class="mb-1"><strong>Tipo:</strong> {{ $exercise->type->name }}</p>
                </div>
                <div class="col-md-6 mb-2">
                    <p class="mb-1"><strong>Tiempo estimado:</strong> {{ $exercise->estimated_minutes }} min</p>
                </div>
            </div>

            <hr>

            <h5 class="fw-bold">Enunciado</h5>
            <div class="border rounded p-3 bg-light mb-3 math-content">
                {!! nl2br(e($exercise->statement)) !!}
            </div>

            @if($exercise->image_url)
                <div class="mb-3">
                    <h5 class="fw-bold">Imagen del ejercicio</h5>
                    <img
                        src="{{ asset('storage/' . $exercise->image_url) }}"
                        alt="Imagen del ejercicio"
                        class="img-fluid rounded border"
                        style="max-width: 400px;"
                    >
                </div>
            @endif

            <div class="mt-3">
                <h5 class="fw-bold">Respuesta correcta</h5>
                <div class="border rounded p-3 bg-light math-content">
                    {!! nl2br(e($exercise->correct_answer)) !!}
                </div>
            </div>

            @if(!empty($exercise->concept_explanation))
                <div class="mt-3">
                    <h5 class="fw-bold">Explicación conceptual</h5>
                    <div class="border rounded p-3 bg-light math-content">
                        {!! nl2br(e($exercise->concept_explanation)) !!}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($exercise->type->name === 'Opción múltiple')
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h4 class="fw-bold mb-3">Opciones de respuesta</h4>

                @if($exercise->multipleChoiceOptions->isEmpty())
                    <p class="mb-0">Este ejercicio no tiene opciones registradas.</p>
                @else
                    @foreach($exercise->multipleChoiceOptions->sortBy('order') as $option)
                        <div class="border rounded p-3 mb-3 {{ $option->is_correct ? 'border-success bg-light' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Opción {{ $option->order }}</strong>
                                    <div class="mb-0 mt-1 math-content">
                                        {!! nl2br(e($option->option_text)) !!}
                                    </div>
                                </div>

                                @if($option->is_correct)
                                    <span class="badge bg-success">Correcta</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="fw-bold mb-3">Solución paso a paso</h4>

            @forelse($exercise->steps->sortBy('step_number') as $step)
                <div class="border rounded p-3 mb-3">
                    <h6 class="fw-bold">Paso {{ $step->step_number }}</h6>
                    <div class="math-content">
                        {!! nl2br(e($step->description)) !!}
                    </div>

                    @if($step->image_url)
                        <div class="mt-2">
                            <img
                                src="{{ asset('storage/' . $step->image_url) }}"
                                alt="Imagen del paso {{ $step->step_number }}"
                                class="img-fluid rounded border"
                                style="max-width: 350px;"
                            >
                        </div>
                    @endif
                </div>
            @empty
                <p class="mb-0">Este ejercicio no tiene pasos registrados.</p>
            @endforelse
        </div>
    </div>
@endsection
