@extends('tutor.main')

@section('content')
    <style>
        .file-preview-box {
            border: 2px dashed #dee2e6;
            border-radius: .5rem;
            padding: 1rem;
            background: #f8f9fa;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }
        .file-preview-box.has-file {
            border-color: #198754;
            background: #d1e7dd;
        }
        .file-preview-box video,
        .file-preview-box iframe {
            max-width: 100%;
            max-height: 250px;
            border-radius: .25rem;
        }
        .file-icon { font-size: 3rem; line-height: 1; }

        .flashcard-item {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #fff;
            position: relative;
        }
        .flashcard-number {
            position: absolute;
            top: .5rem;
            left: .75rem;
            font-size: .8rem;
            color: #6c757d;
            font-weight: 500;
        }
        .flashcard-remove {
            position: absolute;
            top: .5rem;
            right: .5rem;
        }

        .simulator-editor {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            background: #fff;
            min-height: 500px;
        }
        .simulator-help {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: .75rem 1rem;
            border-radius: .25rem;
            margin-bottom: 1rem;
            font-size: .9rem;
        }
        .current-file-box {
            background: #e9ecef;
            padding: .75rem;
            border-radius: .25rem;
            font-size: .9rem;
        }
    </style>

    <a href="{{ route('tutor.resources.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        ← Volver al listado
    </a>

    <div class="row justify-content-center">
        <div class="col-lg-10">

            @if ($resource->status_id === 3 && $resource->latestReview && !$resource->latestReview->is_approved)
                <div class="alert alert-danger">
                    <strong>Este recurso fue rechazado.</strong>
                    @if ($resource->latestReview->feedback)
                        <p class="mb-1 mt-2"><strong>Motivo del moderador:</strong></p>
                        <p class="mb-0">{{ $resource->latestReview->feedback }}</p>
                    @else
                        <p class="mb-0 mt-2">El moderador no dejó un motivo específico.</p>
                    @endif
                    <small class="d-block mt-2">
                        Revisado por {{ $resource->latestReview->reviewer?->first_name }} {{ $resource->latestReview->reviewer?->last_name }}
                        el {{ $resource->latestReview->reviewed_at?->format('d/m/Y H:i') }}
                    </small>
                </div>
            @endif

            @if ($resource->status_id === 2)
                <div class="alert alert-warning">
                    <strong>Atención:</strong> este recurso está actualmente <strong>Aprobado</strong> y visible para los estudiantes.
                    Al guardar los cambios, volverá a estado <strong>Pendiente de revisión</strong> y dejará de estar visible hasta que el moderador lo apruebe de nuevo.
                </div>
            @endif

            <div class="card shadow">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Editar recurso</h4>
                    <small class="text-muted">
                        Tipo: <strong>{{ $resource->type?->name ?? 'N/A' }}</strong>
                        • Estado actual: <strong>{{ $resource->status?->name }}</strong>
                    </small>
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="resourceForm" action="{{ route('tutor.resources.update', $resource->id) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <input type="hidden" id="simulatorStateInput" name="simulator_state" value="">

                        <div class="mb-3">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                   value="{{ old('title', $resource->title) }}" maxlength="255" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-control" rows="3"
                                      maxlength="2000">{{ old('description', $resource->description) }}</textarea>
                            <small class="text-muted">Opcional. Máximo 2000 caracteres.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo de recurso</label>
                            <input type="text" class="form-control" value="{{ $resource->type?->name }}" disabled>
                            <small class="text-muted">El tipo no se puede cambiar. Para cambiar el tipo, crea un recurso nuevo.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subtema asociado <span class="text-danger">*</span></label>
                            <select name="subtopic_id" class="form-select" required>
                                <option value="">-- Selecciona un subtema --</option>
                                @php $currentSubtopicId = old('subtopic_id', $resource->subtopics->first()?->id); @endphp
                                @foreach ($topics as $topic)
                                    @if ($topic->subtopics->isNotEmpty())
                                        <optgroup label="{{ $topic->name }}">
                                            @foreach ($topic->subtopics as $subtopic)
                                                <option value="{{ $subtopic->id }}"
                                                    {{ $currentSubtopicId == $subtopic->id ? 'selected' : '' }}>
                                                    {{ $subtopic->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        @if ($resource->type_id === 1 || $resource->type_id === 2)
                            <div class="mb-3">
                                <label class="form-label">Archivo actual</label>
                                <div class="current-file-box">
                                    @if ($resource->url)
                                        <a href="{{ asset('storage/' . $resource->url) }}" target="_blank">
                                            Ver archivo actual ({{ basename($resource->url) }})
                                        </a>
                                    @else
                                        <span class="text-muted">No hay archivo guardado.</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reemplazar archivo (opcional)</label>
                                <input type="file" id="fileInput" name="file" class="form-control"
                                       accept=".mp4,.webm,.mov,.pdf">
                                <small class="text-muted">Deja este campo vacío si no quieres cambiar el archivo.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vista previa del nuevo archivo</label>
                                <div id="previewBox" class="file-preview-box">
                                    <span class="file-icon text-muted">📄</span>
                                    <small class="text-muted mt-2">No hay archivo nuevo seleccionado</small>
                                </div>
                            </div>
                        @endif

                        @if ($resource->type_id === 3)
                            <hr>
                            <h5 class="mb-3">Tarjetas del mazo</h5>
                            <p class="text-muted small">
                                Edita, agrega o elimina tarjetas. Los cambios reemplazarán todas las tarjetas actuales.
                            </p>

                            <div id="cardsContainer">
                                @foreach ($resource->flashcards->sortBy('order') as $index => $card)
                                    <div class="flashcard-item">
                                        <span class="flashcard-number">Tarjeta #<span class="card-number">{{ $index + 1 }}</span></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger flashcard-remove remove-card-btn">
                                            ✕
                                        </button>
                                        <div class="row mt-4">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Frente <span class="text-danger">*</span></label>
                                                <textarea class="form-control front-text" name="cards[{{ $index }}][front_text]"
                                                          rows="3" maxlength="1000">{{ $card->front_text }}</textarea>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Reverso <span class="text-danger">*</span></label>
                                                <textarea class="form-control back-text" name="cards[{{ $index }}][back_text]"
                                                          rows="3" maxlength="1000">{{ $card->back_text }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" id="addCardBtn" class="btn btn-outline-primary w-100 mb-3">
                                + Agregar tarjeta
                            </button>
                        @endif

                        @if ($resource->type_id === 4)
                            <hr>
                            <h5 class="mb-3">Editor de simulador</h5>
                            <div class="simulator-help">
                                <strong>💡 Instrucciones:</strong> el simulador actual ya está cargado en el editor.
                                Puedes modificar la construcción o dejarla como está.
                                Al guardar, la nueva versión reemplazará la anterior.
                            </div>
                            <div id="ggbApplet" class="simulator-editor"></div>
                        @endif

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('tutor.resources.index') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                Guardar y enviar a revisión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <template id="cardTemplate">
        <div class="flashcard-item">
            <span class="flashcard-number">Tarjeta #<span class="card-number">1</span></span>
            <button type="button" class="btn btn-sm btn-outline-danger flashcard-remove remove-card-btn">
                ✕
            </button>
            <div class="row mt-4">
                <div class="col-md-6 mb-2">
                    <label class="form-label small">Frente <span class="text-danger">*</span></label>
                    <textarea class="form-control front-text" rows="3" maxlength="1000"
                              placeholder="Pregunta, fórmula o concepto..."></textarea>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small">Reverso <span class="text-danger">*</span></label>
                    <textarea class="form-control back-text" rows="3" maxlength="1000"
                              placeholder="Respuesta o explicación..."></textarea>
                </div>
            </div>
        </div>
    </template>
@endsection

@section('scripts')
    @if ($resource->type_id === 4)
        <script src="https://www.geogebra.org/apps/deployggb.js"></script>
    @endif

    <script>
        const TYPE_ID = {{ $resource->type_id }};
        const fileInput = document.getElementById('fileInput');
        const previewBox = document.getElementById('previewBox');
        const resourceForm = document.getElementById('resourceForm');

        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                previewBox.innerHTML = '';
                previewBox.classList.remove('has-file');

                if (!file) {
                    previewBox.innerHTML = `
                        <span class="file-icon text-muted">📄</span>
                        <small class="text-muted mt-2">No hay archivo nuevo seleccionado</small>
                    `;
                    return;
                }

                previewBox.classList.add('has-file');
                const url = URL.createObjectURL(file);
                const mime = file.type;
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);

                let previewHtml = '';
                if (mime.startsWith('video/')) {
                    previewHtml = `<video src="${url}" controls></video>`;
                } else if (mime === 'application/pdf') {
                    previewHtml = `<iframe src="${url}" style="width:100%; height:300px;"></iframe>`;
                } else {
                    previewHtml = `<span class="file-icon">📎</span>`;
                }

                previewHtml += `
                    <div class="mt-2 small">
                        <strong>${file.name}</strong><br>
                        <span class="text-muted">${sizeMB} MB • ${mime || 'tipo desconocido'}</span>
                    </div>
                `;
                previewBox.innerHTML = previewHtml;
            });
        }

        const cardsContainer = document.getElementById('cardsContainer');
        const addCardBtn = document.getElementById('addCardBtn');
        const cardTemplate = document.getElementById('cardTemplate');

        if (cardsContainer && addCardBtn) {
            function addCard() {
                const clone = cardTemplate.content.cloneNode(true);
                cardsContainer.appendChild(clone);
                renumberCards();
            }

            function removeCard(cardElement) {
                cardElement.remove();
                renumberCards();
            }

            function renumberCards() {
                const cards = cardsContainer.querySelectorAll('.flashcard-item');
                cards.forEach((card, index) => {
                    card.querySelector('.card-number').textContent = index + 1;
                    card.querySelector('.front-text').name = `cards[${index}][front_text]`;
                    card.querySelector('.back-text').name = `cards[${index}][back_text]`;
                });
            }

            addCardBtn.addEventListener('click', addCard);

            cardsContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-card-btn')) {
                    const cardElement = e.target.closest('.flashcard-item');
                    const totalCards = cardsContainer.querySelectorAll('.flashcard-item').length;

                    if (totalCards <= 1) {
                        alert('Debes tener al menos una tarjeta.');
                        return;
                    }
                    if (confirm('¿Eliminar esta tarjeta?')) {
                        removeCard(cardElement);
                    }
                }
            });
        }

        let ggbApplet = null;

        if (TYPE_ID === 4) {
            const params = {
                appName: 'classic',
                width: 900,
                height: 500,
                showToolBar: true,
                showAlgebraInput: true,
                showMenuBar: false,
                showResetIcon: true,
                enableShiftDragZoom: true,
                enableRightClick: true,
                language: 'es',
                ggbBase64: @json($resource->simulator_state ?? ''),
                appletOnLoad: function(api) {
                    ggbApplet = api;
                }
            };

            const applet = new GGBApplet(params, true);
            applet.inject('ggbApplet');
        }

        resourceForm.addEventListener('submit', function (e) {
            if (TYPE_ID === 3) {
                const cards = cardsContainer.querySelectorAll('.flashcard-item');
                if (cards.length === 0) {
                    e.preventDefault();
                    alert('Debes tener al menos una tarjeta.');
                    return;
                }

                let incomplete = false;
                cards.forEach((card) => {
                    const front = card.querySelector('.front-text').value.trim();
                    const back = card.querySelector('.back-text').value.trim();
                    if (!front || !back) incomplete = true;
                });

                if (incomplete) {
                    e.preventDefault();
                    alert('Todas las tarjetas deben tener texto en el frente y en el reverso.');
                }
            } else if (TYPE_ID === 4) {
                if (!ggbApplet) {
                    e.preventDefault();
                    alert('El editor aún no se ha cargado. Espera unos segundos.');
                    return;
                }

                const state = ggbApplet.getBase64();
                if (state && state.length >= 10) {
                    document.getElementById('simulatorStateInput').value = state;
                }
            }
        });
    </script>
@endsection
