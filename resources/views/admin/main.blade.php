@extends('layouts.app')

@section('title', 'IngeniaMath - Admin')
@section('navbar-color', 'bg-dark')
@section('footer-color', 'bg-dark')
@section('home-route', route('admin.dashboard'))

@section('menu')
    {{-- <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('admin.dashboard') }}">Inicio</a>
    </li> --}}
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('users.index') }}">Gestión de usuarios</a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white" href=#>Funci 2</a>
    </li>
@endsection
