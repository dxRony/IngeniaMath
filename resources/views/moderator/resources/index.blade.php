@extends('moderator.main')

@section('content')
    <style>
        .resource-row { transition: background .15s; }
        .resource-row:hover { background: #f8f9fa; cursor: pointer; }
        .status-badge {
            font-size: .75rem;
            font-weight: 500;
            padding: .25rem .6rem;
            border-radius: 1rem;
        }
        .status-1 { background: #fff3cd; color: #664d03; }
        .status-2 { background: #d1e7dd; color: #0a3622; }
        .status-3 { background: #f8d7da; color: #58151c; }
        .type-icon {
            display: inline-block;
            width: 32px;
            height: 32px;
            background: #e9ecef;
            border-radius: .25rem;
            text-align: center;
            line-height: 32px;
            font-size: 1rem;
        }
    </style>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <h3 class="mb-0">Revisión de recursos educativos</h3>
        <small class="text-muted">Aprueba o rechaza los recursos subidos por los tutores</small>
    </div>

    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $statusFilter === 'pending' ? 'active' : '' }}"
               href="{{ route('moderator.resources.index', ['status' => 'pending']) }}">
                Pendientes <span class="badge bg-warning text-dark ms-1">{{ $counts['pending'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $statusFilter === 'approved' ? 'active' : '' }}"
               href="{{ route('moderator.resources.index', ['status' => 'approved']) }}">
                Aprobados <span class="badge bg-success ms-1">{{ $counts['approved'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $statusFilter === 'rejected' ? 'active' : '' }}"
               href="{{ route('moderator.resources.index', ['status' => 'rejected']) }}">
                Rechazados <span class="badge bg-danger ms-1">{{ $counts['rejected'] }}</span>
            </a>
        </li>
    </ul>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if ($resources->count() > 0)
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                        <tr>
                            <th style="width:50px;"></th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Subtema</th>
                            <th>Creado por</th>
                            <th>Estado</th>
                            <th>Enviado</th>
                            <th style="width:120px;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($resources as $resource)
                            <tr class="resource-row"
                                onclick="window.location='{{ route('moderator.resources.show', $resource->id) }}'">
                                <td>
                                    <span class="type-icon">
                                        @switch($resource->type_id)
                                            @case(1) 🎬 @break
                                            @case(2) 📄 @break
                                            @case(3) 🃏 @break
                                            @case(4) 📐 @break
                                            @default 📎
                                        @endswitch
                                    </span>
                                </td>
                                <td><strong>{{ $resource->title }}</strong></td>
                                <td><small>{{ $resource->type?->name }}</small></td>
                                <td>
                                    @foreach ($resource->subtopics as $subtopic)
                                        <small class="d-block">
                                            <span class="text-muted">{{ $subtopic->topic?->name }}</span> →
                                            {{ $subtopic->name }}
                                        </small>
                                    @endforeach
                                </td>
                                <td>
                                    <small>
                                        {{ $resource->creator?->first_name }} {{ $resource->creator?->last_name }}
                                    </small>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $resource->status_id }}">
                                        {{ $resource->status?->name }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $resource->created_at?->format('d/m/Y H:i') }}
                                    </small>
                                </td>
                                <td onclick="event.stopPropagation()">
                                    <a href="{{ route('moderator.resources.show', $resource->id) }}"
                                       class="btn btn-sm btn-primary">
                                        Revisar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted">
                        @if ($statusFilter === 'pending')
                            No hay recursos pendientes de revisión. ¡Buen trabajo!
                        @elseif ($statusFilter === 'approved')
                            Aún no se han aprobado recursos.
                        @elseif ($statusFilter === 'rejected')
                            No hay recursos rechazados.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    @if ($resources->hasPages())
        <div class="mt-3 d-flex justify-content-center">
            {{ $resources->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
