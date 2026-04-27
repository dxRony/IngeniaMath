@extends('layouts.app')

@section('title', 'IngeniaMath - Student')
@section('navbar-color', 'bg-success')
@section('footer-color', 'bg-success')
@section('home-route', route('student.dashboard'))

@section('menu')
<li class="nav-item">
    <a class="nav-link text-white" href="{{ route('student.dashboard') }}">
        Inicio
    </a>
</li>
<li class="nav-item">
    <a class="nav-link text-white" href="{{ route('learning-paths.index') }}">
        Ruta de aprendizaje
    </a>
</li>
<li class="nav-item">
    <a class="nav-link text-white" href="{{ route('weekly-plans.index') }}">
        Plan semanal
    </a>
</li>
<li class="nav-item">
    <a class="nav-link text-white" href="{{ route('initial-assessments.results') }}">
        Examen diagnóstico
    </a>
</li>
<li class="nav-item">
    <a class="nav-link text-white" href="{{ route('student.forum.index') }}">
        Foro
    </a>
</li>
@endsection
