@extends('student.main')

@php
    $topicsForSelect = $topics->map(function ($topic) {
        return [
            'id' => $topic->id,
            'name' => $topic->name,
            'subtopics' => $topic->subtopics->map(function ($subtopic) {
                return [
                    'id' => $subtopic->id,
                    'name' => $subtopic->name,
                ];
            })->values(),
        ];
    })->values();
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xxl-10">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
            <div>
                <h1 class="fw-bold mb-1">Crear nuevo hilo</h1>
                <p class="text-muted mb-0">Crea un nuevo hilo en el foro.</p>
            </div>
        </div>

        <form action="{{ route('student.threads.store') }}" method="POST" enctype="multipart/form-data" class="card shadow-sm border-0">
            @csrf
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 px-lg-5">
                <h2 class="h5 mb-1">Datos del hilo</h2>
                <p class="text-muted mb-0">Completa los campos para crear el hilo.</p>
                <hr>
            </div>
            <div class="card-body px-4 px-lg-5 pt-2 pb-4 pb-lg-5">
                <div class="row g-4">

                    <div class="col-12">
                        <label for="title" class="form-label">Título*</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                                name="title" value="{{ old('title', $thread->title ?? '') }}" required
                                placeholder="Título del hilo">
                        </div>
                        @error('title')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-12">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea id="description" name="description" rows="3"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Escribe una descripción opcional para el hilo">{{ old('description', $thread->description ?? '') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="topic_id" class="form-label">Módulo*</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                            <select class="form-select @error('topic_id') is-invalid @enderror" id="topic_id"
                                name="topic_id" required>
                                <option value="">Seleccionar módulo...</option>
                                @foreach($topics as $topic)
                                <option value="{{ $topic->id }}" {{ old('topic_id')==$topic->id ? 'selected' : '' }}>
                                    {{ $topic->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @error('topic_id')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="subtopic_id" class="form-label">Subtema*</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                            <select class="form-select @error('subtopic_id') is-invalid @enderror" id="subtopic_id"
                                name="subtopic_id" required>
                                <option value="">Seleccionar subtema...</option>
                            </select>
                        </div>
                        @error('subtopic_id')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-12">
                        <label for="initial_question" class="form-label">Pregunta inicial*</label>
                        <textarea id="initial_question" name="initial_question" rows="3"
                            class="form-control @error('initial_question') is-invalid @enderror"
                            placeholder="Escribe tu pregunta inicial" required>{{ old('initial_question', $question->initial_question ?? '') }}</textarea>
                        @error('initial_question')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="resource_url" class="form-label">Imagen de referencia (opcional)</label>
                        <input type="file" class="form-control @error('resource_url') is-invalid @enderror"
                            id="resource_url" name="resource_url" accept=".jpg,.jpeg,.png,.webp">
                        @error('resource_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>


            <div
                class="card-footer bg-white border-top p-4 p-lg-5 d-flex flex-column flex-sm-row justify-content-end gap-2">
                <a href="{{ route('student.forum.index') }}" class="btn btn-outline-secondary order-2 order-sm-1">Cancelar</a>
                <button type="submit" class="btn btn-primary order-1 order-sm-2">Crear Hilo</button>
            </div>

        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (() => {
        const topics = @json($topicsForSelect);
        const topicSelect = document.getElementById('topic_id');
        const subtopicSelect = document.getElementById('subtopic_id');
        const oldSubtopicId = '{{ old('subtopic_id') }}';

        if (!topicSelect || !subtopicSelect) {
            return;
        }

        const renderSubtopics = (topicId) => {
            subtopicSelect.innerHTML = '<option value="">Seleccionar subtema...</option>';

            if (!topicId) {
                subtopicSelect.disabled = true;
                return;
            }

            const selectedTopic = topics.find((topic) => String(topic.id) === String(topicId));
            const subtopics = selectedTopic ? selectedTopic.subtopics : [];

            subtopics.forEach((subtopic) => {
                const option = document.createElement('option');
                option.value = subtopic.id;
                option.textContent = subtopic.name;

                if (oldSubtopicId && String(oldSubtopicId) === String(subtopic.id)) {
                    option.selected = true;
                }

                subtopicSelect.appendChild(option);
            });

            subtopicSelect.disabled = subtopics.length === 0;
        };

        topicSelect.addEventListener('change', (event) => {
            renderSubtopics(event.target.value);
        });

        renderSubtopics(topicSelect.value);
    })();
</script>
@endsection
