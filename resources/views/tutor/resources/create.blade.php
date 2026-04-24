<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear recurso educativo - IngeniaMath</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('tutor.dashboard') }}">IngeniaMath - Tutor</a>
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button>
        </form>
    </div>
</nav>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Crear recurso educativo</h4>
                    <small class="text-muted">
                        El recurso será enviado a revisión antes de publicarse.
                    </small>
                </div>
                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="resourceForm" action="{{ route('tutor.resources.store') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        {{-- Input hidden donde se inyectará el estado del simulador --}}
                        <input type="hidden" id="simulatorStateInput" name="simulator_state" value="">

                        <div class="mb-3">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                   value="{{ old('title') }}" maxlength="255" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-control" rows="3"
                                      maxlength="2000">{{ old('description') }}</textarea>
                            <small class="text-muted">Opcional. Máximo 2000 caracteres.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo de recurso <span class="text-danger">*</span></label>
                            <select id="type_id" name="type_id" class="form-select" required>
                                <option value="">-- Selecciona un tipo --</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}"
                                            data-type-key="{{ $type->id }}"
                                        {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subtema asociado <span class="text-danger">*</span></label>
                            <select name="subtopic_id" class="form-select" required>
                                <option value="">-- Selecciona un subtema --</option>
                                @forelse ($topics as $topic)
                                    @if ($topic->subtopics->isNotEmpty())
                                        <optgroup label="{{ $topic->name }}">
                                            @foreach ($topic->subtopics as $subtopic)
                                                <option value="{{ $subtopic->id }}"
                                                    {{ old('subtopic_id') == $subtopic->id ? 'selected' : '' }}>
                                                    {{ $subtopic->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @empty
                                    <option value="" disabled>
                                        No hay subtemas disponibles en el sistema.
                                    </option>
                                @endforelse
                            </select>
                        </div>

                        {{-- Sección para recursos con archivo (videos, PDFs) --}}
                        <div id="fileSection">
                            <div class="mb-3">
                                <label class="form-label">Archivo <span class="text-danger">*</span></label>
                                <input type="file" id="fileInput" name="file" class="form-control"
                                       accept=".mp4,.webm,.mov,.pdf">
                                <small class="text-muted">
                                    Formatos permitidos: mp4, webm, mov (videos), pdf (documentos). Máximo 100 MB.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vista previa</label>
                                <div id="previewBox" class="file-preview-box">
                                    <span class="file-icon text-muted">📄</span>
                                    <small class="text-muted mt-2">
                                        Selecciona un archivo para ver la vista previa
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Sección para flashcards --}}
                        <div id="flashcardSection" style="display: none;">
                            <hr>
                            <h5 class="mb-3">Tarjetas del mazo</h5>
                            <p class="text-muted small">
                                Agrega las tarjetas que formarán este mazo. Cada tarjeta tiene un frente (la pregunta o concepto) y un reverso (la respuesta).
                            </p>
                            <div id="cardsContainer"></div>
                            <button type="button" id="addCardBtn" class="btn btn-outline-primary w-100 mb-3">
                                + Agregar tarjeta
                            </button>
                        </div>

                        {{-- Sección para simulador GeoGebra --}}
                        <div id="simulatorSection" style="display: none;">
                            <hr>
                            <h5 class="mb-3">Editor de simulador</h5>

                            <div class="simulator-help">
                                <strong> Instrucciones:</strong> usa las herramientas del editor para construir tu simulador.
                                Puedes crear gráficas, figuras geométricas, sliders interactivos y mucho más.
                                Cuando termines, simplemente da clic en "Enviar a revisión"
                            </div>

                            <div id="ggbApplet" class="simulator-editor"></div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('tutor.dashboard') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                Enviar a revisión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Plantilla de tarjeta --}}
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://www.geogebra.org/apps/deployggb.js"></script>
<script>
    // Constantes de tipos (deben coincidir con los IDs de resource_types)
    const TYPE_VIDEO = 1;
    const TYPE_PDF = 2;
    const TYPE_FLASHCARDS = 3;
    const TYPE_SIMULATOR = 4;

    const typeSelect = document.getElementById('type_id');
    const fileSection = document.getElementById('fileSection');
    const fileInput = document.getElementById('fileInput');
    const previewBox = document.getElementById('previewBox');
    const flashcardSection = document.getElementById('flashcardSection');
    const cardsContainer = document.getElementById('cardsContainer');
    const addCardBtn = document.getElementById('addCardBtn');
    const cardTemplate = document.getElementById('cardTemplate');
    const simulatorSection = document.getElementById('simulatorSection');
    const simulatorStateInput = document.getElementById('simulatorStateInput');
    const resourceForm = document.getElementById('resourceForm');

    let ggbApplet = null; // Se inicializa cuando se selecciona simulador por primera vez
    let ggbInitialized = false;


    function updateSections() {
        const selectedOption = typeSelect.options[typeSelect.selectedIndex];
        const typeId = selectedOption ? parseInt(selectedOption.value) : null;

        // Ocultar todas las secciones por defecto
        fileSection.style.display = 'none';
        flashcardSection.style.display = 'none';
        simulatorSection.style.display = 'none';
        fileInput.required = false;

        if (typeId === TYPE_FLASHCARDS) {
            flashcardSection.style.display = 'block';
            if (cardsContainer.children.length === 0) addCard();
        } else if (typeId === TYPE_SIMULATOR) {
            simulatorSection.style.display = 'block';
            initGeoGebraOnce();
        } else if (typeId === TYPE_VIDEO || typeId === TYPE_PDF) {
            fileSection.style.display = 'block';
            fileInput.required = true;
        }
    }

    typeSelect.addEventListener('change', updateSections);
    updateSections();

    // =========================================================
    // GeoGebra: inicialización lazy (solo cuando se necesita)
    // =========================================================
    function initGeoGebraOnce() {
        if (ggbInitialized) return;
        ggbInitialized = true;

        const parameters = {
            appName: 'classic',
            width: 900,
            height: 500,
            showToolBar: true,
            showAlgebraInput: true,
            showMenuBar: false,
            showResetIcon: true,
            enableLabelDrags: true,
            enableShiftDragZoom: true,
            enableRightClick: true,
            errorDialogsActive: false,
            useBrowserForJS: false,
            language: 'es',
            appletOnLoad: function(api) {
                ggbApplet = api;
            }
        };

        const applet = new GGBApplet(parameters, true);
        applet.inject('ggbApplet');
    }


    fileInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        previewBox.innerHTML = '';
        previewBox.classList.remove('has-file');

        if (!file) {
            previewBox.innerHTML = `
                <span class="file-icon text-muted">📄</span>
                <small class="text-muted mt-2">Selecciona un archivo para ver la vista previa</small>
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

    // =========================================================
    // Gestión dinámica de tarjetas (flashcards)
    // =========================================================
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

    // =========================================================
    // Validación antes de enviar
    // =========================================================
    resourceForm.addEventListener('submit', function (e) {
        const selectedOption = typeSelect.options[typeSelect.selectedIndex];
        const typeId = selectedOption ? parseInt(selectedOption.value) : null;

        if (typeId === TYPE_FLASHCARDS) {
            const cards = cardsContainer.querySelectorAll('.flashcard-item');
            if (cards.length === 0) {
                e.preventDefault();
                alert('Debes agregar al menos una tarjeta al mazo.');
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
        } else if (typeId === TYPE_SIMULATOR) {
            if (!ggbApplet) {
                e.preventDefault();
                alert('El editor de simulador aún no se ha cargado. Espera unos segundos.');
                return;
            }

            // Extraer el estado del simulador en base64
            const state = ggbApplet.getBase64();
            if (!state || state.length < 10) {
                e.preventDefault();
                alert('El simulador está vacío. Crea al menos una construcción antes de enviar.');
                return;
            }

            simulatorStateInput.value = state;
        }
    });
</script>
</body>
</html>
