@extends('student.main')

@section('content')
    <div class="container py-4">
        <div class="mb-4">
            <h2 class="fw-bold">Práctica Libre</h2>
            <p class="text-muted">
                Selecciona un módulo, subtema y nivel para iniciar una sesión de práctica.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Revisa los campos del formulario.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm mt-4">
            <div class="card-header bg-secondary text-white">
                <strong>Mis sesiones de práctica</strong>
            </div>

            <div class="card-body">
                @if (($sessions ?? collect())->isEmpty())
                    <div class="alert alert-info mb-0">
                        Todavía no has iniciado ninguna práctica.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Módulo</th>
                                <th>Nivel</th>
                                <th>Estado</th>
                                <th>Progreso</th>
                                <th>Aciertos</th>
                                <th class="text-end">Acción</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($sessions as $session)
                                @php
                                    $answered = $session->sessionExercises->whereNotNull('answered_at')->count();
                                    $total = $session->sessionExercises->count();
                                    $percentage = $total > 0
                                        ? round(($session->total_correct / $total) * 100, 2)
                                        : 0;
                                @endphp

                                <tr>
                                    <td>
                                        {{ $session->started_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                    </td>

                                    <td>
                                        {{ $session->topic->name ?? 'No definido' }}
                                    </td>

                                    <td>
                                        {{ $session->initialDifficulty->name ?? 'No definido' }}
                                    </td>

                                    <td>
                                        {{ $session->status->name ?? 'No definido' }}
                                    </td>

                                    <td>
                                        {{ $answered }} / {{ $total }}
                                    </td>

                                    <td>
                                        {{ $percentage }}%
                                    </td>

                                    <td class="text-end">
                                        <a
                                            href="{{ route('student.practice.sessions.show', $session) }}"
                                            class="btn btn-sm btn-primary"
                                        >
                                            {{ $answered < $total ? 'Continuar' : 'Ver resumen' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
