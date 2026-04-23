@extends('layouts.app')

@section('title', 'IngeniaMath - Cuenta desactivada')
@section('navbar-color', 'bg-dark')
@section('footer-color', 'bg-dark')
@section('home-route', route('home'))

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-7">
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body p-4 p-lg-5 text-center">
                <div class="mb-3">
                    <i class="bi bi-shield-lock fs-1 text-warning"></i>
                </div>
                <h1 class="h3 fw-bold mb-3">Cuenta desactivada</h1>
                <p class="text-muted mb-2">
                    Tu cuenta se encuentra desactivada y por el momento no puedes acceder a las funcionalidades de la plataforma.
                </p>
                <p class="text-muted mb-4">
                    Por favor, comunicate con algún administrador para solicitar la reactivacion.
                </p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">Cerrar sesion</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
