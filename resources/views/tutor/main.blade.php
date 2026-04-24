@extends('layouts.app')

@section('title', 'IngeniaMath - Tutor')
@section('navbar-color', 'bg-primary')
@section('footer-color', 'bg-primary')
@section('home-route', route('tutor.dashboard'))

@section('menu')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('tutor.dashboard') }}">
            Inicio
        </a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
            Ejercicios
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{route('tutor.exercises.create')}}">Crear ejercicio</a></li>
            <li><a class="dropdown-item" href="{{route('tutor.exercises.index')}}">Ver mis ejercicios</a></li>
        </ul>
    </li>
    <li class="nav-item">
      <a class="nav-link text-white" href="{{ route('tutor.forum.index') }}">
          Foro
      </a>
    </li>
@endsection
