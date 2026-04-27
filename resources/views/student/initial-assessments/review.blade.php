@extends('student.main')

@section('content')
<div style="max-width: 720px; margin: 0 auto; padding: 2.5rem 1.5rem;">

    {{-- Topbar --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <h1 style="font-size: 22px; font-weight: 500; margin: 0;">Revisión del diagnóstico</h1>
        <span style="font-size: 13px; color: #6b7280;">
            Ejercicio {{ $order }} de {{ $total }}
        </span>
    </div>

    {{-- Barra de progreso --}}
    <div style="height: 4px; background: #f3f4f6; border-radius: 2px; overflow: hidden; margin-bottom: 2rem;">
        <div style="height: 100%; width: {{ round($order / $total * 100, 1) }}%;
                background: #111827; border-radius: 2px;"></div>
    </div>

    {{-- Card del ejercicio --}}
    @php
    $isCorrect = $item->is_correct;
    $typeName = $item->exercise->type->name;
    @endphp

    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
              padding: 1.5rem; margin-bottom: 1.5rem;">

        {{-- Meta --}}
        <div style="display: flex; gap: 8px; margin-bottom: 1rem; align-items: center;">
            <span style="font-size: 12px; padding: 3px 10px; border-radius: 20px; background: #f3f4f6; color: #6b7280;">
                {{ $item->exercise->subtopic->topic->name }}
            </span>
            <span style="font-size: 12px; padding: 3px 10px; border-radius: 20px; background: #f3f4f6; color: #6b7280;">
                {{ $item->exercise->level->name }}
            </span>
            <span style="font-size: 12px; padding: 3px 10px; border-radius: 20px; margin-left: auto;
                   background: {{ $isCorrect ? '#EAF3DE' : '#FCEBEB' }};
                   color: {{ $isCorrect ? '#3B6D11' : '#A32D2D' }};">
                {{ $isCorrect ? 'Correcta' : ($item->given_answer ? 'Incorrecta' : 'Sin responder') }}
            </span>
        </div>

        {{-- Enunciado --}}
        <p style="font-size: 16px; line-height: 1.6; margin: 0 0 1.25rem;">
            {!! $item->exercise->statement !!}
        </p>

        {{-- Imagen opcional --}}
        @if($item->exercise->image_url)
        <img src="{{ Storage::url($item->exercise->image_url) }}" alt="Imagen de apoyo" style="width: 100%; max-height: 200px; object-fit: contain;
                border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 1.25rem;">
        @endif

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 1.25rem 0;">

        {{-- Respuestas --}}
        <p style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase;
              letter-spacing: 0.05em; margin: 0 0 8px;">Respuestas</p>

        <div style="display: flex; gap: 12px; margin-bottom: 1rem;">
            {{-- Respuesta dada --}}
            <div style="flex: 1; padding: 0.625rem 0.875rem; border-radius: 8px;
                  background: {{ $isCorrect ? '#EAF3DE' : '#FCEBEB' }};
                  border: 0.5px solid {{ $isCorrect ? '#97C459' : '#F09595' }};">
                <p style="font-size: 11px; margin: 0 0 3px; opacity: 0.7;
                  color: {{ $isCorrect ? '#3B6D11' : '#A32D2D' }};">Tu respuesta</p>
                <p style="font-size: 14px; font-weight: 500; margin: 0;
                  color: {{ $isCorrect ? '#3B6D11' : '#A32D2D' }};">
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

            {{-- Respuesta correcta --}}
            @if(!$isCorrect)
            <div style="flex: 1; padding: 0.625rem 0.875rem; border-radius: 8px;
                  background: #EAF3DE; border: 0.5px solid #97C459;">
                <p style="font-size: 11px; margin: 0 0 3px; opacity: 0.7; color: #3B6D11;">
                    Respuesta correcta
                </p>
                <p style="font-size: 14px; font-weight: 500; margin: 0; color: #3B6D11;">
                    @if($typeName === 'Completar espacios')
                    {{ implode(' / ', explode('|', $item->exercise->correct_answer)) }}
                    @else
                    {{ $item->exercise->correct_answer }}
                    @endif
                </p>
            </div>
            @endif
        </div>

        {{-- Explicación conceptual --}}
        @if($item->exercise->concept_explanation)
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 1.25rem 0;">
        <p style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase;
              letter-spacing: 0.05em; margin: 0 0 8px;">Explicación conceptual</p>
        <p style="font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0 0 1rem;">
            {!! $item->exercise->concept_explanation !!}
        </p>
        @endif

        {{-- Pasos de solución --}}
        @if($item->exercise->steps->isNotEmpty())
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 1.25rem 0;">
        <p style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase;
              letter-spacing: 0.05em; margin: 0 0 8px;">Solución paso a paso</p>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($item->exercise->steps->sortBy('step_number') as $step)
            <div style="display: flex; gap: 12px;">
                <div style="width: 24px; height: 24px; border-radius: 50%; background: #f3f4f6;
                    border: 1px solid #e5e7eb; font-size: 12px; font-weight: 500;
                    color: #6b7280; display: flex; align-items: center; justify-content: center;
                    flex-shrink: 0; margin-top: 1px;">
                    {{ $step->step_number }}
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 14px; line-height: 1.6; margin: 0 0 6px;">
                        {!! $step->description !!}
                    </p>
                    @if($step->image_url)
                    <img src="{{ Storage::url($step->image_url) }}" alt="Paso {{ $step->step_number }}" style="width: 100%; max-height: 160px; object-fit: contain;
                      border-radius: 6px; border: 1px solid #e5e7eb;">
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>

    {{-- Navegación --}}
    <div style="display: flex; justify-content: space-between; align-items: center;">
        @if($order > 1)
        <a href="{{ route('initial-assessments.review', [$assessment->id, $order - 1]) }}" style="padding: 0.625rem 1.5rem; border-radius: 8px; font-size: 15px; font-weight: 500;
              border: 1px solid #d1d5db; background: transparent; text-decoration: none;
              color: #111827;">
            Anterior
        </a>
        @else
        <a href="{{ route('initial-assessments.results') }}" style="padding: 0.625rem 1.5rem; border-radius: 8px; font-size: 15px; font-weight: 500;
              border: 1px solid #d1d5db; background: transparent; text-decoration: none;
              color: #111827;">
            Ver resultados
        </a>
        @endif

        @if($order < $total) <a href="{{ route('initial-assessments.review', [$assessment->id, $order + 1]) }}" style="padding: 0.625rem 1.5rem; border-radius: 8px; font-size: 15px; font-weight: 500;
              background: #111827; color: #fff; border: none; text-decoration: none;">
            Siguiente
            </a>
            @else
            <a href="{{ route('initial-assessments.results') }}" style="padding: 0.625rem 1.5rem; border-radius: 8px; font-size: 15px; font-weight: 500;
              background: #111827; color: #fff; border: none; text-decoration: none;">
                Ver resultados
            </a>
            @endif
    </div>

</div>
@endsection
