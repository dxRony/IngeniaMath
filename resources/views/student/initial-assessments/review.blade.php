@extends('student.main')

@section('content')
<div class="container py-4" style="max-width: 820px;">

    {{-- Topbar --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
        <h1 class="h3 fw-semibold mb-0">Revisión del diagnóstico</h1>
        <span class="small text-muted">
            Ejercicio {{ $order }} de {{ $total }}
        </span>
    </div>

    {{-- Barra de progreso --}}
    <div class="progress mb-4" style="height: 6px;">
        <div class="progress-bar bg-dark" role="progressbar" style="width: {{ round($order / $total * 100, 1) }}%;"></div>
    </div>

    {{-- Card del ejercicio --}}
    @php
    $isCorrect = $item->is_correct;
    $typeName = $item->exercise->type->name;
    $statusClass = $isCorrect ? 'text-bg-success' : ($item->given_answer ? 'text-bg-danger' : 'text-bg-secondary');
    $answerBoxClass = $isCorrect ? 'alert-success border-success' : 'alert-danger border-danger';
    @endphp

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

        {{-- Meta --}}
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="badge rounded-pill text-bg-light border text-secondary">
                {{ $item->exercise->subtopic->topic->name }}
            </span>
            <span class="badge rounded-pill text-bg-light border text-secondary">
                {{ $item->exercise->level->name }}
            </span>
            <span class="badge {{ $statusClass }} ms-md-auto">
                {{ $isCorrect ? 'Correcta' : ($item->given_answer ? 'Incorrecta' : 'Sin responder') }}
            </span>
        </div>

        {{-- Enunciado --}}
        <p class="fs-6 lh-lg mb-4">
            {!! $item->exercise->statement !!}
        </p>

        {{-- Imagen opcional --}}
        @if($item->exercise->image_url)
        <img src="{{ Storage::url($item->exercise->image_url) }}" alt="Imagen de apoyo"
            class="img-fluid rounded border mb-4" style="max-height: 200px; object-fit: contain; width: 100%;">
        @endif

        <hr class="my-4">

        {{-- Respuestas --}}
        <p class="small fw-semibold text-uppercase text-muted mb-2">Respuestas</p>

        <div class="row g-2 mb-3">
            {{-- Respuesta dada --}}
            <div class="col-12 col-lg-6">
                <div class="alert {{ $answerBoxClass }} mb-0 border">
                    <p class="small mb-1 opacity-75">Tu respuesta</p>
                    <p class="fw-semibold mb-0">
                    @if($item->given_answer)
                    @if($typeName === 'Completar espacios')
                    {{ implode(' / ', explode('|', $item->given_answer)) }}
                    @elseif($typeName === 'Verdadero/Falso')
                    {{ $item->given_answer === 'true' ? 'Verdadero' : 'Falso' }}
                    @else
                    {{ $item->given_answer }}
                    @endif
                    @else
                    <em>Sin responder</em>
                    @endif
                    </p>
                </div>
            </div>

            {{-- Respuesta correcta --}}
            @if(!$isCorrect)
            <div class="col-12 col-lg-6">
                <div class="alert alert-success border-success mb-0 border">
                    <p class="small mb-1 opacity-75">Respuesta correcta</p>
                    <p class="fw-semibold mb-0">
                        @if($typeName === 'Completar espacios')
                        {{ implode(' / ', explode('|', $item->exercise->correct_answer)) }}
                        @else
                        {{ $item->exercise->correct_answer }}
                        @endif
                    </p>
                </div>
            </div>
            @endif
        </div>

        {{-- Explicación conceptual --}}
        @if($item->exercise->concept_explanation)
        <hr class="my-4">
        <p class="small fw-semibold text-uppercase text-muted mb-2">Explicación conceptual</p>
        <p class="text-muted lh-lg mb-3">
            {!! $item->exercise->concept_explanation !!}
        </p>
        @endif

        {{-- Pasos de solución --}}
        @if($item->exercise->steps->isNotEmpty())
        <hr class="my-4">
        <p class="small fw-semibold text-uppercase text-muted mb-2">Solución paso a paso</p>

        <div class="d-flex flex-column gap-3">
            @foreach($item->exercise->steps->sortBy('step_number') as $step)
            <div class="d-flex gap-2">
                <div class="rounded-circle border bg-light text-muted d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width: 24px; height: 24px; font-size: 12px;">
                    {{ $step->step_number }}
                </div>
                <div class="flex-grow-1">
                    <p class="mb-2 lh-lg">
                        {!! $step->description !!}
                    </p>
                    @if($step->image_url)
                    <img src="{{ Storage::url($step->image_url) }}" alt="Paso {{ $step->step_number }}"
                      class="img-fluid rounded border" style="max-height: 160px; object-fit: contain; width: 100%;">
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        </div>
    </div>

    {{-- Navegación --}}
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
        @if($order > 1)
        <a href="{{ route('initial-assessments.review', [$assessment->id, $order - 1]) }}" class="btn btn-outline-secondary">
            Anterior
        </a>
        @else
        <a href="{{ route('initial-assessments.results') }}" class="btn btn-outline-secondary">
            Ver resultados
        </a>
        @endif

        @if($order < $total)
            <a href="{{ route('initial-assessments.review', [$assessment->id, $order + 1]) }}" class="btn btn-dark">
            Siguiente
            </a>
        @else
            <a href="{{ route('initial-assessments.results') }}" class="btn btn-dark">
                Ver resultados
            </a>
        @endif
    </div>

</div>
@endsection
