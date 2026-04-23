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
        <div class="col-lg-8">
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

                        {{-- Campos comunes a todos los tipos --}}
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
                                            data-is-flashcard="{{ $type->id == 3 ? '1' : '0' }}"
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

                        {{-- Sección para recursos con archivo (videos, PDFs, simuladores) --}}
                        <div id="fileSection">
                            <div class="mb-3">
                                <label class="form-label">Archivo <span class="text-danger">*</span></label>
                                <input type="file" id="fileInput" name="file" class="form-control"
                                       accept=".mp4,.webm,.mov,.pdf,.html,.htm">
                                <small class="text-muted">
                                    Formatos permitidos: mp4, webm, mov (videos), pdf (documentos), html (simuladores). Máximo 100 MB.
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

                        {{-- Sección para flashcards (dinámica) --}}
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

                        <div class="d-flex justify-content-between">
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

{{-- Plantilla de tarjeta (no visible, se clona desde JS) --}}
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
<script>
    const typeSelect = document.getElementById('type_id');
    const fileSection = document.getElementById('fileSection');
    const fileInput = document.getElementById('fileInput');
    const previewBox = document.getElementById('previewBox');
    const flashcardSection = document.getElementById('flashcardSection');
    const cardsContainer = document.getElementById('cardsContainer');
    const addCardBtn = document.getElementById('addCardBtn');
    const cardTemplate = document.getElementById('cardTemplate');
    const resourceForm = document.getElementById('resourceForm');

    // =========================================================
    // Cambio de tipo: mostrar/ocultar secciones
    // =========================================================
    function updateSections() {
        const selectedOption = typeSelect.options[typeSelect.selectedIndex];
        const isFlashcard = selectedOption && selectedOption.dataset.isFlashcard === '1';

        if (isFlashcard) {
            fileSection.style.display = 'none';
            flashcardSection.style.display = 'block';
            fileInput.required = false;
            // Si no hay tarjetas aún, agregamos una automáticamente
            if (cardsContainer.children.length === 0) {
                addCard();
            }
        } else {
            fileSection.style.display = 'block';
            flashcardSection.style.display = 'none';
            fileInput.required = true;
        }
    }

    typeSelect.addEventListener('change', updateSections);
    updateSections(); // ejecutar al cargar

    // =========================================================
    // Preview del archivo
    // =========================================================
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
        } else if (mime === 'text/html') {
            previewHtml = `<span class="file-icon">🌐</span>
                           <small class="mt-2">Archivo HTML (simulador)</small>`;
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
            // Actualiza el número visible
            const numberSpan = card.querySelector('.card-number');
            numberSpan.textContent = index + 1;

            // Actualiza los name attributes para que lleguen como cards[0], cards[1]...
            const frontTextarea = card.querySelector('.front-text');
            const backTextarea = card.querySelector('.back-text');
            frontTextarea.name = `cards[${index}][front_text]`;
            backTextarea.name = `cards[${index}][back_text]`;
        });
    }

    addCardBtn.addEventListener('click', addCard);

    // Delegación de eventos para el botón de eliminar
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
        const isFlashcard = typeSelect.options[typeSelect.selectedIndex]?.dataset.isFlashcard === '1';

        if (isFlashcard) {
            const cards = cardsContainer.querySelectorAll('.flashcard-item');
            if (cards.length === 0) {
                e.preventDefault();
                alert('Debes agregar al menos una tarjeta al mazo.');
                return;
            }

            // Validar que todas las tarjetas tengan ambos campos llenos
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
        }
    });
</script>
</body>
</html>
