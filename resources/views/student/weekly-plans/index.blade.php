@extends('student.main')

@section('content')
<div class="container py-4" style="max-width: 980px;">
    {{-- Header --}}
    <div class="mb-4">
        <h1 class="h3 fw-semibold mb-2">Plan de estudio semanal</h1>
        @if($plan)
        <p class="text-muted mb-0">
            Semana del {{ \Carbon\Carbon::parse($plan->week_start)->locale('es')->isoFormat('D [de] MMMM') }}
            al {{ \Carbon\Carbon::parse($plan->week_start)->addDays(6)->locale('es')->isoFormat('D [de] MMMM') }}
            · {{ $plan->available_hours }} horas disponibles
        </p>
        @else
        <p class="text-muted mb-0">Organiza tus horas y genera una distribución semanal personalizada.</p>
        @endif
    </div>

    {{-- Formulario --}}
    <form action="{{ route('weekly-plans.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md">
                <label class="form-label small text-muted mb-1">
                    Horas disponibles por semana
                </label>
                <input type="number" name="available_hours"
                    class="form-control @error('available_hours') is-invalid @enderror" min="1" max="70"
                    value="{{ old('available_hours', $plan?->available_hours ?? '') }}" placeholder="ej. 10">
                @error('available_hours')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-dark w-100 px-4">
                    {{ $plan ? 'Regenerar plan' : 'Generar plan' }}
                </button>
            </div>
        </div>
    </form>

    @if(!$plan)
    <div class="alert alert-info mb-0">
        Ingresa tus horas disponibles para generar tu plan semanal.
    </div>
    @else

    {{-- Métricas --}}
    @php
    $totalExercises = $plan->planDays->sum('recommended_exercises');
    $totalMinutes = $plan->planDays->sum('estimated_minutes');
    $totalSubtopics = $plan->planDays->count();
    @endphp
    <div class="row g-2 mb-4">
        @foreach([
        ['Subtemas esta semana', $totalSubtopics],
        ['Ejercicios totales', $totalExercises],
        ['Minutos totales', $totalMinutes],
        ] as $m)
        <div class="col-12 col-md-4">
            <div class="bg-light rounded p-3 h-100">
                <p class="text-muted small mb-1">{{ $m[0] }}</p>
                <p class="fs-4 fw-semibold mb-0">{{ $m[1] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <hr class="my-4">

    <p class="small fw-semibold text-uppercase text-muted mb-3">
        Distribución por día
    </p>

    @php
    $dayNames = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
    5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $groupedDays = $plan->planDays->groupBy('weekday');
    $weekStart = \Carbon\Carbon::parse($plan->week_start);
    @endphp

    <div class="d-flex flex-column gap-3">
        @for($day = 1; $day <= 7; $day++) @php $dayItems=$groupedDays->get($day, collect());
            $dayDate = $weekStart->copy()->addDays($day - 1)->locale('es')->isoFormat('D MMM');
            @endphp

            <div class="border rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-semibold">{{ $dayNames[$day] }}</span>
                    <span class="text-muted small">{{ $dayDate }}</span>
                </div>

                @if($dayItems->isEmpty())
                <p class="text-muted small fst-italic mb-0">Sin subtemas asignados para este día.</p>
                @else
                <div class="d-flex flex-column gap-2">
                    @foreach($dayItems as $item)
                    <div
                        class="d-flex flex-column flex-md-row align-items-md-center gap-2 border rounded p-2 px-3 bg-light-subtle">
                        <span class="badge text-bg-light border text-secondary fw-normal small">
                            {{ $item->subtopic->topic->name }}
                        </span>
                        <span class="flex-grow-1">{{ $item->subtopic->name }}</span>
                        <span class="text-muted small">{{ $item->recommended_exercises }} ejercicios</span>
                        <span class="text-muted small">{{ $item->estimated_minutes }} min</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endfor
    </div>

    @endif
</div>
@endsection
