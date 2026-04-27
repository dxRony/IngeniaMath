@extends('student.main')

@section('content')
<div class="container py-4" style="max-width: 820px;">

    {{-- Header --}}
    <div class="mb-4">
        <h1 class="h3 fw-semibold mb-2">Mi ruta de aprendizaje</h1>
        <p class="text-muted mb-0">
            Subtemas ordenados por prioridad y complejidad según tu diagnóstico inicial.
        </p>
    </div>

    @if(!$path)
    <div class="alert alert-info mb-0">
        Aún no tienes una ruta generada. Completa el diagnóstico inicial primero.
    </div>
    @else

    {{-- Métricas --}}
    <div class="row g-2 mb-4">
        @foreach([
        ['Total de subtemas', $total],
        ['Completados', $completed],
        ['Pendientes', $pending],
        ] as $m)
        <div class="col-12 col-md-4">
            <div class="bg-light rounded p-3 h-100">
                <p class="small text-muted mb-1">{{ $m[0] }}</p>
                <p class="fs-4 fw-semibold mb-0">{{ $m[1] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <hr class="my-4">

    <p class="small fw-semibold text-uppercase text-muted mb-3">Subtemas a trabajar</p>

    {{-- Items agrupados por módulo --}}
    @foreach($groupedItems as $topicName => $items)
    @php
    $masteryName = $items->first()->masteryLevel->name;
    $badgeClass = match($masteryName) {
        'Deficiente' => 'text-bg-danger',
        default => 'text-bg-warning'
    };
    @endphp

    <div class="mb-4">
        {{-- Header del módulo --}}
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="fw-semibold">{{ $topicName }}</span>
            <span class="badge {{ $badgeClass }}">
                {{ $masteryName }}
            </span>
        </div>

        {{-- Items del módulo --}}
        <div class="d-flex flex-column gap-2">
            @foreach($items as $item)
            <div class="d-flex align-items-center gap-2 border rounded p-2 px-3 bg-white {{ $item->is_completed ? 'opacity-50' : '' }}">

                {{-- Círculo check --}}
                @if($item->is_completed)
                <span class="badge rounded-pill text-bg-success" style="width: 16px; height: 16px; padding: 0;"></span>
                @else
                <span class="rounded-circle border d-inline-block" style="width: 16px; height: 16px;"></span>
                @endif

                <span class="small text-muted" style="min-width: 24px;">
                    {{ $item->order }}
                </span>

                <span class="flex-grow-1">
                    {{ $item->subtopic->name }}
                </span>

                <span class="small text-muted">
                    {{ $item->subtopic->complexity->name }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    @endif
</div>
@endsection
