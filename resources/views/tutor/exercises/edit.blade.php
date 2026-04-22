@extends('tutor.main')

@section('content')
    <h1 class="fw-bold mb-4">Editar ejercicio</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif
    <form action="{{ route('tutor.exercises.update', $exercise) }}"
          method="POST"
          enctype="multipart/form-data"
          class="card shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Módulo</label>
                    <select id="topic_id" class="form-select">
                        <option value="">Seleccione un módulo</option>
                        @foreach($topics as $topic)
                            <option value="{{ $topic->id }}" {{ $exercise->subtopic->topic_id == $topic->id ? 'selected' : '' }}>
                                {{ $topic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subtema</label>
                    <select name="subtopic_id" id="subtopic_id" class="form-select" required>
                        <option value="">Seleccione un subtema</option>
                        @foreach($subtopics as $subtopic)
                            <option value="{{ $subtopic->id }}"
                                    data-topic="{{ $subtopic->topic_id }}"
                                {{ $exercise->subtopic_id == $subtopic->id ? 'selected' : '' }}>
                                {{ $subtopic->topic->name }} - {{ $subtopic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nivel</label>
                    <select name="level_id" class="form-select" required>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" {{ $exercise->level_id == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo</label>
                    <select name="type_id" id="type_id" class="form-select" required>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}"
                                    data-name="{{ $type->name }}"
                                {{ $exercise->type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Enunciado</label>
                    <textarea name="statement" rows="4" class="form-control" required>{{ old('statement', $exercise->statement) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Imagen del ejercicio</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                    @if($exercise->image_url)
                        <div class="mt-2">
                            <small class="text-muted d-block">Imagen actual:</small>
                            <img src="{{ asset('storage/' . $exercise->image_url) }}" alt="Imagen actual" style="max-width: 200px;">
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Respuesta correcta</label>
                    <input type="text" name="correct_answer" class="form-control" value="{{ old('correct_answer', $exercise->correct_answer) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tiempo estimado (minutos)</label>
                    <input type="number" name="estimated_minutes" class="form-control" min="1"
                           value="{{ old('estimated_minutes', $exercise->estimated_minutes) }}" required>
                </div>
            </div>
            <div id="multiple-choice-section" class="mt-4 d-none">
                <hr>
                <h5>Opciones de respuesta</h5>
                <div id="options-container">
                    @php
                        $existingOptions = $exercise->multipleChoiceOptions->sortBy('order')->values();
                        $correctOptionIndex = $existingOptions->search(fn($option) => $option->is_correct);
                    @endphp
                    @for($i = 0; $i < max(4, $existingOptions->count()); $i++)
                        <div class="border rounded p-3 mb-3">
                            <label class="form-label">Opción {{ $i + 1 }}</label>
                            <input type="text"
                                   name="options[{{ $i }}][option_text]"
                                   class="form-control mb-2"
                                   value="{{ old("options.$i.option_text", $existingOptions[$i]->option_text ?? '') }}">

                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="correct_option"
                                       value="{{ $i }}"
                                       id="correct_option_{{ $i }}"
                                    {{ (string) old('correct_option', $correctOptionIndex) === (string) $i ? 'checked' : '' }}>
                                <label class="form-check-label" for="correct_option_{{ $i }}">
                                    Marcar como correcta
                                </label>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Solución paso a paso</h5>

                <div style="max-width: 220px;">
                    <label class="form-label">Cantidad de pasos</label>
                    <input type="number" id="step_count" class="form-control" min="1" max="10"
                           value="{{ max(1, $exercise->steps->count()) }}">
                </div>
            </div>
            <div id="existing-steps-data" class="d-none"
                 data-steps='@json($exercise->steps->sortBy("step_number")->values())'></div>
            <div id="steps-container"></div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('tutor.exercises.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        const topicSelect = document.getElementById('topic_id');
        const subtopicSelect = document.getElementById('subtopic_id');
        const typeSelect = document.getElementById('type_id');
        const multipleChoiceSection = document.getElementById('multiple-choice-section');
        const stepCountInput = document.getElementById('step_count');
        const stepsContainer = document.getElementById('steps-container');
        const existingStepsData = JSON.parse(document.getElementById('existing-steps-data').dataset.steps || '[]');

        topicSelect.addEventListener('change', function () {
            const topicId = this.value;

            Array.from(subtopicSelect.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }
                option.hidden = topicId && option.dataset.topic !== topicId;
            });
            subtopicSelect.value = '';
        });

        function toggleMultipleChoiceSection() {
            const selectedOption = typeSelect.options[typeSelect.selectedIndex];
            const selectedName = selectedOption.dataset.name ?? '';

            if (selectedName === 'Opción múltiple') {
                multipleChoiceSection.classList.remove('d-none');
            } else {
                multipleChoiceSection.classList.add('d-none');
            }
        }
        typeSelect.addEventListener('change', toggleMultipleChoiceSection);

        function renderSteps(stepCount) {
            stepsContainer.innerHTML = '';
            for (let i = 0; i < stepCount; i++) {
                const step = existingStepsData[i] ?? null;
                const stepHtml = `
    <div class="border rounded p-3 mb-3">
        <label class="form-label">Paso ${i + 1}</label>
        <textarea name="steps[${i}][description]" rows="2" class="form-control mb-2">${step?.description ?? ''}</textarea>

        <input type="hidden" name="steps[${i}][existing_image_url]" value="${step?.image_url ?? ''}">

        ${step?.image_url ? `
            <div class="mb-2">
                <small class="text-muted d-block">Imagen actual:</small>
                <img src="/storage/${step.image_url}" alt="Imagen paso" style="max-width: 200px;">
            </div>
        ` : ''}

        <label class="form-label">Nueva imagen del paso</label>
        <input type="file" name="steps[${i}][image_file]" class="form-control" accept="image/*">
    </div>
`;
                stepsContainer.insertAdjacentHTML('beforeend', stepHtml);
            }
        }
        stepCountInput.addEventListener('input', function () {
            let count = parseInt(this.value);

            if (isNaN(count) || count < 1) count = 1;
            if (count > 10) count = 10;

            renderSteps(count);
        });
        toggleMultipleChoiceSection();
        renderSteps(parseInt(stepCountInput.value));

        Array.from(subtopicSelect.options).forEach(option => {
            if (!option.value) return;
            option.hidden = topicSelect.value && option.dataset.topic !== topicSelect.value;
        });
    </script>
@endsection
