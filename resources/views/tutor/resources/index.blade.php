@extends('tutor.main')

@section('content')
    <style>
        .resource-row { transition: background .15s; }
        .resource-row:hover { background: #f8f9fa; }
        .status-badge {
            font-size: .75rem;
            font-weight: 500;
            padding: .25rem .6rem;
            border-radius: 1rem;
        }
        .status-1 { background: #fff3cd; color: #664d03; }
        .status-2 { background: #d1e7dd; color: #0a3622; }
        .status-3 { background: #f8d7da; color: #58151c; }
        .status-4 { background: #e2e3e5; color: #41464b; }
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

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Mis recursos educativos</h3>
            <small class="text-muted">Gestiona los recursos que has subido</small>
        </div>
        <a href="{{ route('tutor.resources.create') }}" class="btn btn-primary">
            + Crear nuevo recurso
        </a>
    </div>

    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <a class="nav-link {{ !$statusFilter ? 'active' : '' }}"
               href="{{ route('tutor.resources.index') }}">
                Todos <span class="badge bg-secondary ms-1">{{ $counts['all'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $statusFilter === 'pending' ? 'active' : '' }}"
               href="{{ route('tutor.resources.index', ['status' => 'pending']) }}">
                Pendientes <span class="badge bg-warning text-dark ms-1">{{ $counts['pending'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $statusFilter === 'approved' ? 'active' : '' }}"
               href="{{ route('tutor.resources.index', ['status' => 'approved']) }}">
                Aprobados <span class="badge bg-success ms-1">{{ $counts['approved'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $statusFilter === 'rejected' ? 'active' : '' }}"
               href="{{ route('tutor.resources.index', ['status' => 'rejected']) }}">
                Rechazados <span class="badge bg-danger ms-1">{{ $counts['rejected'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $statusFilter === 'deleted' ? 'active' : '' }}"
               href="{{ route('tutor.resources.index', ['status' => 'deleted']) }}">
                Eliminados <span class="badge bg-secondary ms-1">{{ $counts['deleted'] }}</span>
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
                            <th>Estado</th>
                            <th>Creado</th>
                            <th style="width:170px;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($resources as $resource)
                            <tr class="resource-row">
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
                                <td>
                                    <strong>{{ $resource->title }}</strong>
                                    @if ($resource->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($resource->description, 80) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $resource->type?->name }}</small>
                                </td>
                                <td>
                                    @foreach ($resource->subtopics as $subtopic)
                                        <small class="d-block">
                                            <span class="text-muted">{{ $subtopic->topic?->name }}</span> →
                                            {{ $subtopic->name }}
                                        </small>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $resource->status_id }}">
                                        {{ $resource->status?->name }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $resource->created_at?->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>
                                    @if ($resource->status_id !== 4)
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('tutor.resources.edit', $resource->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                Editar
                                            </a>
                                            <form action="{{ route('tutor.resources.destroy', $resource->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este recurso? Ya no será visible ni se podrá editar.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted mb-3">
                        @if ($statusFilter === 'pending')
                            No tienes recursos pendientes de revisión.
                        @elseif ($statusFilter === 'approved')
                            No tienes recursos aprobados todavía.
                        @elseif ($statusFilter === 'rejected')
                            No tienes recursos rechazados.
                        @elseif ($statusFilter === 'deleted')
                            No tienes recursos eliminados.
                        @else
                            Aún no has creado ningún recurso.
                        @endif
                    </p>
                    <a href="{{ route('tutor.resources.create') }}" class="btn btn-primary">
                        Crear mi primer recurso
                    </a>
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
