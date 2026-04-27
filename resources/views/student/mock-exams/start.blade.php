@extends('student.main')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Simulacro de examen de admisión</h4>
                    <small class="text-muted">Pon a prueba tus conocimientos en condiciones reales del examen</small>
                </div>
                <div class="card-body">

                    @if ($activeConfig)
                        <div class="alert alert-info">
                            <h6 class="alert-heading mb-3">Detalles del simulacro</h6>
                            <div class="row small">
                                <div class="col-md-6">
                                    <strong> Duración:</strong> {{ $activeConfig->duration_minutes }} minutos
                                </div>
                                <div class="col-md-6">
                                    <strong> Total de preguntas:</strong> {{ $activeConfig->total_questions }}
                                </div>
                                <div class="col-md-6 mt-2">
                                    <strong> Puntaje mínimo de aprobación:</strong> {{ $activeConfig->min_passing_score }}%
                                </div>
                            </div>

                            <hr>

                            <h6 class="mb-2">Distribución por módulos</h6>
                            <ul class="mb-0 small">
                                @foreach ($activeConfig->topicDistributions as $dist)
                                    <li>
                                        <strong>{{ $dist->topic?->name ?? '?' }}:</strong>
                                        {{ $dist->question_count }} pregunta(s)
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h6 class="alert-heading mb-2">⚠️ Antes de empezar, ten en cuenta:</h6>
                            <ul class="mb-0 small">
                                <li>Una vez iniciado, <strong>no podrás pausarlo</strong>.</li>
                                <li>Si cierras el navegador o pierdes la conexión, el simulacro se cerrará automáticamente con las respuestas que hayas dado.</li>
                                <li>El cronómetro corre desde el momento en que des clic.</li>
                                <li>Las preguntas no respondidas se evalúan como incorrectas.</li>
                            </ul>
                        </div>

                        <form action="{{ route('student.mock-exams.start') }}" method="POST"
                              onsubmit="return confirm('¿Estás listo para iniciar el simulacro? Una vez iniciado el cronómetro no se detiene.')">
                            @csrf
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                     Iniciar simulacro
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">Simulacro no disponible</h6>
                            <p class="mb-0">
                                Aún no se ha configurado ningún simulacro. Por favor contacta al administrador.
                            </p>
                        </div>
                    @endif

                    <hr class="mt-4">
                    <div class="text-center">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            Volver al dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
