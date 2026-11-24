@extends('admin.main')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xxl-10">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
            <div>
                <h1 class="fw-bold mb-1">Crear nuevo usuario</h1>
                <p class="text-muted mb-0">Registra una cuenta con datos básicos y rol asignado.</p>
            </div>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="card shadow-sm border-0">
            @csrf
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 px-lg-5">
                <h2 class="h5 mb-1">Datos de la cuenta</h2>
                <p class="text-muted mb-0">Completa los campos para crear el acceso del usuario.</p>
                <hr>
            </div>
            @include('admin.users.partials.form', [
            'submitButton' => 'Crear Usuario'
            ])
        </form>
    </div>
</div>
@endsection
