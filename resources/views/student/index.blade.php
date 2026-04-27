@extends('student.main')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-10">

  {{-- Header --}}
  <div class="mb-4">
    <h1 class="h4 fw-medium mb-1">Bienvenido, {{ Auth::user()->first_name }}</h1>
    <p class="text-muted mb-0">Panel de progreso personal</p>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- Métricas numéricas --}}
  <div class="row g-3 mb-4">
    @foreach([
      ['Ejercicios resueltos',    $totalExercises],
      ['Simulacros realizados',   $totalMockExams],
      ['Prom. simulacros',        $avgMockScore ? round($avgMockScore, 1).'%' : '—'],
      ['Racha actual',            $streak.' días'],
      ['Módulos completados',     $completedModules.' / '.($completedModules + $pendingModules)],
    ] as $m)
    <div class="col-6 col-md-4 col-lg-2">
      <div class="bg-light rounded p-3 h-100">
        <p class="text-muted small mb-1">{{ $m[0] }}</p>
        <p class="fs-5 fw-medium mb-0">{{ $m[1] }}</p>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Radar + Línea --}}
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card border rounded p-3 h-100">
        <p class="text-muted small text-uppercase fw-medium mb-3" style="letter-spacing:.05em">
          Radar de dominio
        </p>
        <canvas id="radarChart" height="220"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border rounded p-3 h-100">
        <p class="text-muted small text-uppercase fw-medium mb-3" style="letter-spacing:.05em">
          Progreso en el tiempo
        </p>
        <canvas id="lineChart" height="220"></canvas>
      </div>
    </div>
  </div>

  {{-- Barras + Top errores --}}
  <div class="row g-3 mb-3">
    <div class="col-md-7">
      <div class="card border rounded p-3 h-100">
        <p class="text-muted small text-uppercase fw-medium mb-3" style="letter-spacing:.05em">
          Comparativa por módulo
        </p>
        @foreach($topics as $i => $topic)
        @php
          $accuracy = $barData[$i] ?? 0;
          $color = $accuracy >= 80 ? '#639922' : ($accuracy >= 50 ? '#BA7517' : '#E24B4A');
        @endphp
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="text-muted small" style="min-width:130px">{{ $topic->name }}</span>
          <div class="flex-grow-1 bg-light rounded" style="height:6px;overflow:hidden">
            <div style="height:100%;width:{{ round($accuracy) }}%;background:{{ $color }};border-radius:3px"></div>
          </div>
          <span class="text-muted small" style="min-width:36px;text-align:right">
            {{ round($accuracy) }}%
          </span>
        </div>
        @endforeach
      </div>
    </div>

    <div class="col-md-5">
      <div class="card border rounded p-3 h-100">
        <p class="text-muted small text-uppercase fw-medium mb-3" style="letter-spacing:.05em">
          Top 3 módulos con mayor error
        </p>
        @forelse($topErrors as $error)
        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
          <span class="small">{{ $error['name'] }}</span>
          <span class="badge rounded-pill"
                style="background:{{ $error['error_rate'] >= 60 ? '#FCEBEB' : '#FAEEDA' }};
                       color:{{ $error['error_rate'] >= 60 ? '#A32D2D' : '#854F0B' }};">
            {{ $error['error_rate'] }}% error
          </span>
        </div>
        @empty
        <p class="text-muted small">Sin datos disponibles.</p>
        @endforelse

        <p class="text-muted small text-uppercase fw-medium mt-3 mb-2" style="letter-spacing:.05em">
          Estado de módulos
        </p>
        @php
          $dominado     = $moduleScores->filter(fn($m) => $m->masteryLevel->name === 'Dominado')->count();
          $enDesarrollo = $moduleScores->filter(fn($m) => $m->masteryLevel->name === 'En desarrollo')->count();
          $deficiente   = $moduleScores->filter(fn($m) => $m->masteryLevel->name === 'Deficiente')->count();
        @endphp
        <div class="d-flex gap-2 flex-wrap">
          <span class="badge rounded-pill" style="background:#EAF3DE;color:#3B6D11">
            Dominado: {{ $dominado }}
          </span>
          <span class="badge rounded-pill" style="background:#FAEEDA;color:#854F0B">
            En desarrollo: {{ $enDesarrollo }}
          </span>
          <span class="badge rounded-pill" style="background:#FCEBEB;color:#A32D2D">
            Deficiente: {{ $deficiente }}
          </span>
        </div>
      </div>
    </div>
  </div>

  {{-- Heatmap --}}
  <div class="card border rounded p-3 mb-3">
    <p class="text-muted small text-uppercase fw-medium mb-3" style="letter-spacing:.05em">
      Actividad de estudio
    </p>
    <div id="heatmap" class="d-flex flex-wrap gap-1"></div>
    <p class="text-muted small mt-2 mb-0">Racha actual: {{ $streak }} días consecutivos</p>
  </div>

</div>
</div>

{{-- @push('scripts') --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  const radarLabels = @json($radarLabels);
  const radarData   = @json($radarData);
  const lineLabels  = @json($lineLabels);
  const lineData    = @json($lineData);
  const barLabels   = @json($topics->pluck('name'));
  const barData     = @json($barData);
  const activityDays = @json($activityDays);

  // Radar
  new Chart(document.getElementById('radarChart'), {
    type: 'radar',
    data: {
      labels: radarLabels,
      datasets: [{
        data: radarData,
        backgroundColor: 'rgba(99,153,34,0.15)',
        borderColor: '#639922',
        pointBackgroundColor: '#639922',
        borderWidth: 1.5,
        pointRadius: 3,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { r: { min: 0, max: 100, ticks: { stepSize: 25, font: { size: 10 } },
                     pointLabels: { font: { size: 11 } } } }
    }
  });

  // Línea
  new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
      labels: lineLabels.length ? lineLabels : ['Diagnóstico'],
      datasets: [{
        label: 'Puntaje promedio',
        data: lineData.length ? lineData : [0],
        borderColor: '#639922',
        backgroundColor: 'rgba(99,153,34,0.1)',
        borderWidth: 1.5,
        pointRadius: 4,
        fill: true,
        tension: 0.3,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { min: 0, max: 100, ticks: { font: { size: 10 } } },
                x: { ticks: { font: { size: 10 } } } }
    }
  });

  // Heatmap
  const hm = document.getElementById('heatmap');
  const today = new Date();
  const colors = ['#ebedf0', '#c6e48b', '#7bc96f', '#239a3b', '#196127'];

  for (let i = 89; i >= 0; i--) {
    const d = new Date(today);
    d.setDate(d.getDate() - i);
    const key = d.toISOString().split('T')[0];
    const sessions = activityDays[key] || 0;
    const intensity = sessions === 0 ? 0 : Math.min(sessions, 4);
    const cell = document.createElement('div');
    cell.style.cssText = `width:12px;height:12px;border-radius:2px;background:${colors[intensity]}`;
    cell.title = `${key}: ${sessions} sesiones`;
    hm.appendChild(cell);
  }
</script>
{{-- @endpush --}}

@endsection
