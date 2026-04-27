{{--
Modal global de Recursos Educativos.
Se incluye desde el layout del estudiante para estar disponible en todas las pantallas.
Se dispara desde el navbar con data-bs-toggle="modal" data-bs-target="#resourcesModal".
--}}

<div class="modal fade" id="resourcesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    {{-- Botón "volver al listado" (solo visible en vista detalle) --}}
                    <button type="button" id="resBackBtn" class="btn btn-sm btn-outline-secondary"
                        style="display: none;">
                        ← Volver
                    </button>

                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-0" id="resModalTitle">📚 Recursos educativos</h5>
                        <small class="text-muted" id="resModalSubtitle">Material de apoyo para tu preparación</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body" id="resModalBody">
                {{-- El contenido se inyecta dinámicamente con JS --}}
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

{{-- Estilos específicos del modal --}}
<style>
    .res-card {
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
        border: 1px solid #dee2e6;
    }

    .res-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .res-flashcard {
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

    .res-flashcard:hover {
        transform: translateY(-2px);
    }

    .res-flashcard .card-label {
        position: absolute;
        top: .25rem;
        left: .5rem;
        font-size: .7rem;
        color: #6c757d;
    }

    .res-flashcard .card-content {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100px;
        font-size: .9rem;
        word-break: break-word;
    }

    .res-ggb-container {
        min-height: 500px;
        background: #fff;
    }
</style>

@push('scripts')
<script src="https://www.geogebra.org/apps/deployggb.js"></script>
<script>
    (function() {
            const modalEl       = document.getElementById('resourcesModal');
            const modalBody     = document.getElementById('resModalBody');
            const modalTitle    = document.getElementById('resModalTitle');
            const modalSubtitle = document.getElementById('resModalSubtitle');
            const backBtn       = document.getElementById('resBackBtn');

            let bsModal = null;
            let ggbApp  = null;
            let currentTopicFilter = null;
            let currentPage = 1;

            // Inicializar el modal de Bootstrap
            document.addEventListener('DOMContentLoaded', function () {
                bsModal = new bootstrap.Modal(modalEl);
            });

            // Cuando se abre el modal, cargar el listado
            modalEl.addEventListener('show.bs.modal', function () {
                currentTopicFilter = null;
                currentPage = 1;
                loadResourcesList();
            });

            // Cuando se cierra, limpiar
            modalEl.addEventListener('hidden.bs.modal', function () {
                modalBody.innerHTML = '';
                ggbApp = null;
                backBtn.style.display = 'none';
            });

            // Botón "Volver" regresa al listado
            backBtn.addEventListener('click', function () {
                loadResourcesList();
            });

            // ===========================================================
            // VISTA 1: LISTADO de recursos
            // ===========================================================
            async function loadResourcesList() {
                showSpinner();
                backBtn.style.display = 'none';
                modalTitle.textContent = '📚 Recursos educativos';
                modalSubtitle.textContent = 'Material de apoyo para tu preparación';

                try {
                    const url = new URL('/student/resources', window.location.origin);
                    if (currentTopicFilter) url.searchParams.set('topic', currentTopicFilter);
                    url.searchParams.set('page', currentPage);

                    const response = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) throw new Error('Error al cargar recursos');

                    const data = await response.json();
                    renderListView(data);
                } catch (err) {
                    modalBody.innerHTML = `
                <div class="alert alert-danger">
                    Error al cargar los recursos. Intenta de nuevo.
                </div>
            `;
                }
            }

            function renderListView(data) {
                let html = '';

                // Filtro por módulo
                html += `
            <div class="mb-3">
                <label class="form-label small text-muted mb-1">Filtrar por módulo</label>
                <select id="resTopicFilter" class="form-select">
                    <option value="">Todos los módulos (${data.pagination.total})</option>
        `;

                data.topics.forEach(topic => {
                    const selected = data.selected_topic === topic.id ? 'selected' : '';
                    html += `<option value="${topic.id}" ${selected}>${escapeHtml(topic.name)} (${topic.count})</option>`;
                });

                html += `
                </select>
            </div>
        `;

                // Grid de cards
                if (data.resources.length > 0) {
                    html += '<div class="row g-3">';
                    data.resources.forEach(r => {
                        html += `
                    <div class="col-md-4 col-sm-6">
                        <div class="card res-card h-100" data-resource-id="${r.id}">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div style="font-size: 2.5rem; line-height: 1;">${r.icon}</div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="mb-0 text-truncate">${escapeHtml(r.title)}</h6>
                                    <small class="text-muted">${escapeHtml(r.type_name || '')}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    });
                    html += '</div>';

                    // Paginación
                    if (data.pagination.has_pages) {
                        html += renderPagination(data.pagination);
                    }
                } else {
                    html += `
                <div class="text-center py-5">
                    <div style="font-size: 3rem;">📚</div>
                    <h5 class="mt-3">
                        ${data.selected_topic
                        ? 'No hay recursos en este módulo'
                        : 'No hay recursos disponibles'}
                    </h5>
                </div>
            `;
                }

                modalBody.innerHTML = html;

                // Listeners
                document.getElementById('resTopicFilter').addEventListener('change', function () {
                    currentTopicFilter = this.value || null;
                    currentPage = 1;
                    loadResourcesList();
                });

                document.querySelectorAll('.res-card').forEach(card => {
                    card.addEventListener('click', function () {
                        const id = this.dataset.resourceId;
                        loadResourceDetail(id);
                    });
                });

                document.querySelectorAll('.res-page-btn').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        currentPage = parseInt(this.dataset.page);
                        loadResourcesList();
                    });
                });
            }

            function renderPagination(pag) {
                let html = '<nav class="mt-4 d-flex justify-content-center"><ul class="pagination">';

                // Anterior
                if (pag.current_page > 1) {
                    html += `<li class="page-item"><a href="#" class="page-link res-page-btn" data-page="${pag.current_page - 1}">«</a></li>`;
                } else {
                    html += '<li class="page-item disabled"><span class="page-link">«</span></li>';
                }

                // Números
                for (let p = 1; p <= pag.last_page; p++) {
                    if (p === pag.current_page) {
                        html += `<li class="page-item active"><span class="page-link">${p}</span></li>`;
                    } else {
                        html += `<li class="page-item"><a href="#" class="page-link res-page-btn" data-page="${p}">${p}</a></li>`;
                    }
                }

                // Siguiente
                if (pag.current_page < pag.last_page) {
                    html += `<li class="page-item"><a href="#" class="page-link res-page-btn" data-page="${pag.current_page + 1}">»</a></li>`;
                } else {
                    html += '<li class="page-item disabled"><span class="page-link">»</span></li>';
                }

                html += '</ul></nav>';
                return html;
            }

            // ===========================================================
            // VISTA 2: DETALLE del recurso
            // ===========================================================
            async function loadResourceDetail(id) {
                showSpinner();
                backBtn.style.display = 'inline-block';
                modalTitle.textContent = 'Cargando...';
                modalSubtitle.textContent = '';

                try {
                    const response = await fetch(`/student/resources/${id}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) throw new Error('Error al cargar el recurso');

                    const data = await response.json();
                    renderDetailView(data);
                } catch (err) {
                    modalBody.innerHTML = `
                <div class="alert alert-danger">
                    Error al cargar el recurso.
                </div>
            `;
                }
            }

            function renderDetailView(data) {
                modalTitle.textContent = data.title;

                const subtopics = data.subtopics.map(s => `${s.topic} → ${s.name}`).join(' | ');
                modalSubtitle.textContent = subtopics;

                let html = '';

                if (data.description) {
                    html += `<p class="text-muted mb-3">${escapeHtml(data.description)}</p>`;
                }

                switch (parseInt(data.type_id)) {
                    case 1: html += renderVideo(data); break;
                    case 2: html += renderPdf(data); break;
                    case 3: html += renderFlashcards(data); break;
                    case 4: html += renderSimulatorPlaceholder(); break;
                    default: html += '<div class="alert alert-warning">Tipo desconocido.</div>';
                }

                if (data.creator) {
                    html += `<hr><small class="text-muted">Por: ${escapeHtml(data.creator)}</small>`;
                }

                modalBody.innerHTML = html;

                if (parseInt(data.type_id) === 4 && data.simulator_state) {
                    initGeoGebra(data.simulator_state);
                }

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

                let html = `<p class="small text-muted mb-3">${data.flashcards.length} tarjeta(s). Clic para voltear.</p><div class="text-center">`;

                data.flashcards.forEach(card => {
                    html += `
                <div class="res-flashcard"
                     data-front="${escapeHtml(card.front_text)}"
                     data-back="${escapeHtml(card.back_text)}"
                     onclick="window._toggleResFlashcard(this)">
                    <div class="card-label">#${card.order}</div>
                    <div class="card-content">${escapeHtml(card.front_text)}</div>
                </div>
            `;
                });

                html += '</div>';
                return html;
            }

            function renderSimulatorPlaceholder() {
                return `<div id="resGgbApplet" class="res-ggb-container"></div>`;
            }

            function initGeoGebra(state) {
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
                ggbApp.inject('resGgbApplet');
            }

            // Función global para los onclick de las flashcards
            window._toggleResFlashcard = function(el) {
                const current = el.querySelector('.card-content').textContent;
                const front = el.dataset.front;
                const back = el.dataset.back;
                el.querySelector('.card-content').textContent = current === front ? back : front;
                el.style.background = current === front ? '#d1e7dd' : '#fff';
            };

            function showSpinner() {
                modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `;
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text ?? '';
                return div.innerHTML;
            }
        })();
</script>
@endpush
