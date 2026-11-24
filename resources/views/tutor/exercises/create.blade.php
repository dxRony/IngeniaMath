@extends('tutor.main')

@section('content')
    <h1 class="fw-bold mb-4">Crear ejercicio</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
                </div>

                <div class="col-md-6">
                    <label class="form-label">Subtema*</label>
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
                    <label class="form-label">Nivel*</label>
                    <select name="level_id" class="form-select" required>
                        <option value="">Seleccione un nivel</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tipo*</label>
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
                    <label class="form-label">Enunciado*</label>
                    <textarea name="statement" rows="4" class="form-control" required>{{ old('statement') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Imagen o diagrama del ejercicio</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                    <small class="text-muted">Opcional</small>
                </div>

                {{-- respuesta numerica abierta--}}
                <div class="col-md-6" id="generic-answer-section">
                    <label class="form-label">Respuesta correcta*</label>
                    <input type="text"
                           name="correct_answer"
                           id="correct_answer"
                           class="form-control"
                           value="{{ old('correct_answer') }}">
                    <small class="text-muted" id="correct-answer-help">
                        Escribe la respuesta correcta del ejercicio.
                    </small>
                </div>

                {{-- V/F --}}
                <div class="col-md-6 d-none" id="true-false-section">
                    <label class="form-label">Respuesta correcta (Verdadero/Falso)</label>
                    <select name="true_false_answer" id="true_false_answer" class="form-select">
                        <option value="">Seleccione una opción</option>
                        <option value="Verdadero" {{ old('true_false_answer') === 'Verdadero' ? 'selected' : '' }}>
                            Verdadero
                        </option>
                        <option value="Falso" {{ old('true_false_answer') === 'Falso' ? 'selected' : '' }}>
                            Falso
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tiempo estimado (minutos)*</label>
                    <input type="number"
                           name="estimated_minutes"
                           class="form-control"
                           min="1"
                           value="{{ old('estimated_minutes') }}"
                           required>
                </div>

                <div class="col-12">
                    <label class="form-label">Explicación conceptual del tema relacionado*</label>
                    <textarea name="concept_explanation" rows="4" class="form-control" required>{{ old('concept_explanation') }}</textarea>
                    <small class="text-muted">
                        Explica el concepto matemático detrás del ejercicio.
                    </small>
                </div>
            </div>

            {{-- opcion multiple --}}
            <div id="multiple-choice-section" class="mt-4 d-none">
                <hr>
                <h5>Opciones de respuesta</h5>
                <p class="text-muted">Marca una sola opción como correcta.</p>

                <div id="options-container">
                    @for($i = 0; $i < 4; $i++)
                        <div class="border rounded p-3 mb-3">
                            <label class="form-label">Opción {{ $i + 1 }}</label>
                            <input type="text"
                                   name="options[{{ $i }}][option_text]"
                                   class="form-control mb-2"
                                   value="{{ old("options.$i.option_text") }}">

                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="correct_option"
                                       value="{{ $i }}"
                                       id="correct_option_{{ $i }}"
                                    {{ (string) old('correct_option') === (string) $i ? 'checked' : '' }}>
                                <label class="form-check-label" for="correct_option_{{ $i }}">
                                    Marcar como correcta
                                </label>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- completar espacios --}}
            <div id="fill-blank-section" class="mt-4 d-none">
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">Respuestas para completar espacios</h5>
                        <small class="text-muted">Indica cuántos espacios en blanco tiene el ejercicio y su respuesta correcta.</small>
                    </div>

                    <div style="max-width: 220px;">
                        <label class="form-label">Cantidad de espacios</label>
                        <input type="number"
                               name="blank_count"
                               id="blank_count"
                               class="form-control"
                               min="1"
                               max="10"
                               value="{{ old('blank_count', 1) }}">
                    </div>
                </div>

                <div id="fill-blank-answers-container"></div>
            </div>

            {{-- ejercicios relacionados --}}
            <div class="mt-4">
                <hr>
                <h5>Ejercicios relacionados</h5>
                <p class="text-muted mb-3">
                    Selecciona ejercicios del mismo subtema creados por el tutor.
                </p>

                <div id="related-exercises-container" class="border rounded p-3 bg-light" style="max-height: 280px; overflow-y: auto;">
                    @forelse($relatedExercises ?? [] as $relatedExercise)
                        <div class="form-check related-exercise-item"
                             data-subtopic="{{ $relatedExercise->subtopic_id }}">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="related_exercises[]"
                                   value="{{ $relatedExercise->id }}"
                                   id="related_exercise_{{ $relatedExercise->id }}"
                                {{ in_array($relatedExercise->id, old('related_exercises', [])) ? 'checked' : '' }}>

                            <label class="form-check-label" for="related_exercise_{{ $relatedExercise->id }}">
                                <strong>#{{ $relatedExercise->id }}</strong>
                                - {{ \Illuminate\Support\Str::limit($relatedExercise->statement, 90) }}
                            </label>
                        </div>
                    @empty
                        <p class="mb-0 text-muted">No hay ejercicios disponibles para relacionar.</p>
                    @endforelse
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
        const stepCountInput = document.getElementById('step_count');
        const stepsContainer = document.getElementById('steps-container');

        const multipleChoiceSection = document.getElementById('multiple-choice-section');
        const trueFalseSection = document.getElementById('true-false-section');
        const genericAnswerSection = document.getElementById('generic-answer-section');
        const fillBlankSection = document.getElementById('fill-blank-section');

        const correctAnswerInput = document.getElementById('correct_answer');
        const correctAnswerHelp = document.getElementById('correct-answer-help');

        const blankCountInput = document.getElementById('blank_count');
        const fillBlankAnswersContainer = document.getElementById('fill-blank-answers-container');

        const relatedExerciseItems = document.querySelectorAll('.related-exercise-item');

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
            filterRelatedExercises('');
        });

        subtopicSelect.addEventListener('change', function () {
            filterRelatedExercises(this.value);
        });

        function filterRelatedExercises(subtopicId) {
            relatedExerciseItems.forEach(item => {
                item.classList.toggle('d-none', subtopicId && item.dataset.subtopic !== subtopicId);

                if (subtopicId && item.dataset.subtopic !== subtopicId) {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = false;
                }
            });
        }

        function updateTypeSections() {
            const selectedOption = typeSelect.options[typeSelect.selectedIndex];
            const selectedName = selectedOption?.dataset.name ?? '';

            const trueFalseAnswer = document.getElementById('true_false_answer');
            const optionInputs = document.querySelectorAll('input[name^="options"]');
            const correctOptionInputs = document.querySelectorAll('input[name="correct_option"]');

            multipleChoiceSection.classList.add('d-none');
            trueFalseSection.classList.add('d-none');
            fillBlankSection.classList.add('d-none');
            genericAnswerSection.classList.add('d-none');

            correctAnswerInput.required = false;
            correctAnswerInput.disabled = true;

            if (trueFalseAnswer) {
                trueFalseAnswer.required = false;
                trueFalseAnswer.disabled = true;
            }

            optionInputs.forEach(input => {
                input.required = false;
                input.disabled = true;
            });

            correctOptionInputs.forEach(input => {
                input.required = false;
                input.disabled = true;
            });

            fillBlankAnswersContainer.innerHTML = '';

            if (selectedName === 'Opción múltiple') {
                multipleChoiceSection.classList.remove('d-none');

                optionInputs.forEach(input => {
                    input.required = true;
                    input.disabled = false;
                });

                correctOptionInputs.forEach(input => {
                    input.required = true;
                    input.disabled = false;
                });

            } else if (selectedName === 'Verdadero/Falso') {
                trueFalseSection.classList.remove('d-none');

                if (trueFalseAnswer) {
                    trueFalseAnswer.required = true;
                    trueFalseAnswer.disabled = false;
                }

            } else if (selectedName === 'Respuesta numérica abierta') {
                genericAnswerSection.classList.remove('d-none');
                correctAnswerInput.disabled = false;
                correctAnswerInput.required = true;
                correctAnswerHelp.textContent = 'Ingresa la respuesta numérica correcta.';

            } else if (selectedName === 'Completar espacios') {
                fillBlankSection.classList.remove('d-none');
                renderFillBlankAnswers(parseInt(blankCountInput?.value || 1), true);

            } else {
                genericAnswerSection.classList.remove('d-none');
                correctAnswerInput.disabled = false;
                correctAnswerInput.required = true;
            }
        }
        typeSelect.addEventListener('change', updateTypeSections);
        function renderFillBlankAnswers(blankCount, enabled = false) {
            fillBlankAnswersContainer.innerHTML = '';

            for (let i = 0; i < blankCount; i++) {
                const oldValue = @json(old('blank_answers', []));
                const value = oldValue[i] ?? '';

                const html = `
            <div class="border rounded p-3 mb-3">
                <label class="form-label">Respuesta del espacio ${i + 1}</label>
                <input type="text"
                       name="blank_answers[${i}]"
                       class="form-control"
                       value="${value}"
                       ${enabled ? 'required' : 'disabled'}>
            </div>
        `;
                fillBlankAnswersContainer.insertAdjacentHTML('beforeend', html);
            }
        }

        if (blankCountInput) {
            blankCountInput.addEventListener('input', function () {
                let count = parseInt(this.value);

                if (isNaN(count) || count < 1) count = 1;
                if (count > 10) count = 10;

                renderFillBlankAnswers(count, true);
            });
        }

        function renderSteps(stepCount) {
            stepsContainer.innerHTML = '';
            for (let i = 0; i < stepCount; i++) {
                const stepHtml = `
                <div class="border rounded p-3 mb-3">
                    <label class="form-label">Paso ${i + 1}</label>
                    <textarea name="steps[${i}][description]" rows="2" class="form-control mb-2"></textarea>

                    <label class="form-label">Imagen del paso</label>
                    <small class="text-muted">Opcional</small>
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
        updateTypeSections();
        renderSteps(parseInt(stepCountInput.value));
    </script>
@endsection
