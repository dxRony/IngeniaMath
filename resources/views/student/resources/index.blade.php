@extends('student.main')

@section('content')
    <div class="container py-4" style="max-width: 1100px;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0">Recursos educativos</h3>
                <small class="text-muted">Material de apoyo para tu preparación</small>
            </div>
        </div>

        {{-- Filtro por módulo --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('student.resources.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label small text-muted">Filtrar por módulo</label>
                        <select name="topic" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los módulos ({{ $resources->total() }})</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}" {{ $selectedTopic === $topic->id ? 'selected' : '' }}>
                                    {{ $topic->name }} ({{ $topic->count }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        @if($selectedTopic)
                            <a href="{{ route('student.resources.index') }}" class="btn btn-outline-secondary w-100">
                                Limpiar filtro
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Grid de recursos --}}
        @if($resources->count() > 0)
            <div class="row g-3">
                @foreach($resources as $resource)
                    @php
                        $icon = match($resource->type_id) {
                            1 => '🎬',
                            2 => '📄',
                            3 => '🃏',
                            4 => '📐',
                            default => '📎',
                        };
                    @endphp
                    <div class="col-md-4 col-sm-6">
                        <button type="button"
                                class="card shadow-sm border-0 h-100 w-100 text-start p-0 resource-card"
                                data-resource-id="{{ $resource->id }}"
                                style="cursor: pointer; transition: transform 0.15s, box-shadow 0.15s;">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div style="font-size: 2.5rem; line-height: 1;">{{ $icon }}</div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="mb-0 text-truncate">{{ $resource->title }}</h6>
                                    <small class="text-muted">{{ $resource->type?->name }}</small>
                                </div>
                            </div>
                        </button>
                    </div>
                @endforeach
            </div>

            @if($resources->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $resources->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <div style="font-size: 4rem;">📚</div>
                    <h4 class="mt-3">
                        @if($selectedTopic)
                            No hay recursos en este módulo aún
                        @else
                            Aún no hay recursos disponibles
                        @endif
                    </h4>
                    <p class="text-muted mb-0">Pronto se agregarán materiales de apoyo.</p>
                </div>
            </div>
        @endif

    </div>

    {{-- Modal universal para mostrar el recurso --}}
    <div class="modal fade" id="resourceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalTitle">Cargando...</h5>
                        <small class="text-muted" id="modalSubtitle"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .resource-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }

        /* Flashcards en modal */
        .student-flashcard {
            display: inline-block;
            width: 220px;
            min-height: 140px;
            border: 1px solid #198754;
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
        .student-flashcard:hover {
            transform: translateY(-2px);
        }
        .student-flashcard .card-label {
            position: absolute;
            top: .25rem;
            left: .5rem;
            font-size: .7rem;
            color: #6c757d;
        }
        .student-flashcard .card-content {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100px;
            font-size: .9rem;
            word-break: break-word;
        }

        .ggb-modal-container {
            min-height: 500px;
            background: #fff;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://www.geogebra.org/apps/deployggb.js"></script>
    <script>
        const resourceModal = new bootstrap.Modal(document.getElementById('resourceModal'));
        const modalTitle    = document.getElementById('modalTitle');
        const modalSubtitle = document.getElementById('modalSubtitle');
        const modalBody     = document.getElementById('modalBody');

        let ggbApp = null;

        document.querySelectorAll('.resource-card').forEach(card => {
            card.addEventListener('click', async function () {
                const id = this.dataset.resourceId;

                // Reset modal a estado de carga
                modalTitle.textContent = 'Cargando...';
                modalSubtitle.textContent = '';
                modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            `;

                resourceModal.show();

                try {
                    const response = await fetch(`/student/resources/${id}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) throw new Error('Error al cargar el recurso');

                    const data = await response.json();
                    renderResourceInModal(data);
                } catch (err) {
                    modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> No se pudo cargar el recurso. Intenta de nuevo.
                    </div>
                `;
                }
            });
        });

        function renderResourceInModal(data) {
            modalTitle.textContent = data.title;

            const subtopicsList = data.subtopics
                .map(s => `${s.topic} → ${s.name}`)
                .join(' | ');
            modalSubtitle.textContent = subtopicsList;

            let html = '';

            // Descripción si tiene
            if (data.description) {
                html += `<p class="text-muted mb-3">${escapeHtml(data.description)}</p>`;
            }

            // Renderizar según el tipo
            switch (parseInt(data.type_id)) {
                case 1: // Video
                    html += renderVideo(data);
                    break;
                case 2: // PDF
                    html += renderPdf(data);
                    break;
                case 3: // Flashcards
                    html += renderFlashcards(data);
                    break;
                case 4: // Simulador
                    html += renderSimulatorPlaceholder();
                    break;
                default:
                    html += '<div class="alert alert-warning">Tipo de recurso desconocido.</div>';
            }

            // Footer info
            if (data.creator) {
                html += `<hr><small class="text-muted">Por: ${escapeHtml(data.creator)}</small>`;
            }

            modalBody.innerHTML = html;

            // Inyectar simulador después de poner el HTML
            if (parseInt(data.type_id) === 4 && data.simulator_state) {
                initGeoGebra(data.simulator_state);
            }

            // Re-renderizar MathJax si hay fórmulas
            if (window.MathJax && window.MathJax.typesetPromise) {
                window.MathJax.typesetPromise([modalBody]);
            }
        }

        function renderVideo(data) {
            if (!data.url) return '<div class="alert alert-danger">Sin archivo de video.</div>';
            return `
            <video controls style="width: 100%; max-height: 70vh; border-radius: .25rem;">
                <source src="${escapeHtml(data.url)}">
                Tu navegador no soporta video.
            </video>
        `;
        }

        function renderPdf(data) {
            if (!data.url) return '<div class="alert alert-danger">Sin archivo PDF.</div>';
            return `
            <iframe src="${escapeHtml(data.url)}"
                    style="width: 100%; height: 70vh; border: 1px solid #dee2e6; border-radius: .25rem;"></iframe>
        `;
        }

        function renderFlashcards(data) {
            if (!data.flashcards || data.flashcards.length === 0) {
                return '<div class="alert alert-warning">Sin tarjetas en este mazo.</div>';
            }

            let html = `
            <p class="small text-muted mb-3">
                ${data.flashcards.length} tarjeta(s). Haz clic en cada una para voltear.
            </p>
            <div class="text-center">
        `;

            data.flashcards.forEach(card => {
                html += `
                <div class="student-flashcard"
                     data-front="${escapeHtml(card.front_text)}"
                     data-back="${escapeHtml(card.back_text)}"
                     onclick="toggleStudentCard(this)">
                    <div class="card-label">#${card.order}</div>
                    <div class="card-content">${escapeHtml(card.front_text)}</div>
                </div>
            `;
            });

            html += '</div>';
            return html;
        }

        function renderSimulatorPlaceholder() {
            return `<div id="ggbAppletStudent" class="ggb-modal-container"></div>`;
        }

        function initGeoGebra(state) {
            // Limpiar instancia anterior si existe
            const container = document.getElementById('ggbAppletStudent');
            if (!container) return;

            const params = {
                appName: 'classic',
                width: 900,
                height: 500,
                showToolBar: false,
                showMenuBar: false,
                showAlgebraInput: false,
                showResetIcon: true,
                enableShiftDragZoom: true,
                enableRightClick: false,
                language: 'es',
                ggbBase64: state,
            };

            ggbApp = new GGBApplet(params, true);
            ggbApp.inject('ggbAppletStudent');
        }

        function toggleStudentCard(el) {
            const current = el.querySelector('.card-content').textContent;
            const front = el.dataset.front;
            const back = el.dataset.back;
            el.querySelector('.card-content').textContent = current === front ? back : front;
            el.style.background = current === front ? '#d1e7dd' : '#fff';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        // Limpiar el modal al cerrar (importante para el simulador)
        document.getElementById('resourceModal').addEventListener('hidden.bs.modal', function () {
            modalBody.innerHTML = '';
            ggbApp = null;
        });
    </script>
@endsection
