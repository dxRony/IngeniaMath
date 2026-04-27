@extends('student.main')

@section('content')
<div class="container py-4" style="max-width: 820px;">

    {{-- Header --}}
    <div class="mb-4">
        <h1 class="h3 fw-semibold mb-2">Resultados del diagnóstico</h1>
        <p class="text-muted mb-0">
            Aquí está tu nivel de dominio actual por módulo. Tu ruta de aprendizaje ha sido generada.
        </p>
    </div>

    {{-- Métricas --}}
    @php
    $dominado = $moduleResults->where('masteryLevel.name', 'Dominado')->count();
    $enDesarrollo = $moduleResults->where('masteryLevel.name', 'En desarrollo')->count();
    $deficiente = $moduleResults->where('masteryLevel.name', 'Deficiente')->count();
    @endphp

    <div class="row g-2 mb-4">
        @foreach([
        ['Respondidas correctamente', $totalCorrect . ' / ' . $totalExercises],
        ['Módulos dominados', $dominado],
        ['En desarrollo', $enDesarrollo],
        ['Deficientes', $deficiente],
        ] as $metric)
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="bg-light rounded p-3 h-100">
                <p class="small text-muted mb-1">{{ $metric[0] }}</p>
                <p class="fs-4 fw-semibold mb-0">{{ $metric[1] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <hr class="my-4">

    {{-- Detalle por módulo --}}
    <p class="small fw-semibold text-uppercase text-muted mb-3">Detalle por módulo</p>

    <div class="d-flex flex-column gap-2 mb-4">
        @foreach($moduleResults as $result)
        @php
        $name = $result->masteryLevel->name;
        $score = (int) $result->score;
        $badgeClass = match($name) {
            'Dominado' => 'text-bg-success',
            'En desarrollo' => 'text-bg-warning',
            default => 'text-bg-danger'
        };
        $barClass = match($name) {
            'Dominado' => 'bg-success',
            'En desarrollo' => 'bg-warning',
            default => 'bg-danger'
        };
        @endphp
        <div class="border rounded p-3 bg-white">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                <span class="flex-grow-1">{{ $result->topic->name }}</span>

                <div class="w-100" style="max-width: 220px;">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $score }}%;"></div>
                    </div>
                </div>

                <span class="fw-semibold" style="min-width: 52px;">{{ $score }}%</span>

                <span class="badge {{ $badgeClass }}" style="min-width: 110px;">
                    {{ $name }}
                </span>
            </div>
        </div>
        @endforeach
    </div>

    <hr class="my-4">

    {{-- Footer --}}
    <div class="d-flex flex-column flex-md-row justify-content-end gap-2">
        <a href="{{ route('learning-paths.index') }}" class="btn btn-dark">
            Ver ruta de aprendizaje generada
        </a>
        <a href="{{ route('initial-assessments.review', [$assessment->id, 1]) }}" class="btn btn-outline-secondary">
            Revisar ejercicios
        </a>
    </div>

</div>
@endsection
