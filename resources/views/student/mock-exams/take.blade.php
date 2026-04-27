@extends('layouts.assessment')

@section('title', 'Simulacro - IngeniaMath')
@section('exam-pill-text', 'Simulacro en curso')

@section('content')
    <div class="container py-4" style="max-width: 820px;">

        {{-- Timer fijo arriba --}}
        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3 sticky-top"
             style="top: 0.5rem; z-index: 100;">
            <div>
                <strong>⏱ Tiempo restante:</strong>
                <span id="js-timer" class="fw-bold fs-5">--:--</span>
            </div>
            <small class="text-muted">No salgas de esta pantalla</small>
        </div>

        {{-- Barra de progreso --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div class="w-100" style="max-width: 420px;">
                <p class="small text-muted mb-2">
                    Pregunta <strong>{{ $order }}</strong> de {{ $totalQuestions }}
                </p>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-dark" role="progressbar"
                         style="width: {{ round($order / $totalQuestions * 100, 1) }}%; transition: width 0.3s;"></div>
                </div>
            </div>
            <span class="small text-muted">
            {{ $totalQuestions - $order }} restantes
        </span>
        </div>

        <form id="mock-exam-form"
              action="{{ route('student.mock-exams.answer', ['id' => $exam->id, 'order' => $order]) }}"
              method="POST">
            @csrf

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    {{-- Badges: módulo y dificultad --}}
                    <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge rounded-pill text-bg-light border text-secondary">
                        {{ $examExercise->exercise->subtopic->topic->name }}
                    </span>
                        <span class="badge rounded-pill text-bg-light border text-secondary">
                        {{ $examExercise->exercise->level->name }}
                    </span>
                    </div>

                    {{-- Enunciado (oculto si es fill_blank, porque ahí va con el input) --}}
                    @if($examExercise->exercise->type->name !== 'Completar espacios')
                        <p class="fs-6 lh-lg mb-4">
                            {!! $examExercise->exercise->statement !!}
                        </p>
                    @endif

                    {{-- Imagen opcional --}}
                    @if($examExercise->exercise->image_url)
                        <img src="{{ Storage::url($examExercise->exercise->image_url) }}"
                             alt="Imagen de apoyo"
                             class="img-fluid rounded border mb-4"
                             style="max-height: 220px; object-fit: contain; width: 100%;">
                    @endif

                    {{-- Input según tipo --}}
                    @php $typeName = $examExercise->exercise->type->name; @endphp

                    @if($typeName === 'Opción múltiple')
                        @include('student.mock-exams.partials._multiple_choice')
                    @elseif($typeName === 'Verdadero/Falso')
                        @include('student.mock-exams.partials._true_false')
                    @elseif($typeName === 'Respuesta numérica abierta')
                        @include('student.mock-exams.partials._numeric')
                    @elseif($typeName === 'Completar espacios')
                        @include('student.mock-exams.partials._fill_blank')
                    @endif
                </div>
            </div>

            {{-- Navegación --}}
            <div class="d-flex justify-content-end">
                @if($order < $totalQuestions)
                    <button type="submit" class="btn btn-dark">
                        Siguiente →
                    </button>
                @else
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('¿Finalizar el simulacro?')">
                         Finalizar simulacro
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // ===========================================================
        // TIMER del simulacro (cliente)
        // El servidor también valida; esto solo es la UX del cronómetro
        // ===========================================================
        let secondsRemaining = {{ $secondsRemaining }};
        const timerEl = document.getElementById('js-timer');
        const examForm = document.getElementById('mock-exam-form');
        let timeoutTriggered = false;

        function updateTimerDisplay() {
            if (secondsRemaining <= 0) {
                timerEl.textContent = '00:00';
                return;
            }

            const minutes = Math.floor(secondsRemaining / 60);
            const seconds = secondsRemaining % 60;
            timerEl.textContent =
                String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

            // Cambiar color cuando queden menos de 5 minutos
            if (secondsRemaining <= 300) {
                timerEl.classList.add('text-danger');
            }
        }

        function tick() {
            secondsRemaining--;
            updateTimerDisplay();

            if (secondsRemaining <= 0 && !timeoutTriggered) {
                timeoutTriggered = true;
                // Auto-submit al endpoint de finalizar
                window.location.href = "{{ route('student.mock-exams.finish', $exam->id) }}";
            }
        }

        updateTimerDisplay();
        setInterval(tick, 1000);

        // ===========================================================
        // Anti-abandono: avisar antes de cerrar
        // ===========================================================
        window.addEventListener('beforeunload', function (e) {
            if (timeoutTriggered) return; // Si fue por timeout, no avisar
            e.preventDefault();
            e.returnValue = 'Si sales del simulacro, se cerrará automáticamente con tus respuestas actuales.';
            return e.returnValue;
        });

        // Quitar el aviso al hacer submit normal
        examForm.addEventListener('submit', function () {
            timeoutTriggered = true; // marca para que beforeunload no se active
        });
    </script>
@endpush
