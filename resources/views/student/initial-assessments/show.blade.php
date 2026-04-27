@extends('layouts.assessment')

@section('content')
<div class="container py-4" style="max-width: 820px;">

    {{-- Barra de progreso --}}
    @php $current = $assessmentExercises->search(fn($i) => is_null($i->given_answer)) + 1 ??
    $assessmentExercises->count(); @endphp
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div class="w-100" style="max-width: 420px;">
            <p class="small text-muted mb-2">
                Pregunta <span id="js-current">1</span> de {{ $assessmentExercises->count() }}
            </p>
            <div class="progress" style="height: 6px;">
                <div id="js-bar" class="progress-bar bg-dark" role="progressbar"
                    style="width: {{ round(1 / $assessmentExercises->count() * 100, 1) }}%; transition: width 0.3s;"></div>
            </div>
        </div>
        <span id="js-remaining" class="small text-muted">
            {{ $assessmentExercises->count() - 1 }} restantes
        </span>
    </div>

    {{-- Ejercicios (uno visible a la vez) --}}
    <form id="assessment-form" action="{{ route('initial-assessments.update', $assessment->id) }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($assessmentExercises as $index => $item)
        <div class="exercise-pane" id="pane-{{ $index }}" style="{{ $index > 0 ? 'display:none;' : '' }}">

            {{-- Card del ejercicio --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                {{-- Badges: módulo y dificultad --}}
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge rounded-pill text-bg-light border text-secondary">
                        {{ $item->exercise->subtopic->topic->name }}
                    </span>
                    <span class="badge rounded-pill text-bg-light border text-secondary">
                        {{ $item->exercise->level->name }}
                    </span>
                </div>

                {{-- Enunciado --}}
                <p class="fs-6 lh-lg mb-4">
                    {!! $item->exercise->statement !!}
                </p>

                {{-- Imagen opcional --}}
                @if($item->exercise->image_url)
                <img src="{{ Storage::url($item->exercise->image_url) }}" alt="Imagen de apoyo"
                    class="img-fluid rounded border mb-4" style="max-height: 220px; object-fit: contain; width: 100%;">
                @endif

                {{-- Input según tipo --}}
                @php $typeName = $item->exercise->type->name; @endphp

                @if($typeName === 'Opción múltiple')
                @include('student.initial-assessments.partials._multiple_choice', [
                'item' => $item, 'index' => $index
                ])
                @elseif($typeName === 'Verdadero/Falso')
                @include('student.initial-assessments.partials._true_false', [
                'item' => $item, 'index' => $index
                ])
                @elseif($typeName === 'Respuesta numérica abierta')
                @include('student.initial-assessments.partials._numeric', [
                'item' => $item, 'index' => $index
                ])
                @elseif($typeName === 'Completar espacios')
                @include('student.initial-assessments.partials._fill_blank', [
                'item' => $item, 'index' => $index
                ])
                @endif
                </div>

            </div>
        </div>
        @endforeach

        {{-- Navegación --}}
        <div class="d-flex justify-content-end gap-2">
            <button type="button" id="btn-prev" onclick="navigate(-1)" class="btn btn-outline-secondary"
                style="display: none;">
                Anterior
            </button>
            <button type="button" id="btn-next" onclick="navigate(1)" class="btn btn-dark">
                Siguiente
            </button>
            <button type="submit" id="btn-submit" class="btn btn-dark" style="display: none;">
                Finalizar diagnóstico
            </button>
        </div>

    </form>
</div>

<script>
  const total = {{ $assessmentExercises->count() }};
  let current = 0;

  function navigate(dir) {
    document.getElementById('pane-' + current).style.display = 'none';
    current = Math.min(Math.max(0, current + dir), total - 1);
    document.getElementById('pane-' + current).style.display = 'block';
    updateUI();
  }

  function updateUI() {
    document.getElementById('js-current').textContent = current + 1;
    document.getElementById('js-remaining').textContent = (total - current - 1) + ' restantes';
    document.getElementById('js-bar').style.width = (((current + 1) / total) * 100).toFixed(1) + '%';
    document.getElementById('btn-prev').style.display  = current === 0 ? 'none' : 'inline-block';
    document.getElementById('btn-next').style.display  = current === total - 1 ? 'none' : 'inline-block';
    document.getElementById('btn-submit').style.display = current === total - 1 ? 'inline-block' : 'none';
  }

  updateUI();
</script>

@endsection
