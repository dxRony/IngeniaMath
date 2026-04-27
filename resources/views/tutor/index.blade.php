@extends('tutor.main')

@section('content')
    <div class="mb-4">
        <h1 class="fw-bold">Bienvenido Tutor</h1>
        <p class="text-muted">
            Consulta el desempeño general de tus ejercicios y detecta módulos o preguntas con mayor dificultad para los estudiantes.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="mb-4">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Ejercicios creados</small>
                        <h2 class="fw-bold text-primary">{{ $totalExercises }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Aprobados</small>
                        <h2 class="fw-bold text-success">{{ $approvedExercises }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">En revisión</small>
                        <h2 class="fw-bold text-warning">{{ $reviewExercises }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <small class="text-muted">Borradores</small>
                        <h2 class="fw-bold text-secondary">{{ $draftExercises }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <strong>Estadísticas grupales de estudiantes</strong>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3">
                        <small class="text-muted">Respuestas totales</small>
                        <h3 class="fw-bold">{{ $groupStats['total_answers'] }}</h3>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Correctas</small>
                        <h3 class="fw-bold text-success">{{ $groupStats['correct_answers'] }}</h3>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Incorrectas</small>
                        <h3 class="fw-bold text-danger">{{ $groupStats['incorrect_answers'] }}</h3>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted">Porcentaje de acierto</small>
                        <h3 class="fw-bold text-primary">{{ $groupStats['accuracy_percentage'] }}%</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white">
                <strong>Módulos con mayor tasa de error</strong>
            </div>

            <div class="card-body">
                @if($modulesWithHighestError->isEmpty())
                    <div class="alert alert-info mb-0">
                        Aún no hay respuestas registradas para calcular errores por módulo.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th>Módulo</th>
                                <th>Respuestas</th>
                                <th>Errores</th>
                                <th>Tasa de error</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($modulesWithHighestError as $module)
                                <tr>
                                    <td>{{ $module->topic_name }}</td>
                                    <td>{{ $module->total_answers }}</td>
                                    <td>{{ $module->total_errors }}</td>
                                    <td>
                                            <span class="badge bg-danger">
                                                {{ $module->error_percentage }}%
                                            </span>
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
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <strong>Ejercicios con mayor porcentaje de respuestas incorrectas</strong>

                <button type="button" class="btn btn-sm btn-dark" onclick="window.print()">
                    Exportar / Imprimir reporte
                </button>
            </div>

            <div class="card-body">
                @if($exercisesWithHighestError->isEmpty())
                    <div class="alert alert-info mb-0">
                        Aún no hay respuestas registradas para calcular errores por ejercicio.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Módulo</th>
                                <th>Subtema</th>
                                <th>Enunciado</th>
                                <th>Respuestas</th>
                                <th>Errores</th>
                                <th>Tasa de error</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($exercisesWithHighestError as $exercise)
                                <tr>
                                    <td>#{{ $exercise->id }}</td>
                                    <td>{{ $exercise->topic_name }}</td>
                                    <td>{{ $exercise->subtopic_name }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($exercise->statement, 80) }}</td>
                                    <td>{{ $exercise->total_answers }}</td>
                                    <td>{{ $exercise->total_errors }}</td>
                                    <td>
                                            <span class="badge bg-danger">
                                                {{ $exercise->error_percentage }}%
                                            </span>
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
@endsection
