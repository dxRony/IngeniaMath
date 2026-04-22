@extends('admin.main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold">Gestión de usuarios</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        Nuevo usuario
    </a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        @if($users->isEmpty())
        <p class="mb-0">Aún no hay usuarios registrados.</p>
        @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Correo</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                    <tr>
                        <td class="align-middle">{{ $index + 1 }}</td>
                        <td class="align-middle">{{ $user->first_name }}</td>
                        <td class="align-middle">{{ $user->last_name }}</td>
                        <td class="align-middle">{{ $user->email }}</td>
                        <td class="align-middle">{{ ucfirst($user->role->real_name) }}</td>
                        <td class="align-middle">
                            @if ($user->is_active)
                            <span class="badge bg-success">Activo</span>
                            @else
                            <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm">Editar</a>

                                <form action="{{ route('users.toggle-state', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if ($user->is_active)
                                    <button type="submit" class="btn btn-warning btn-sm">Desactivar</button>
                                    @else
                                    <button type="submit" class="btn btn-success btn-sm">Activar</button>
                                    @endif
                                </form>

                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteUserModal"
                                    data-delete-url="{{ route('users.destroy', $user->id) }}"
                                    data-user-name="{{ $user->first_name }} {{ $user->last_name }}"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Seguro que deseas eliminar a <strong id="deleteUserName">este usuario</strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteUserForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const deleteUserModal = document.getElementById('deleteUserModal');

    if (deleteUserModal) {
        deleteUserModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const deleteUrl = button.getAttribute('data-delete-url');
            const userName = button.getAttribute('data-user-name');

            const deleteForm = deleteUserModal.querySelector('#deleteUserForm');
            const deleteUserName = deleteUserModal.querySelector('#deleteUserName');

            deleteForm.setAttribute('action', deleteUrl);
            deleteUserName.textContent = userName;
        });
    }
</script>
@endsection
