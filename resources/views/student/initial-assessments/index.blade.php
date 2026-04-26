{{-- resources/views/student/initial-assessments/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div style="max-width: 720px; margin: 0 auto; padding: 3rem 1.5rem;">

  {{-- Header --}}
  <div style="margin-bottom: 2.5rem;">
    <span style="display: inline-block; font-size: 12px; font-weight: 500; padding: 4px 12px;
                 border-radius: 20px; background: var(--color-background-info);
                 color: var(--color-text-info); margin-bottom: 1rem;">
      Paso obligatorio
    </span>
    <h1 style="font-size: 28px; font-weight: 500; margin: 0 0 0.5rem;">
      Diagnóstico inicial
    </h1>
    <p style="font-size: 15px; color: #6b7280; line-height: 1.6; margin: 0;">
      Antes de comenzar tu preparación, necesitamos conocer tu nivel actual.
      Este diagnóstico nos permitirá generar una ruta de estudio personalizada para ti.
    </p>
  </div>

  {{-- Métricas --}}
  <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 2rem;">
    @foreach([['Preguntas', '28'], ['Duración estimada', '~35 min'], ['Módulos evaluados', '7']] as $metric)
    <div style="background: #f9fafb; border-radius: 8px; padding: 0.875rem 1rem;">
      <p style="font-size: 12px; color: #6b7280; margin: 0 0 4px;">{{ $metric[0] }}</p>
      <p style="font-size: 20px; font-weight: 500; margin: 0;">{{ $metric[1] }}</p>
    </div>
    @endforeach
  </div>

  <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

  {{-- Temas --}}
  <p style="font-size: 13px; font-weight: 500; color: #6b7280; margin: 0 0 0.75rem;
            text-transform: uppercase; letter-spacing: 0.05em;">
    Temas del diagnóstico
  </p>
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; margin-bottom: 2rem;">
    @foreach($topics as $topic)
    <div style="display: flex; align-items: center; gap: 10px; border: 1px solid #e5e7eb;
                border-radius: 8px; padding: 0.625rem 0.875rem;">
      <span style="font-size: 12px; font-weight: 500; color: #9ca3af; min-width: 18px;">
        {{ str_pad($topic->order, 2, '0', STR_PAD_LEFT) }}
      </span>
      <span style="font-size: 13px;">{{ $topic->name }}</span>
    </div>
    @endforeach
  </div>

  <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

  {{-- Reglas --}}
  <p style="font-size: 13px; font-weight: 500; color: #6b7280; margin: 0 0 0.75rem;
            text-transform: uppercase; letter-spacing: 0.05em;">
    Antes de empezar
  </p>
  <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 2rem;">
    @foreach([
      'El diagnóstico debe completarse en una sola sesión, no puede pausarse.',
      'Responde con honestidad, los resultados solo se usan para personalizar tu ruta de estudio.',
      'No se mostrará retroalimentación durante el diagnóstico, solo al finalizar.',
    ] as $rule)
    <div style="display: flex; align-items: flex-start; gap: 10px; font-size: 14px;
                color: #6b7280; line-height: 1.5;">
      <span style="width: 6px; height: 6px; border-radius: 50%; background: #d1d5db;
                   margin-top: 7px; flex-shrink: 0; display: inline-block;"></span>
      {{ $rule }}
    </div>
    @endforeach
  </div>

  <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

  {{-- Footer --}}
  <div style="display: flex; align-items: center; justify-content: space-between;
              flex-wrap: wrap; gap: 1rem;">
    <span style="font-size: 13px; color: #6b7280;">
      Al finalizar, se generará tu ruta de aprendizaje automáticamente.
    </span>
    <a href="{{ route('initial-assessments.show', $assessment->id) }}"
       style="padding: 0.625rem 1.75rem; background: #111827; color: #fff;
              border-radius: 8px; font-size: 15px; font-weight: 500;
              text-decoration: none;">
      Iniciar diagnóstico
    </a>
  </div>

</div>
@endsection
