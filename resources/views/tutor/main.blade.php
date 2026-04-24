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
<li class="nav-item">
    <a class="nav-link text-white" href="{{ route('tutor.forum.index') }}">
        Foro
    </a>
</li>
@endsection
