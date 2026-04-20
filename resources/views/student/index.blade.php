@extends('student.main')

@section('content')
    <div class="text-center">
        <h1 class="fw-bold">Bienvenido, Estudiante</h1>
        <p class="lead">-descripcion rol-</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="fw-bold text-primary">-funcionalidades-</h4>
                        <p class="text-muted mb-0">
                            -funcionalidad rol-
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="fw-bold text-success">-funcionalidades-</h4>
                        <p class="text-muted mb-0">
                            -funcionalidad rol-
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="fw-bold text-danger">-funcionalidades-</h4>
                        <p class="text-muted mb-0">
                            -funcionalidad rol-
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
