@extends('admin.main')

@section('content')
    <div class="mb-4">
        <h1 class="fw-bold text-warning">Bienvenido Administrador</h1>
        <p class="text-muted">
            Vista general del uso de la plataforma, usuarios, ejercicios, simulacros y actividad reciente.
        </p>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <section class="mb-4">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Usuarios registrados</small>
                        <h2 class="fw-bold text-primary">{{ $totalUsers }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Ejercicios resueltos</small>
                        <h2 class="fw-bold text-success">{{ $totalSolvedExercises }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Simulacros realizados</small>
                        <h2 class="fw-bold text-danger">{{ $totalMockExams }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <strong>Actividad reciente del sistema</strong>

                <button type="button" class="btn btn-sm btn-light" onclick="window.print()">
                    Exportar / Imprimir reporte
                </button>
            </div>

            <div class="card-body">
                @if($recentSolvedExercises->isEmpty())
                    <div class="alert alert-info mb-0">
                        No hay ejercicios resueltos recientemente.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Módulo</th>
                                <th>Subtema</th>
                                <th>Resultado</th>
                                <th>Fecha</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($recentSolvedExercises as $sessionExercise)
                                <tr>
                                    <td>
                                        {{ $sessionExercise->session->student->first_name ?? '' }}
                                        {{ $sessionExercise->session->student->last_name ?? '' }}
                                    </td>

                                    <td>
                                        {{ $sessionExercise->exercise->subtopic->topic->name ?? 'No definido' }}
                                    </td>

                                    <td>
                                        {{ $sessionExercise->exercise->subtopic->name ?? 'No definido' }}
                                    </td>

                                    <td>
                                        @if($sessionExercise->is_correct)
                                            <span class="badge bg-success">Correcto</span>
                                        @else
                                            <span class="badge bg-danger">Incorrecto</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $sessionExercise->answered_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-dark text-white">
                        <strong>Uso por rol</strong>
                    </div>

                    <div class="card-body">
                        @if($usersByRole->isEmpty())
                            <div class="alert alert-info mb-0">
                                No hay usuarios registrados por rol.
                            </div>
                        @else
                            <table class="table table-hover align-middle">
                                <thead>
                                <tr>
                                    <th>Rol</th>
                                    <th>Total usuarios</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($usersByRole as $role)
                                    <tr>
                                        <td>{{ $role->role_name }}</td>
                                        <td>
                                                <span class="badge bg-primary">
                                                    {{ $role->total }}
                                                </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-warning text-dark">
                        <strong>Estado del banco de ejercicios</strong>
                    </div>

                    <div class="card-body">
                        @if($exerciseBankStatus->isEmpty())
                            <div class="alert alert-info mb-0">
                                No hay ejercicios registrados.
                            </div>
                        @else
                            <table class="table table-hover align-middle">
                                <thead>
                                <tr>
                                    <th>Estado</th>
                                    <th>Total ejercicios</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($exerciseBankStatus as $status)
                                    <tr>
                                        <td>{{ $status->status_name }}</td>
                                        <td>
                                                <span class="badge bg-dark">
                                                    {{ $status->total }}
                                                </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
