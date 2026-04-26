@extends('student.main')

@section('content')
<div style="max-width: 720px; margin: 0 auto; padding: 2.5rem 1.5rem;">

    {{-- Header --}}
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 22px; font-weight: 500; margin: 0 0 6px;">
            Mi ruta de aprendizaje
        </h1>
        <p style="font-size: 15px; color: #6b7280; margin: 0;">
            Subtemas ordenados por prioridad y complejidad según tu diagnóstico inicial.
        </p>
    </div>

    @if(!$path)
    <p style="font-size: 15px; color: #6b7280;">
        Aún no tienes una ruta generada. Completa el diagnóstico inicial primero.
    </p>
    @else

    {{-- Métricas --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
              gap: 12px; margin-bottom: 2rem;">
        @foreach([
        ['Total de subtemas', $total],
        ['Completados', $completed],
        ['Pendientes', $pending],
        ] as $m)
        <div style="background: #f9fafb; border-radius: 8px; padding: 1rem;">
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 4px;">{{ $m[0] }}</p>
            <p style="font-size: 24px; font-weight: 500; margin: 0;">{{ $m[1] }}</p>
        </div>
        @endforeach
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

    <p style="font-size: 13px; font-weight: 500; color: #6b7280; margin: 0 0 0.75rem;
            text-transform: uppercase; letter-spacing: 0.05em;">
        Subtemas a trabajar
    </p>

    {{-- Items agrupados por módulo --}}
    @foreach($groupedItems as $topicName => $items)
    @php
    $masteryName = $items->first()->masteryLevel->name;
    $badgeBg = match($masteryName) { 'Deficiente' => '#FCEBEB', default => '#FAEEDA' };
    $badgeText = match($masteryName) { 'Deficiente' => '#A32D2D', default => '#854F0B' };
    @endphp

    <div style="margin-bottom: 1.5rem;">
        {{-- Header del módulo --}}
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <span style="font-size: 15px; font-weight: 500;">{{ $topicName }}</span>
            <span style="font-size: 12px; padding: 3px 10px; border-radius: 20px;
                   background: {{ $badgeBg }}; color: {{ $badgeText }};">
                {{ $masteryName }}
            </span>
        </div>

        {{-- Items del módulo --}}
        <div style="display: flex; flex-direction: column; gap: 6px;">
            @foreach($items as $item)
            <div style="display: flex; align-items: center; gap: 12px; padding: 0.625rem 0.875rem;
                  background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
                  {{ $item->is_completed ? 'opacity: 0.5;' : '' }}">

                {{-- Círculo check --}}
                <div style="width: 16px; height: 16px; border-radius: 50%; flex-shrink: 0;
                    {{ $item->is_completed
                      ? 'background: #639922; border: 1.5px solid #639922;'
                      : 'border: 1.5px solid #d1d5db;' }}">
                </div>

                <span style="font-size: 12px; color: #9ca3af; min-width: 24px;">
                    {{ $item->order }}
                </span>

                <span style="font-size: 14px; flex: 1;">
                    {{ $item->subtopic->name }}
                </span>

                <span style="font-size: 12px; color: #9ca3af;">
                    {{ $item->subtopic->complexity->name }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    @endif
</div>
@endsection
