@extends('admin.main')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Configuraciones de simulacro</h3>
            <small class="text-muted">Define la duración, cantidad de preguntas y distribución por módulos del simulacro de examen.</small>
        </div>
        <a href="{{ route('admin.exam-configs.create') }}" class="btn btn-primary">
            + Nueva configuración
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if ($configs->count() > 0)
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Duración</th>
                            <th>Preguntas</th>
                            <th>Puntaje mínimo</th>
                            <th>Distribución</th>
                            <th>Estado</th>
                            <th>Creada</th>
                            <th style="width: 220px;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($configs as $config)
                            <tr>
                                <td><strong>#{{ $config->id }}</strong></td>
                                <td>{{ $config->duration_minutes }} min</td>
                                <td>{{ $config->total_questions }}</td>
                                <td>{{ $config->min_passing_score }}%</td>
                                <td>
                                    <small>
                                        @foreach ($config->topicDistributions as $dist)
                                            {{ $dist->topic?->name ?? '?' }}: {{ $dist->question_count }}@if (!$loop->last), @endif
                                        @endforeach
                                    </small>
                                </td>
                                <td>
                                    @if ($config->is_active)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $config->created_at?->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.exam-configs.edit', $config->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Editar
                                        </a>
                                        @if (!$config->is_active)
                                            <form action="{{ route('admin.exam-configs.activate', $config->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Activar esta configuración? Se desactivarán las demás.')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    Activar
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.exam-configs.deactivate', $config->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Desactivar? Los estudiantes no podrán iniciar simulacros hasta activar otra.')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    Desactivar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted mb-3">No hay configuraciones creadas aún.</p>
                    <a href="{{ route('admin.exam-configs.create') }}" class="btn btn-primary">
                        Crear la primera configuración
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if ($configs->hasPages())
        <div class="mt-3 d-flex justify-content-center">
            {{ $configs->links() }}
        </div>
    @endif
@endsection
