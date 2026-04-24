<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resource->title }} - Revisión - IngeniaMath</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.geogebra.org/apps/deployggb.js"></script>
    <style>
        .status-badge {
            font-size: .8rem;
            font-weight: 500;
            padding: .3rem .75rem;
            border-radius: 1rem;
        }
        .status-1 { background: #fff3cd; color: #664d03; }
        .status-2 { background: #d1e7dd; color: #0a3622; }
        .status-3 { background: #f8d7da; color: #58151c; }

        .flashcard {
            display: inline-block;
            width: 220px;
            min-height: 140px;
            border: 1px solid #0d6efd;
            border-radius: .5rem;
            padding: 1rem;
            margin: .4rem;
            cursor: pointer;
            background: #fff;
            vertical-align: top;
            text-align: center;
            position: relative;
            transition: transform 0.2s;
        }
        .flashcard:hover { transform: translateY(-2px); }
        .flashcard .card-label {
            position: absolute;
            top: .25rem;
            left: .5rem;
            font-size: .7rem;
            color: #6c757d;
        }
        .flashcard .card-content {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100px;
            font-size: .9rem;
            word-break: break-word;
        }

        .ggb-container {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            min-height: 450px;
            background: #fff;
        }

        .review-history-item {
            border-left: 3px solid #dee2e6;
            padding: .5rem .75rem;
            margin-bottom: .5rem;
        }
        .review-history-item.approved { border-left-color: #198754; }
        .review-history-item.rejected { border-left-color: #dc3545; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('moderator.dashboard') }}">IngeniaMath - Moderador</a>
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button>
        </form>
    </div>
</nav>

<div class="container pb-5">

    <a href="{{ route('moderator.resources.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        ← Volver al listado
    </a>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row">
        {{-- Columna izquierda: detalles + contenido --}}
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="mb-1">{{ $resource->title }}</h3>
                            <small class="text-muted">
                                Por <strong>{{ $resource->creator?->first_name }} {{ $resource->creator?->last_name }}</strong>
                                • {{ $resource->created_at?->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <span class="status-badge status-{{ $resource->status_id }}">
                            {{ $resource->status?->name }}
                        </span>
                    </div>

                    @if ($resource->description)
                        <p class="text-muted">{{ $resource->description }}</p>
                    @endif

                    <div class="row small mb-3">
                        <div class="col-md-6">
                            <strong>Tipo:</strong> {{ $resource->type?->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Subtema(s):</strong>
                            @foreach ($resource->subtopics as $subtopic)
                                {{ $subtopic->topic?->name }} → {{ $subtopic->name }}@if (!$loop->last), @endif
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    {{-- Renderizado del contenido según el tipo --}}
                    <h5 class="mb-3">Contenido</h5>

                    @if ($resource->type_id === 1)
                        {{-- VIDEO --}}
                        @if ($resource->url)
                            <video controls style="max-width: 100%;">
                                <source src="{{ asset('storage/' . $resource->url) }}">
                                Tu navegador no soporta video.
                            </video>
                        @else
                            <div class="alert alert-danger">Sin archivo de video.</div>
                        @endif

                    @elseif ($resource->type_id === 2)
                        {{-- PDF --}}
                        @if ($resource->url)
                            <iframe src="{{ asset('storage/' . $resource->url) }}"
                                    style="width: 100%; height: 600px; border: 1px solid #dee2e6; border-radius: .25rem;"></iframe>
                        @else
                            <div class="alert alert-danger">Sin archivo PDF.</div>
                        @endif

                    @elseif ($resource->type_id === 3)
                        {{-- FLASHCARDS --}}
                        <p class="small text-muted">
                            {{ $resource->flashcards->count() }} tarjeta(s). Haz clic en cada una para voltear.
                        </p>
                        @forelse ($resource->flashcards->sortBy('order') as $card)
                            <div class="flashcard"
                                 data-front="{{ e($card->front_text) }}"
                                 data-back="{{ e($card->back_text) }}"
                                 onclick="toggleCard(this)">
                                <div class="card-label">Tarjeta #{{ $card->order }}</div>
                                <div class="card-content">{{ $card->front_text }}</div>
                            </div>
                        @empty
                            <div class="alert alert-warning">Mazo sin tarjetas.</div>
                        @endforelse

                    @elseif ($resource->type_id === 4)
                        {{-- SIMULADOR --}}
                        @if ($resource->simulator_state)
                            <div id="ggbApplet" class="ggb-container"></div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const params = {
                                        appName: 'classic',
                                        width: 700,
                                        height: 450,
                                        showToolBar: false,
                                        showMenuBar: false,
                                        showAlgebraInput: false,
                                        showResetIcon: true,
                                        enableShiftDragZoom: true,
                                        enableRightClick: false,
                                        language: 'es',
                                        ggbBase64: @json($resource->simulator_state),
                                    };
                                    const app = new GGBApplet(params, true);
                                    app.inject('ggbApplet');
                                });
                            </script>
                        @else
                            <div class="alert alert-danger">Simulador sin estado guardado.</div>
                        @endif
                    @else
                        <div class="alert alert-secondary">Tipo desconocido.</div>
                    @endif
                </div>
            </div>

            {{-- Historial de revisiones previas --}}
            @if ($resource->reviews->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Historial de revisiones</h6>
                    </div>
                    <div class="card-body">
                        @foreach ($resource->reviews->sortByDesc('reviewed_at') as $review)
                            <div class="review-history-item {{ $review->is_approved ? 'approved' : 'rejected' }}">
                                <small>
                                    <strong>{{ $review->is_approved ? ' Aprobado' : '  Rechazado' }}</strong>
                                    por {{ $review->reviewer?->first_name }} {{ $review->reviewer?->last_name }}
                                    • {{ $review->reviewed_at?->format('d/m/Y H:i') }}
                                </small>
                                @if ($review->feedback)
                                    <p class="mb-0 mt-1 small">{{ $review->feedback }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Columna derecha: acciones de aprobación/rechazo --}}
        <div class="col-lg-4">
            @if ($resource->status_id === 1)
                {{-- Solo mostrar botones si está pendiente --}}
                <div class="card shadow-sm sticky-top" style="top: 1rem;">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Acciones de revisión</h6>
                    </div>
                    <div class="card-body">

                        {{-- Aprobar --}}
                        <form action="{{ route('moderator.resources.approve', $resource->id) }}" method="POST" class="mb-3">
                            @csrf
                            <label class="form-label small">Comentario (opcional)</label>
                            <textarea name="feedback" class="form-control form-control-sm mb-2" rows="2"
                                      maxlength="2000" placeholder="Comentario para el tutor..."></textarea>
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('¿Aprobar este recurso?')">
                                 Aprobar
                            </button>
                        </form>

                        <hr>

                        {{-- Rechazar --}}
                        <form action="{{ route('moderator.resources.reject', $resource->id) }}" method="POST">
                            @csrf
                            <label class="form-label small">Motivo del rechazo (opcional)</label>
                            <textarea name="feedback" class="form-control form-control-sm mb-2" rows="3"
                                      maxlength="2000"
                                      placeholder="Explica al tutor qué debe corregir..."></textarea>
                            <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('¿Rechazar este recurso?')">
                                 Rechazar
                            </button>
                        </form>
                    </div>
                </div>
            @else
                {{-- Si ya fue aprobado o rechazado, solo mostrar info --}}
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <p class="mb-2">Este recurso ya fue revisado.</p>
                        <span class="status-badge status-{{ $resource->status_id }}">
                            {{ $resource->status?->name }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleCard(el) {
        const current = el.querySelector('.card-content').textContent;
        const front = el.dataset.front;
        const back = el.dataset.back;
        el.querySelector('.card-content').textContent = current === front ? back : front;
        el.style.background = current === front ? '#e7f3ff' : '#fff';
    }
</script>

</body>
</html>
