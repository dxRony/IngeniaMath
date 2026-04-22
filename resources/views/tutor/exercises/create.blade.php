@extends('tutor.main')

@section('content')
    <h1 class="fw-bold mb-4">Crear ejercicio</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('tutor.exercises.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="card shadow-sm">
        @csrf

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Módulo</label>
                    <select id="topic_id" class="form-select">
                        <option value="">Seleccione un módulo</option>
                        @foreach($topics as $topic)
                            <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Solo para filtrar subtemas</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Subtema</label>
                    <select name="subtopic_id" id="subtopic_id" class="form-select" required>
                        <option value="">Seleccione un subtema</option>
                        @foreach($subtopics as $subtopic)
                            <option value="{{ $subtopic->id }}" data-topic="{{ $subtopic->topic_id }}">
                                {{ $subtopic->topic->name }} - {{ $subtopic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nivel</label>
                    <select name="level_id" class="form-select" required>
                        <option value="">Seleccione un nivel</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tipo</label>
                    <select name="type_id" id="type_id" class="form-select" required>
                        <option value="">Seleccione un tipo</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" data-name="{{ $type->name }}">
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Enunciado</label>
                    <textarea name="statement" rows="4" class="form-control" required>{{ old('statement') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Imagen del ejercicio</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                    <small class="text-muted">Opcional</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Respuesta correcta</label>
                    <input type="text" name="correct_answer" class="form-control" value="{{ old('correct_answer') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tiempo estimado (minutos)</label>
                    <input type="number" name="estimated_minutes" class="form-control" min="1" value="{{ old('estimated_minutes') }}" required>
                </div>
            </div>

            <div id="multiple-choice-section" class="mt-4 d-none">
                <hr>
                <h5>Opciones de respuesta</h5>
                <p class="text-muted">Marca una sola opción como correcta.</p>

                <div id="options-container">
                    @for($i = 0; $i < 4; $i++)
                        <div class="border rounded p-3 mb-3">
                            <label class="form-label">Opción {{ $i + 1 }}</label>
                            <input type="text" name="options[{{ $i }}][option_text]" class="form-control mb-2">

                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="correct_option"
                                       value="{{ $i }}"
                                       id="correct_option_{{ $i }}">
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
                    <input type="number" id="step_count" class="form-control" min="1" max="10" value="1">
                </div>
            </div>

            <div id="steps-container"></div>
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('tutor.exercises.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar borrador</button>
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

        typeSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const selectedName = selectedOption.dataset.name ?? '';

            if (selectedName === 'Opción múltiple') {
                multipleChoiceSection.classList.remove('d-none');
            } else {
                multipleChoiceSection.classList.add('d-none');
            }
        });

        function renderSteps(stepCount) {
            stepsContainer.innerHTML = '';

            for (let i = 0; i < stepCount; i++) {
                const stepHtml = `
                <div class="border rounded p-3 mb-3">
                    <label class="form-label">Paso ${i + 1}</label>
                    <textarea name="steps[${i}][description]" rows="2" class="form-control mb-2"></textarea>

                    <label class="form-label">Imagen del paso</label>
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

        renderSteps(parseInt(stepCountInput.value));
    </script>
@endsection
