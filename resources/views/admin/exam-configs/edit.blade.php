@extends('admin.main')

@section('content')
    <a href="{{ route('admin.exam-configs.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        ← Volver al listado
    </a>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Editar configuración #{{ $config->id }}</h4>
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

                    <form action="{{ route('admin.exam-configs.update', $config->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Duración (minutos) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_minutes" class="form-control"
                                       value="{{ old('duration_minutes', $config->duration_minutes) }}"
                                       min="1" max="600" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total de preguntas <span class="text-danger">*</span></label>
                                <input type="number" id="totalQuestionsInput" name="total_questions"
                                       class="form-control"
                                       value="{{ old('total_questions', $config->total_questions) }}"
                                       min="1" max="200" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Puntaje mínimo (%) <span class="text-danger">*</span></label>
                                <input type="number" name="min_passing_score" class="form-control"
                                       value="{{ old('min_passing_score', $config->min_passing_score) }}"
                                       min="0" max="100" step="0.01" required>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_active" value="1"
                                   class="form-check-input" id="isActiveCheck"
                                {{ old('is_active', $config->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActiveCheck">
                                Configuración activa
                                @if ($config->is_active)
                                    <small class="text-success">(actualmente activa)</small>
                                @endif
                            </label>
                        </div>

                        <hr>

                        <h5 class="mb-3">Distribución por módulos</h5>
                        <p class="text-muted small">
                            La suma debe coincidir con el total de preguntas.
                        </p>

                        <div class="alert alert-info small">
                            <strong>Disponibilidad actual:</strong>
                            @foreach ($topics as $topic)
                                {{ $topic->name }}: <strong>{{ $availability[$topic->id] ?? 0 }}</strong>@if (!$loop->last) | @endif
                            @endforeach
                        </div>

                        <div class="row mb-3">
                            @foreach ($topics as $topic)
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small">
                                        {{ $topic->name }}
                                        <span class="text-muted">(disp: {{ $availability[$topic->id] ?? 0 }})</span>
                                    </label>
                                    <input type="number"
                                           name="distributions[{{ $topic->id }}]"
                                           class="form-control distribution-input"
                                           value="{{ old('distributions.' . $topic->id, $currentDistributions[$topic->id] ?? 0) }}"
                                           min="0"
                                           max="{{ $availability[$topic->id] ?? 0 }}">
                                </div>
                            @endforeach
                        </div>

                        <div class="alert alert-warning small mb-3" id="sumWarning" style="display: none;">
                            <strong>Atención:</strong> la suma actual es <span id="currentSum">0</span>, pero el total esperado es <span id="expectedSum">0</span>.
                        </div>
                        <div class="alert alert-success small mb-3" id="sumOk">
                            ✓ Suma correcta: <strong><span id="currentSumOk">0</span></strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.exam-configs.index') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const totalQuestionsInput = document.getElementById('totalQuestionsInput');
        const distributionInputs = document.querySelectorAll('.distribution-input');
        const sumWarning = document.getElementById('sumWarning');
        const sumOk = document.getElementById('sumOk');
        const currentSum = document.getElementById('currentSum');
        const currentSumOk = document.getElementById('currentSumOk');
        const expectedSum = document.getElementById('expectedSum');

        function updateSumWarning() {
            const total = parseInt(totalQuestionsInput.value) || 0;
            let sum = 0;
            distributionInputs.forEach(input => {
                sum += parseInt(input.value) || 0;
            });

            currentSum.textContent = sum;
            currentSumOk.textContent = sum;
            expectedSum.textContent = total;

            if (sum === total) {
                sumWarning.style.display = 'none';
                sumOk.style.display = 'block';
            } else {
                sumWarning.style.display = 'block';
                sumOk.style.display = 'none';
            }
        }

        totalQuestionsInput.addEventListener('input', updateSumWarning);
        distributionInputs.forEach(input => input.addEventListener('input', updateSumWarning));
        updateSumWarning();
    </script>
@endsection
