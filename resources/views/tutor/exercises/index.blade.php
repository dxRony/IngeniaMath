@extends('tutor.main')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Mis ejercicios</h1>
        <a href="{{ route('tutor.exercises.create') }}" class="btn btn-primary">
            Nuevo ejercicio
        </a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('duplicate_alerts'))
        <div class="alert alert-warning">
            <strong>Posibles ejercicios duplicados detectados:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('duplicate_alerts') as $duplicate)
                    <li>
                        Ejercicio #{{ $duplicate['exercise_id'] }} con similitud de {{ $duplicate['similarity'] }}%
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if($exercises->isEmpty())
                <p class="mb-0">Aún no has creado ejercicios.</p>
            @else
                <div class="card mb-4 border-0 bg-light">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Filtros</h5>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Módulo</label>
                                <select id="filter-topic" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($exercises->pluck('subtopic.topic.name')->unique()->sort() as $topicName)
                                        <option value="{{ $topicName }}">{{ $topicName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Nivel</label>
                                <select id="filter-level" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($exercises->pluck('level.name')->unique()->sort() as $levelName)
                                        <option value="{{ $levelName }}">{{ $levelName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Tipo</label>
                                <select id="filter-type" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($exercises->pluck('type.name')->unique()->sort() as $typeName)
                                        <option value="{{ $typeName }}">{{ $typeName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select id="filter-status" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($exercises->pluck('status.name')->unique()->sort() as $statusName)
                                        <option value="{{ $statusName }}">{{ $statusName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                Mostrando <span id="visible-count">{{ $exercises->count() }}</span> de {{ $exercises->count() }} ejercicios.
                            </small>

                            <button type="button" id="clear-filters" class="btn btn-sm btn-outline-secondary">
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle" id="exercises-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Módulo</th>
                            <th>Subtema</th>
                            <th>Nivel</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Última revisión</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($exercises as $exercise)
                            @php
                                $latestReview = $exercise->reviews->sortByDesc('reviewed_at')->first();
                            @endphp
                            <tr
                                class="exercise-row"
                                data-topic="{{ $exercise->subtopic->topic->name }}"
                                data-level="{{ $exercise->level->name }}"
                                data-type="{{ $exercise->type->name }}"
                                data-status="{{ $exercise->status->name }}"
                            >
                                <td>{{ $exercise->id }}</td>
                                <td>{{ $exercise->subtopic->topic->name }}</td>
                                <td>{{ $exercise->subtopic->name }}</td>
                                <td>{{ $exercise->level->name }}</td>
                                <td>{{ $exercise->type->name }}</td>
                                <td>
                                    @if($exercise->status->name === 'Aprobado')
                                        <span class="badge bg-success">Aprobado</span>
                                    @elseif($exercise->status->name === 'En revisión')
                                        <span class="badge bg-warning text-dark">En revisión</span>
                                    @elseif($exercise->status->name === 'Borrador')
                                        <span class="badge bg-secondary">Borrador</span>
                                    @elseif($exercise->status->name === 'Publicado')
                                        <span class="badge bg-primary">Publicado</span>
                                    @else
                                        <span class="badge bg-dark">{{ $exercise->status->name }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latestReview)
                                        <div>
                                            <strong>{{ $latestReview->is_approved ? 'Aprobado' : 'Rechazado' }}</strong><br>
                                            <small
                                                class="text-muted">{{ $latestReview->reviewed_at?->format('d/m/Y H:i') }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">Sin revisión</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('tutor.exercises.show', $exercise) }}"
                                           class="btn btn-sm btn-success">
                                            Ver ejercicio
                                        </a>
                                        <a href="{{ route('tutor.exercises.reviews', $exercise) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            Ver revisiones
                                        </a>
                                        @if($exercise->status->name === 'Borrador')
                                            <a href="{{ route('tutor.exercises.edit', $exercise) }}"
                                               class="btn btn-sm btn-primary">
                                                Editar
                                            </a>
                                            <form action="{{ route('tutor.exercises.submitReview', $exercise) }}"
                                                  method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    Enviar a revisión
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div id="no-results-message" class="alert alert-info d-none mt-3">
                        No se encontraron ejercicios con los filtros seleccionados.
                    </div>
                </div>
            @endif
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filters = {
                topic: document.getElementById('filter-topic'),
                level: document.getElementById('filter-level'),
                type: document.getElementById('filter-type'),
                status: document.getElementById('filter-status'),
            };

            const rows = document.querySelectorAll('.exercise-row');
            const visibleCount = document.getElementById('visible-count');
            const clearButton = document.getElementById('clear-filters');
            const noResultsMessage = document.getElementById('no-results-message');

            function applyFilters() {
                let count = 0;

                rows.forEach(row => {
                    const matchesTopic = !filters.topic.value || row.dataset.topic === filters.topic.value;
                    const matchesLevel = !filters.level.value || row.dataset.level === filters.level.value;
                    const matchesType = !filters.type.value || row.dataset.type === filters.type.value;
                    const matchesStatus = !filters.status.value || row.dataset.status === filters.status.value;

                    const isVisible = matchesTopic  && matchesLevel && matchesType && matchesStatus;

                    row.classList.toggle('d-none', !isVisible);

                    if (isVisible) {
                        count++;
                    }
                });

                visibleCount.textContent = count;
                noResultsMessage.classList.toggle('d-none', count > 0);
            }

            Object.values(filters).forEach(select => {
                select.addEventListener('change', applyFilters);
            });

            clearButton.addEventListener('click', function () {
                Object.values(filters).forEach(select => {
                    select.value = '';
                });

                applyFilters();
            });
        });
    </script>
@endsection
