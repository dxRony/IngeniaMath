@extends('layouts.app')

@section('content')
<div style="max-width: 720px; margin: 0 auto; padding: 2rem 1.5rem;">

    {{-- Barra de progreso --}}
    @php $current = $assessmentExercises->search(fn($i) => is_null($i->given_answer)) + 1 ??
    $assessmentExercises->count(); @endphp
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <div style="flex: 1; max-width: 320px;">
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 6px;">
                Pregunta <span id="js-current">1</span> de {{ $assessmentExercises->count() }}
            </p>
            <div style="height: 4px; background: #f3f4f6; border-radius: 2px; overflow: hidden;">
                <div id="js-bar" style="height: 100%; background: #111827; border-radius: 2px;
                                width: {{ round(1 / $assessmentExercises->count() * 100, 1) }}%;
                                transition: width 0.3s;"></div>
            </div>
        </div>
        <span id="js-remaining" style="font-size: 13px; color: #6b7280;">
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
            <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
                  padding: 1.5rem; margin-bottom: 1.5rem;">

                {{-- Badges: módulo y dificultad --}}
                <div style="display: flex; gap: 8px; margin-bottom: 1rem;">
                    <span style="font-size: 12px; padding: 3px 10px; border-radius: 20px;
                       background: #f3f4f6; color: #6b7280;">
                        {{ $item->exercise->subtopic->topic->name }}
                    </span>
                    <span style="font-size: 12px; padding: 3px 10px; border-radius: 20px;
                       background: #f3f4f6; color: #6b7280;">
                        {{ $item->exercise->level->name }}
                    </span>
                </div>

                {{-- Enunciado --}}
                <p style="font-size: 16px; line-height: 1.6; margin: 0 0 1.25rem;">
                    {!! $item->exercise->statement !!}
                </p>

                {{-- Imagen opcional --}}
                @if($item->exercise->image_url)
                <img src="{{ Storage::url($item->exercise->image_url) }}" alt="Imagen de apoyo" style="width: 100%; max-height: 220px; object-fit: contain;
                    border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 1.25rem;">
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
        @endforeach

        {{-- Navegación --}}
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" id="btn-prev" onclick="navigate(-1)" style="padding: 0.625rem 1.5rem; border-radius: 8px; font-size: 15px;
                     font-weight: 500; border: 1px solid #d1d5db; background: transparent;
                     cursor: pointer; display: none;">
                Anterior
            </button>
            <button type="button" id="btn-next" onclick="navigate(1)" style="padding: 0.625rem 1.5rem; border-radius: 8px; font-size: 15px;
                     font-weight: 500; background: #111827; color: #fff;
                     border: none; cursor: pointer;">
                Siguiente
            </button>
            <button type="submit" id="btn-submit" style="padding: 0.625rem 1.5rem; border-radius: 8px; font-size: 15px;
                     font-weight: 500; background: #111827; color: #fff;
                     border: none; cursor: pointer; display: none;">
                Finalizar diagnóstico
            </button>
        </div>

    </form>
</div>

{{-- @push('scripts')
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
@endpush --}}
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
