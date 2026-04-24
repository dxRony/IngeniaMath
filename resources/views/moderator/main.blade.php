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
<li class="nav-item">
    <a class="nav-link text-white" href="{{ route('moderator.forum.index') }}">
        Foro
    </a>
</li>
@endsection
