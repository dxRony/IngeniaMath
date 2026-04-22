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

                    <form action="{{ route('tutor.resources.store') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

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
                                        {{ $type->name }}{{ $type->id == 3 ? ' (próximamente)' : '' }}
                                    </option>
                                @endforeach
                            </select>

                            <div id="flashcardWarning" class="alert alert-warning mt-2 d-none" role="alert">
                                <strong>Atención:</strong> los recursos tipo <em>Flashcards</em> se crean desde
                                una sección dedicada que aún no está disponible. Selecciona otro tipo por ahora.
                            </div>
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

                        <div class="mb-3">
                            <label class="form-label">Archivo <span class="text-danger">*</span></label>
                            <input type="file" id="fileInput" name="file" class="form-control"
                                   accept=".mp4,.webm,.mov,.pdf,.html,.htm" required>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const typeSelect = document.getElementById('type_id');
    const fileInput = document.getElementById('fileInput');
    const previewBox = document.getElementById('previewBox');
    const flashcardWarning = document.getElementById('flashcardWarning');
    const submitBtn = document.getElementById('submitBtn');

    // Bloquea Flashcards porque aún no tiene interfaz
    function updateFlashcardState() {
        const selectedOption = typeSelect.options[typeSelect.selectedIndex];
        const isFlashcard = selectedOption && selectedOption.dataset.isFlashcard === '1';

        if (isFlashcard) {
            flashcardWarning.classList.remove('d-none');
            fileInput.disabled = true;
            submitBtn.disabled = true;
        } else {
            flashcardWarning.classList.add('d-none');
            fileInput.disabled = false;
            submitBtn.disabled = false;
        }
    }

    typeSelect.addEventListener('change', updateFlashcardState);
    updateFlashcardState();

    // Preview dinámico del archivo seleccionado
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
</script>
</body>
</html>
