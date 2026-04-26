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
    <a class="nav-link text-white" href="{{ route('student.forum.index') }}">
        Foro
    </a>
</li>
<li class="nav-item">
    <a class="nav-link text-white" href="{{ route('initial-assessments.results') }}">
        Examen diagnóstico
    </a>
</li>
@endsection
