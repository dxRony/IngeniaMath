@extends('student.main')

@section('content')
<div style="max-width: 720px; margin: 0 auto; padding: 2.5rem 1.5rem;">

    {{-- Header --}}
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 22px; font-weight: 500; margin: 0 0 6px;">
            Resultados del diagnóstico
        </h1>
        <p style="font-size: 15px; color: #6b7280; margin: 0;">
            Aquí está tu nivel de dominio actual por módulo. Tu ruta de aprendizaje ha sido generada.
        </p>
    </div>

    {{-- Métricas --}}
    @php
    $dominado = $moduleResults->where('masteryLevel.name', 'Dominado')->count();
    $enDesarrollo = $moduleResults->where('masteryLevel.name', 'En desarrollo')->count();
    $deficiente = $moduleResults->where('masteryLevel.name', 'Deficiente')->count();
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
              gap: 12px; margin-bottom: 2rem;">
        @foreach([
        ['Respondidas correctamente', $totalCorrect . ' / ' . $totalExercises],
        ['Módulos dominados', $dominado],
        ['En desarrollo', $enDesarrollo],
        ['Deficientes', $deficiente],
        ] as $metric)
        <div style="background: #f9fafb; border-radius: 8px; padding: 1rem;">
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 4px;">{{ $metric[0] }}</p>
            <p style="font-size: 24px; font-weight: 500; margin: 0;">{{ $metric[1] }}</p>
        </div>
        @endforeach
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

    {{-- Detalle por módulo --}}
    <p style="font-size: 13px; font-weight: 500; color: #6b7280; margin: 0 0 0.75rem;
            text-transform: uppercase; letter-spacing: 0.05em;">
        Detalle por módulo
    </p>

    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 2rem;">
        @foreach($moduleResults as $result)
        @php
        $name = $result->masteryLevel->name;
        $score = (int) $result->score;
        $badgeBg = match($name) { 'Dominado' => '#EAF3DE', 'En desarrollo' => '#FAEEDA', default => '#FCEBEB' };
        $badgeText= match($name) { 'Dominado' => '#3B6D11', 'En desarrollo' => '#854F0B', default => '#A32D2D' };
        $barColor = match($name) { 'Dominado' => '#639922', 'En desarrollo' => '#BA7517', default => '#E24B4A' };
        @endphp
        <div style="display: flex; align-items: center; gap: 12px; padding: 0.875rem 1rem;
                background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;">
            <span style="font-size: 14px; flex: 1;">{{ $result->topic->name }}</span>
            <div style="flex: 1; max-width: 160px; height: 6px; background: #f3f4f6;
                  border-radius: 3px; overflow: hidden;">
                <div style="height: 100%; width: {{ $score }}%; background: {{ $barColor }};
                    border-radius: 3px;"></div>
            </div>
            <span style="font-size: 14px; font-weight: 500; min-width: 44px; text-align: right;">
                {{ $score }}%
            </span>
            <span style="font-size: 12px; padding: 3px 10px; border-radius: 20px; min-width: 90px;
                   text-align: center; background: {{ $badgeBg }}; color: {{ $badgeText }};">
                {{ $name }}
            </span>
        </div>
        @endforeach
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

    {{-- Footer --}}
    <div style="display: flex; justify-content: flex-end;">
        <a href="{{ route('learning-paths.index') }}" style="padding: 0.625rem 1.75rem; background: #111827; color: #fff;
              border-radius: 8px; font-size: 15px; font-weight: 500; text-decoration: none;">
            Ver ruta de aprendizaje generada
        </a>
    </div>

</div>
@endsection
