@extends('student.main')

@section('content')
    <div class="container py-4" style="max-width: 1100px;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0">Historial de simulacros</h3>
                <small class="text-muted">Revisa tu evolución a lo largo del tiempo</small>
            </div>
            <a href="{{ route('student.mock-exams.start-form') }}" class="btn btn-success">
                 Iniciar nuevo
            </a>
        </div>

        @if($generalStats['total_exams'] === 0)
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <div style="font-size: 4rem;">📊</div>
                    <h4 class="mt-3">Aún no has hecho ningún simulacro</h4>
                    <p class="text-muted mb-4">
                        Inicia tu primer simulacro para empezar a construir tu historial y ver tu evolución.
                    </p>
                    <a href="{{ route('student.mock-exams.start-form') }}" class="btn btn-success btn-lg">
                        Iniciar mi primer simulacro
                    </a>
                </div>
            </div>
        @else

            {{-- Métricas generales --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <div class="text-muted small">Simulacros realizados</div>
                            <h3 class="mb-0">{{ $generalStats['total_exams'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <div class="text-muted small">Promedio</div>
                            <h3 class="mb-0">{{ $generalStats['avg_score'] }}%</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <div class="text-muted small">Mejor puntaje</div>
                            <h3 class="mb-0 text-success">{{ $generalStats['best_score'] }}%</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <div class="text-muted small">Aprobados</div>
                            <h3 class="mb-0 text-primary">{{ $generalStats['passed_count'] }}/{{ $generalStats['total_exams'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráfica de evolución --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Evolución del puntaje</h5>
                    <small class="text-muted">Tu desempeño a lo largo del tiempo</small>
                </div>
                <div class="card-body">
                    @if(count($evolutionData) >= 2)
                        <canvas id="evolutionChart" style="max-height: 350px;"></canvas>
                    @else
                        <div class="alert alert-info mb-0">
                            Necesitas al menos <strong>2 simulacros</strong> para ver la gráfica de evolución.
                            Has hecho {{ count($evolutionData) }} hasta ahora.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Análisis de mejora y debilidad --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">📈 Mayor mejora</h6>
                        </div>
                        <div class="card-body">
                            @if($mostImproved)
                                <h4 class="mb-2">{{ $mostImproved['topic_name'] }}</h4>
                                <p class="text-success mb-2">
                                    <strong>+{{ $mostImproved['slope'] > 0 ? '↑' : '↓' }} tendencia</strong>
                                    de mejora detectada
                                </p>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li><strong>Primer simulacro:</strong> {{ $mostImproved['first_score'] }}%</li>
                                    <li><strong>Último simulacro:</strong> {{ $mostImproved['last_score'] }}%</li>
                                    <li><strong>Basado en:</strong> {{ $mostImproved['samples'] }} simulacros</li>
                                </ul>
                            @else
                                <p class="text-muted mb-0">
                                    Necesitas al menos <strong>2 simulacros</strong> para detectar tendencias de mejora.
                                    Sigue practicando.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">⚠️ Mayor debilidad persistente</h6>
                        </div>
                        <div class="card-body">
                            @if($weakestTopic)
                                <h4 class="mb-2">{{ $weakestTopic['topic_name'] }}</h4>
                                <p class="text-warning mb-2">
                                    Promedio histórico: <strong>{{ $weakestTopic['percentage'] }}%</strong>
                                </p>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li>
                                        <strong>Aciertos:</strong>
                                        {{ $weakestTopic['total_correct'] }} de {{ $weakestTopic['total_attempts'] }}
                                    </li>
                                    <li class="mt-2 text-secondary">
                                        💡 Te recomendamos reforzar este módulo en tu ruta de aprendizaje.
                                    </li>
                                </ul>
                            @else
                                <p class="text-muted mb-0">
                                    Aún no hay datos suficientes para detectar áreas débiles.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla del historial --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Listado de simulacros</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Puntaje</th>
                                <th>Estado</th>
                                <th>Duración</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($history as $exam)
                                @php
                                    $isPassed = $exam->score >= $exam->config->min_passing_score;
                                    $duration = $exam->started_at && $exam->ended_at
                                        ? $exam->started_at->diffInMinutes($exam->ended_at)
                                        : null;
                                @endphp
                                <tr>
                                    <td><strong>#{{ $exam->id }}</strong></td>
                                    <td>
                                        <small>{{ $exam->started_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong class="{{ $isPassed ? 'text-success' : 'text-danger' }}">
                                            {{ $exam->score }}%
                                        </strong>
                                    </td>
                                    <td>
                                        @if($isPassed)
                                            <span class="badge bg-success">Aprobado</span>
                                        @else
                                            <span class="badge bg-warning text-dark">No aprobado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $duration !== null ? $duration . ' min' : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <a href="{{ route('student.mock-exams.results', $exam->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Ver detalles
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        @endif

    </div>
@endsection

@section('scripts')
    @if(count($evolutionData) >= 2)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            const evolutionData = @json($evolutionData);

            const labels = evolutionData.map(d => d.date);
            const scores = evolutionData.map(d => d.score);
            const minPassing = evolutionData[0]?.min ?? 60;

            const ctx = document.getElementById('evolutionChart');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Puntaje obtenido',
                            data: scores,
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointBackgroundColor: scores.map(s => s >= minPassing ? '#198754' : '#dc3545'),
                        },
                        {
                            label: 'Puntaje mínimo',
                            data: scores.map(() => minPassing),
                            borderColor: '#dc3545',
                            borderDash: [8, 4],
                            pointRadius: 0,
                            fill: false,
                            tension: 0,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Puntaje (%)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Fecha del simulacro'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + '%';
                                }
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endsection
