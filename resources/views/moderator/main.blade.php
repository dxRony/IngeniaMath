@extends('layouts.app')
@section('title', 'IngeniaMath - Moderator')
@section('navbar-color', 'bg-secondary')
@section('footer-color', 'bg-secondary')
@section('home-route', route('moderator.dashboard'))
@section('menu')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('moderator.dashboard') }}">
            Inicio
        </a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
            Ejercicios
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('moderator.exercises.review.index') }}">Revisar ejercicios</a></li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
            Recursos
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('moderator.resources.index') }}">Revisar recursos</a></li>
        </ul>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('moderator.forum.index') }}">
            Foro
        </a>
    </li>
@endsection
