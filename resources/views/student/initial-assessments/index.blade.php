@extends('layouts.assessment')

@section('content')
<div class="container py-5" style="max-width: 1000px;">
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-lg-5">

      <div class="mb-4">
        <span class="badge rounded-pill text-bg-info mb-3">Paso obligatorio</span>
        <h1 class="h3 fw-semibold mb-2">Diagnóstico inicial</h1>
        <p class="text-muted mb-0">
          Antes de comenzar tu preparación, necesitamos conocer tu nivel actual.
          Este diagnóstico nos permitirá generar una ruta de estudio personalizada para ti.
        </p>
      </div>

      <div class="row g-2 mb-4">
        @foreach([['Preguntas', '28'], ['Duración estimada', '~35 min'], ['Módulos evaluados', '7']] as $metric)
        <div class="col-12 col-md-4">
          <div class="bg-light rounded p-3 h-100">
            <p class="text-muted small mb-1">{{ $metric[0] }}</p>
            <p class="fs-4 fw-semibold mb-0">{{ $metric[1] }}</p>
          </div>
        </div>
        @endforeach
      </div>

      <hr class="my-4">

      <p class="text-uppercase small fw-semibold text-muted mb-3">Temas del diagnóstico</p>
      <div class="row g-2 mb-4">
        @foreach($topics as $topic)
        <div class="col-12 col-md-6">
          <div class="border rounded p-2 px-3 d-flex align-items-center gap-2 h-100">
            <span class="text-muted small fw-semibold" style="min-width: 24px;">
              {{ str_pad($topic->order, 2, '0', STR_PAD_LEFT) }}
            </span>
            <span>{{ $topic->name }}</span>
          </div>
        </div>
        @endforeach
      </div>

      <hr class="my-4">

      <p class="text-uppercase small fw-semibold text-muted mb-3">Antes de empezar</p>
      <ul class="list-group list-group-flush mb-4">
        @foreach([
          'El diagnóstico debe completarse en una sola sesión, no puede pausarse.',
          'Responde con honestidad, los resultados solo se usan para personalizar tu ruta de estudio.',
          'No se mostrará retroalimentación durante el diagnóstico, solo al finalizar.',
        ] as $rule)
        <li class="list-group-item px-0 text-muted">{{ $rule }}</li>
        @endforeach
      </ul>

      <hr class="my-4">

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <span class="small text-muted">
          Al finalizar, se generará tu ruta de aprendizaje automáticamente.
        </span>
        <a href="{{ route('initial-assessments.show', $assessment->id) }}" class="btn btn-dark px-4">
          Iniciar diagnóstico
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
