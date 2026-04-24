@php
$roleId = (int) Auth::user()->role_id;
$currentUserId = Auth::user()->id;
$isThreadCreator = $currentUserId === $thread->created_by;
$isTutor = $roleId === 3;
$isModerator = $roleId === 2;

$layout = match ($roleId) {
1 => 'admin.main',
2 => 'moderator.main',
3 => 'tutor.main',
4 => 'student.main',
default => 'layouts.app',
};

$topic = $thread->subtopic?->topic;
$borderClass = $topic?->forum_border_class ?? 'border-secondary';
$creatorProfilePhoto = $thread->creator?->profile_photo_url
? (str_starts_with($thread->creator->profile_photo_url, 'img/')
? asset($thread->creator->profile_photo_url)
: Storage::url($thread->creator->profile_photo_url))
: asset('img/default_pp.png');
@endphp

@extends($layout)

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xxl-10">
        @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        <!-- Header del Hilo -->
        <div class="mb-4">
            <div class="mb-3">

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if ($isModerator)
                        <a href="{{ route('moderator.forum.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Volver al foro
                        </a>
                        @elseif ($isTutor)
                        <a href="{{ route('tutor.forum.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Volver al foro
                        </a>
                        @else
                        <a href="{{ route('student.forum.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Volver al foro
                        </a>
                        @endif
                    </div>

                    @if ($isModerator && !$thread->is_closed)
                    <div>
                        <form action="{{ route('moderator.threads.close', $thread->id) }}" method="POST"
                            class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning btn-sm">Cerrar Hilo</button>
                        </form>
                    </div>
                    @endif
                </div>

                @if ($thread->is_closed)
                <div class="alert alert-warning mb-4 mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i> Este hilo fue cerrado.
                </div>
                @endif

            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">
                            <span class="badge bg-light text-dark">{{ $topic?->name ?? 'Sin tema' }}</span>
                            <span class="badge bg-light text-dark ms-2">{{ $thread->subtopic?->name ?? 'Sin subtema'
                                }}</span>
                        </small>
                    </div>

                    <h1 class="h3 fw-bold mb-3">{{ $thread->title }}</h1>

                    @if($thread->description)
                    <p class="text-muted mb-3">{{ $thread->description }}</p>
                    @endif

                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $creatorProfilePhoto }}" alt="Foto de perfil" class="rounded-circle" width="40"
                            height="40">
                        <div class="flex-grow-1">
                            @if($thread->creator)
                            <a href="{{ route('profile.public.show', $thread->creator->id) }}"
                                class="mb-0 fw-semibold text-decoration-none d-inline-block">
                                {{ $thread->creator->first_name ?? 'Usuario desconocido' }} {{
                                $thread->creator->last_name ?? '' }}
                            </a>
                            @else
                            <p class="mb-0 fw-semibold">Usuario desconocido</p>
                            @endif
                            <small class="text-muted">
                                {{ $thread->created_at->locale('es')->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <h2 class="h4 fw-bold mb-1">Preguntas del hilo</h2>
            <p class="text-muted mb-0">Las respuestas aparecen debajo de cada pregunta.</p>
        </div>

        @forelse($thread->questions as $question)
        @php
        $questionPhotoUrl = null;
        if ($question->resource_url) {
        $questionPhotoUrl = str_starts_with($question->resource_url, 'forum/questions/')
        ? Storage::url($question->resource_url)
        : asset($question->resource_url);
        }
        @endphp

        <div class="card border-1 {{ $borderClass }} shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                    <div>
                        <small class="badge bg-light text-dark mb-2">Pregunta #{{ $question->order }}</small>
                        <p class="mb-0 fw-semibold text-muted">
                            {{ $question->created_at->locale('es')->diffForHumans() }}
                        </p>
                    </div>

                    @if($isTutor && !$question->trashed() && !$thread->is_closed)
                    <button class="btn btn-outline-primary btn-sm" type="button"
                        id="toggleReplyForm-{{ $question->id }}" onclick="toggleReplyForm({{ $question->id }})">
                        <i class="bi bi-chat-left-quote me-1"></i> Responder pregunta
                    </button>
                    @elseif($isModerator && !$question->trashed())
                    <button type="button" class="btn btn-outline-danger btn-sm"
                        onclick="openDeleteModal('{{ route('moderator.questions.destroy', $question->id) }}', 'pregunta')">
                        <i class="bi bi-trash me-1"></i> Eliminar pregunta
                    </button>
                    @endif
                </div>

                @if($question->trashed())
                <div class="alert alert-secondary mb-3" role="alert">
                    Esta pregunta fue eliminada por incumplir con las normas de la comunidad.
                </div>
                @else
                <p class="mb-3">{{ $question->body }}</p>
                @endif

                @if($questionPhotoUrl && !$question->trashed())
                <div class="mb-3">
                    <img src="{{ $questionPhotoUrl }}" alt="Imagen de la pregunta" class="img-fluid rounded"
                        style="max-height: 300px; object-fit: contain;">
                </div>
                @endif

                @if($isTutor && !$question->trashed())
                <div class="mt-3" id="replyFormContainer-{{ $question->id }}" style="display: none;">
                    <div class="card border-primary shadow-sm">
                        <div class="card-header bg-light border-primary">
                            <h5 class="mb-0 fw-semibold">Responder pregunta</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('tutor.question-replies.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="question_id" value="{{ $question->id }}">

                                <div class="mb-3">
                                    <label for="replyBody-{{ $question->id }}" class="form-label">Tu respuesta</label>
                                    <textarea class="form-control" id="replyBody-{{ $question->id }}" name="body"
                                        rows="5" placeholder="Escribe tu respuesta aquí..." required></textarea>
                                    <small class="text-muted">Máximo 5000 caracteres</small>
                                </div>

                                <div class="mb-3">
                                    <label for="replyImage-{{ $question->id }}" class="form-label">Imagen
                                        (opcional)</label>
                                    <input type="file" class="form-control" id="replyImage-{{ $question->id }}"
                                        name="resource_url" accept="image/*">
                                    <small class="text-muted">JPG, PNG, GIF. Máximo 2MB</small>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i> Publicar respuesta
                                    </button>
                                    <button type="button" class="btn btn-secondary"
                                        onclick="toggleReplyForm({{ $question->id }})">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-4">
                    <h3 class="h6 fw-bold mb-3">Respuestas</h3>

                    @forelse($question->replies as $reply)
                    @php
                    $replyPhotoUrl = null;
                    if ($reply->resource_url) {
                    $replyPhotoUrl = str_starts_with($reply->resource_url, 'forum/replies/')
                    ? Storage::url($reply->resource_url)
                    : asset($reply->resource_url);
                    }
                    $replyCreatorPhoto = $reply->creator?->profile_photo_url
                    ? (str_starts_with($reply->creator->profile_photo_url, 'img/')
                    ? asset($reply->creator->profile_photo_url)
                    : Storage::url($reply->creator->profile_photo_url))
                    : asset('img/default_pp.png');
                    @endphp

                    <div class="card mb-3 {{ $reply->is_accepted ? 'border-success' : 'border-light' }}">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $replyCreatorPhoto }}" alt="Foto de perfil"
                                        class="rounded-circle border"
                                        style="width: 36px; height: 36px; object-fit: cover;">
                                    <div>
                                        @if($reply->creator)
                                        <a href="{{ route('profile.public.show', $reply->creator?->id) }}"
                                            class="mb-0 fw-semibold text-decoration-none d-inline-block">
                                            {{ $reply->creator?->first_name ?? 'Usuario desconocido' }} {{
                                            $reply->creator?->last_name ?? '' }}
                                        </a>
                                        @else
                                        <p class="mb-0 fw-semibold">Usuario desconocido</p>
                                        @endif
                                        <small class="text-muted">{{ $reply->created_at->locale('es')->diffForHumans()
                                            }}</small>
                                    </div>
                                </div>

                                @if($isThreadCreator && !$reply->is_accepted && !$reply->trashed())
                                <form action="{{ route('student.question-replies.accept', $reply->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-check-circle me-1"></i> Aceptar respuesta
                                    </button>
                                </form>
                                @elseif($roleId === 2 && !$reply->trashed())
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                    onclick="openDeleteModal('{{ route('moderator.question-replies.destroy', $reply->id) }}', 'respuesta')">
                                    <i class="bi bi-trash me-1"></i> Eliminar respuesta
                                </button>
                                @endif
                            </div>

                            @if($reply->trashed())
                            <div class="alert alert-secondary mb-3" role="alert">
                                Esta respuesta fue eliminada por incumplir con las normas de la comunidad.
                            </div>
                            @else
                            <p class="mb-3">{{ $reply->body }}</p>
                            @endif

                            @if($replyPhotoUrl && !$reply->trashed())
                            <div class="mb-3">
                                <img src="{{ $replyPhotoUrl }}" alt="Imagen de la respuesta" class="img-fluid rounded"
                                    style="max-height: 260px; object-fit: contain;">
                            </div>
                            @endif

                            @if($reply->is_accepted)
                            <span class="badge text-bg-success">Respuesta aceptada</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-muted mb-0">Aún no hay respuestas para esta pregunta.</p>
                    @endforelse
                </div>
            </div>
        </div>
        @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-0">Este hilo todavía no tiene preguntas.</p>
            </div>
        </div>
        @endforelse

        @if($isThreadCreator && !$thread->is_closed)
        <div class="mb-4">
            <button class="btn btn-outline-success w-100" type="button" id="toggleQuestionForm"
                onclick="toggleQuestionForm()">
                <i class="bi bi-plus-circle me-2"></i> Publicar otra pregunta
            </button>

            <div id="questionFormContainer" class="mt-3" style="display: none;">
                <div class="card border-success shadow-sm">
                    <div class="card-header bg-light border-success">
                        <h5 class="mb-0 fw-semibold">Nueva pregunta</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('student.questions.store') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="thread_id" value="{{ $thread->id }}">

                            <div class="mb-3">
                                <label for="questionBody" class="form-label">Tu pregunta</label>
                                <textarea class="form-control" id="questionBody" name="body" rows="5"
                                    placeholder="Escribe tu pregunta aquí..." required></textarea>
                                <small class="text-muted">Máximo 5000 caracteres</small>
                            </div>

                            <div class="mb-3">
                                <label for="questionImage" class="form-label">Imagen (opcional)</label>
                                <input type="file" class="form-control" id="questionImage" name="resource_url"
                                    accept="image/*">
                                <small class="text-muted">JPG, PNG, GIF. Máximo 2MB</small>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i> Publicar pregunta
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="toggleQuestionForm()">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar esta <span id="deleteItemType">publicación</span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="confirmDeleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleReplyForm(questionId) {
    const container = document.getElementById(`replyFormContainer-${questionId}`);
    const button = document.getElementById(`toggleReplyForm-${questionId}`);

    if (!container || !button) {
        return;
    }

    const isHidden = container.style.display === 'none' || container.style.display === '';

    if (isHidden) {
        container.style.display = 'block';
        button.innerHTML = '<i class="bi bi-chevron-up me-1"></i> Ocultar formulario';
    } else {
        container.style.display = 'none';
        button.innerHTML = '<i class="bi bi-chat-left-quote me-1"></i> Responder pregunta';
    }
}

function toggleQuestionForm() {
    const container = document.getElementById('questionFormContainer');
    const button = document.getElementById('toggleQuestionForm');

    if (!container || !button) {
        return;
    }

    const isHidden = container.style.display === 'none' || container.style.display === '';

    if (isHidden) {
        container.style.display = 'block';
        button.innerHTML = '<i class="bi bi-chevron-up me-2"></i> Ocultar formulario';
    } else {
        container.style.display = 'none';
        button.innerHTML = '<i class="bi bi-plus-circle me-2"></i> Publicar otra pregunta';
    }
}

function openDeleteModal(actionUrl, itemType) {
    const deleteForm = document.getElementById('confirmDeleteForm');
    const itemTypeText = document.getElementById('deleteItemType');

    if (!deleteForm || !itemTypeText) {
        return;
    }

    deleteForm.setAttribute('action', actionUrl);
    itemTypeText.textContent = itemType;

    const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    deleteModal.show();
}
</script>
@endsection
