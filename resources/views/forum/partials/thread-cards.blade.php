<div class="row g-3">
    @forelse($threads as $thread)
    @php
    $roleId = (int) Auth::user()->role_id;
    $topic = $thread->subtopic?->topic;
    $borderClass = $topic?->forum_border_class ?? 'border-secondary';
    $creator = $thread->creator;
    $questionsCount = (int) $thread->questions_count;
    $totalRepliesCount = (int) ($thread->total_replies_count ?? 0);
    $acceptedRepliesCount = (int) ($thread->accepted_replies_count ?? 0);

    if ($creator?->profile_photo_url) {
        $profilePhoto = str_starts_with($creator->profile_photo_url, 'img/')
            ? asset($creator->profile_photo_url)
            : Storage::url($creator->profile_photo_url);
    } else {
        $profilePhoto = asset('img/default_pp.png');
    }
    @endphp

    <div class="col-12">
        <div class="card shadow-sm border-start border-4 {{ $borderClass }} h-100">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-between">
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            @if (Auth::id() === $thread->created_by)
                            <span class="badge text-bg-primary">Tu hilo</span>
                            @endif
                            <span class="badge text-bg-light border">{{ $topic?->name ?? 'Sin módulo' }}</span>
                            <span class="badge text-bg-light border">{{ $thread->subtopic?->name ?? 'Sin subtema' }}</span>
                        </div>

                        <h2 class="h5 mb-2">{{ $thread->title }}</h2>

                        @if($thread->description)
                        <p class="text-muted mb-0">{{ $thread->description }}</p>
                        @else
                        <p class="text-muted fst-italic mb-0">Sin descripción.</p>
                        @endif

                        <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                            <span class="badge {{ $thread->is_closed ? 'text-bg-danger' : 'text-bg-success' }}">
                                {{ $thread->is_closed ? 'Hilo cerrado' : 'Hilo abierto' }}
                            </span>
                            <span class="badge text-bg-light border">
                                {{ $questionsCount }} {{ $questionsCount === 1 ? 'pregunta' : 'preguntas' }}
                            </span>
                            <span class="badge text-bg-light border">
                                {{ $totalRepliesCount }} {{ $totalRepliesCount === 1 ? 'respuesta' : 'respuestas' }}
                            </span>
                            <span class="badge {{ $acceptedRepliesCount > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $acceptedRepliesCount }} {{ $acceptedRepliesCount === 1 ? 'aceptada' : 'aceptadas' }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex flex-md-column align-items-md-end justify-content-between gap-3" style="min-width: 250px;">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $profilePhoto }}" alt="Foto de perfil" class="rounded-circle border" style="width: 42px; height: 42px; object-fit: cover;">
                            <div>
                                @if($creator)
                                <a href="{{ route('profile.public.show', $creator?->id) }}" class="mb-0 small fw-semibold text-decoration-none d-inline-block">
                                    {{ $creator?->first_name }} {{ $creator?->last_name }}
                                </a>
                                @else
                                <p class="mb-0 small fw-semibold">Usuario desconocido</p>
                                @endif
                                <p class="mb-0 text-muted" style="font-size: 0.8rem;">{{ $thread->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                        @if ($roleId === 2)
                        <a href="{{ route('moderator.threads.show', $thread->id) }}" class="btn btn-outline-primary btn-sm">
                            Ver más
                        </a>
                        @elseif ($roleId === 3)
                        <a href="{{ route('tutor.threads.show', $thread->id) }}" class="btn btn-outline-primary btn-sm">
                            Ver más
                        </a>
                        @elseif ($roleId === 4)
                        <a href="{{ route('student.threads.show', $thread->id) }}" class="btn btn-outline-primary btn-sm">
                            Ver más
                        </a>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-3">No hay hilos para este filtro.</p>
                @if ((int) Auth::user()->role_id === 4)
                <a href="{{ route('student.threads.create') }}" class="btn btn-primary">Crear primer hilo</a>
                @endif
            </div>
        </div>
    </div>
    @endforelse
</div>
