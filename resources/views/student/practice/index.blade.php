@extends('student.main')

@section('content')
    <div class="container py-4">
        <div class="mb-4">
            <h2 class="fw-bold">Práctica</h2>
            <p class="text-muted">
                Selecciona un modo, módulo, subtema y nivel para iniciar una sesión de práctica.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Revisa los campos del formulario.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <strong>Configurar práctica</strong>
            </div>

            <div class="card-body">
                <form action="{{ route('student.practice.start') }}" method="POST" id="practiceForm">
                    @csrf

                    <div class="mb-3">
                        <label for="mode_id" class="form-label">Tipo de práctica</label>
                        <select
                            name="mode_id"
                            id="mode_id"
                            class="form-select @error('mode_id') is-invalid @enderror"
                            required
                        >
                            <option value="">Seleccione un tipo</option>
                            @foreach ($modes as $mode)
                                <option
                                    value="{{ $mode->id }}"
                                    data-name="{{ $mode->name }}"
                                    {{ old('mode_id') == $mode->id ? 'selected' : '' }}
                                >
                                    {{ $mode->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('mode_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="free-practice-fields" class="d-none">
                        <div class="mb-3">
                            <label for="topic_id" class="form-label">Módulo</label>
                            <select
                                name="topic_id"
                                id="topic_id"
                                class="form-select @error('topic_id') is-invalid @enderror"
                            >
                                <option value="">Seleccione un módulo</option>
                                @foreach ($topics as $topic)
                                    <option
                                        value="{{ $topic->id }}"
                                        data-subtopics='@json($topic->subtopics)'
                                        {{ old('topic_id') == $topic->id ? 'selected' : '' }}
                                    >
                                        {{ $topic->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('topic_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subtopic_id" class="form-label">Subtema</label>
                            <select
                                name="subtopic_id"
                                id="subtopic_id"
                                class="form-select @error('subtopic_id') is-invalid @enderror"
                            >
                                <option value="">Todos los subtemas del módulo</option>
                            </select>

                            @error('subtopic_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted">
                                Puedes dejarlo vacío para practicar con todos los subtemas del módulo.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="level_id" class="form-label">Nivel de dificultad</label>
                            <select
                                name="level_id"
                                id="level_id"
                                class="form-select @error('level_id') is-invalid @enderror"
                            >
                                <option value="">Seleccione un nivel</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}" {{ old('level_id') == $level->id ? 'selected' : '' }}>
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('level_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="quantity" class="form-label">Cantidad de ejercicios</label>
                        <input
                            type="number"
                            name="quantity"
                            id="quantity"
                            class="form-control @error('quantity') is-invalid @enderror"
                            min="1"
                            max="20"
                            value="{{ old('quantity', 10) }}"
                            required
                        >

                        @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-muted">
                            Puedes seleccionar entre 1 y 20 ejercicios.
                        </small>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            Iniciar práctica
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modeSelect = document.getElementById('mode_id');
            const freePracticeFields = document.getElementById('free-practice-fields');

            const topicSelect = document.getElementById('topic_id');
            const subtopicSelect = document.getElementById('subtopic_id');
            const levelSelect = document.getElementById('level_id');

            const oldSubtopicId = @json(old('subtopic_id'));

            function loadSubtopics() {
                const selectedOption = topicSelect.options[topicSelect.selectedIndex];

                subtopicSelect.innerHTML = '';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Todos los subtemas del módulo';
                subtopicSelect.appendChild(defaultOption);

                if (!selectedOption || !selectedOption.dataset.subtopics) {
                    return;
                }

                const subtopics = JSON.parse(selectedOption.dataset.subtopics);

                subtopics.forEach(function (subtopic) {
                    const option = document.createElement('option');
                    option.value = subtopic.id;
                    option.textContent = subtopic.name;

                    if (oldSubtopicId && parseInt(oldSubtopicId) === parseInt(subtopic.id)) {
                        option.selected = true;
                    }

                    subtopicSelect.appendChild(option);
                });
            }

            function updatePracticeModeFields() {
                const selectedOption = modeSelect.options[modeSelect.selectedIndex];
                const modeName = selectedOption?.dataset.name ?? '';

                const isFree = modeName === 'Libre';
                const isGuided = modeName === 'Guiada';

                freePracticeFields.classList.toggle('d-none', !isFree);

                topicSelect.required = isFree;
                levelSelect.required = isFree;

                topicSelect.disabled = !isFree;
                subtopicSelect.disabled = !isFree;
                levelSelect.disabled = !isFree;

                if (isGuided || !modeName) {
                    topicSelect.value = '';
                    subtopicSelect.innerHTML = '<option value="">Todos los subtemas del módulo</option>';
                    levelSelect.value = '';
                }
            }

            topicSelect.addEventListener('change', loadSubtopics);
            modeSelect.addEventListener('change', updatePracticeModeFields);

            if (topicSelect.value) {
                loadSubtopics();
            }

            updatePracticeModeFields();
        });
    </script>
@endsection
