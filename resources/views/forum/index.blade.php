@php
$roleId = (int) Auth::user()->role_id;
$layout = match ($roleId) {
1 => 'admin.main',
2 => 'moderator.main',
3 => 'tutor.main',
4 => 'student.main',
default => 'layouts.app',
};
@endphp

@extends($layout)

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xxl-10">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
            <div>
                <h1 class="fw-bold mb-1">Foro de dudas</h1>
                <p class="text-muted mb-0">Consulta hilos por tema y participa en la discusión.</p>
            </div>
            @if ($roleId === 4)
            <a href="{{ route('student.threads.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Crear hilo
            </a>
            @endif

        </div>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form id="forumFilterForm" method="GET" class="row g-3 align-items-end">
                    <div class="col-12 col-md-5 col-lg-4">
                        <label for="topicFilter" class="form-label fw-semibold mb-1">Filtrar por módulo</label>
                        <select name="topic_id" id="topicFilter" class="form-select">
                            <option value="">Todos los módulos</option>
                            @foreach($topics as $topic)
                            <option value="{{ $topic->id }}" {{ (int) ($selectedTopicId ?? 0)===(int) $topic->id ?
                                'selected' : '' }}>
                                {{ $topic->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($roleId === 4)
                    <div class="col-12 col-md-auto">
                        <div class="form-check form-switch pt-md-3">
                            <label class="form-check-label fw-semibold" for="myThreadsFilter">
                                Mostrar solo mis hilos
                            </label>
                            <input type="checkbox" class="form-check-input" id="myThreadsFilter" name="my_threads"
                                value="1" {{ $showMyThreads ? 'checked' : '' }}>
                        </div>
                    </div>
                    @endif

                    <div class="col-12 col-md-auto">
                        <button type="button" id="clearTopicFilter" class="btn btn-outline-secondary w-100">Limpiar
                            filtros</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="threadCardsContainer">
            @include('forum.partials.thread-cards', ['threads' => $threads])
        </div>

        <div id="threadPaginationContainer">
            @include('forum.partials.thread-pagination', ['threads' => $threads])
        </div>
    </div>
</div>

<script>
    (() => {
    const filterForm = document.getElementById('forumFilterForm');
    const filterSelect = document.getElementById('topicFilter');
    const myThreadsCheckbox = document.getElementById('myThreadsFilter');
    const clearButton = document.getElementById('clearTopicFilter');
    const cardsContainer = document.getElementById('threadCardsContainer');
    const paginationContainer = document.getElementById('threadPaginationContainer');

    if (!filterForm || !filterSelect || !clearButton || !cardsContainer || !paginationContainer || !myThreadsCheckbox) {
        return;
    }

    let controller = null;

    const fetchFilteredThreads = async (targetUrl = null) => {
        if (controller) {
            controller.abort();
        }

        controller = new AbortController();

        const formData = new FormData(filterForm);

        // Asegurarse de que el checkbox se incluya correctamente
        if (myThreadsCheckbox.checked) {
            formData.set('my_threads', '1');
        } else {
            formData.delete('my_threads');
        }

        const params = new URLSearchParams(formData);
        const baseUrl = filterForm.getAttribute('action') || window.location.pathname;
        const requestUrl = targetUrl || `${baseUrl}?${params.toString()}`;

        try {
            const response = await fetch(requestUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                signal: controller.signal,
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            cardsContainer.innerHTML = data.threadsHtml ?? '';
            paginationContainer.innerHTML = data.paginationHtml ?? '';
            window.history.replaceState({}, '', requestUrl);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        }
    };

    // Event listeners
    filterSelect.addEventListener('change', () => {
        fetchFilteredThreads();
    });

    myThreadsCheckbox.addEventListener('change', () => {
        fetchFilteredThreads();
    });

    clearButton.addEventListener('click', () => {
        filterSelect.value = '';
        myThreadsCheckbox.checked = false;
        fetchFilteredThreads();
    });

    paginationContainer.addEventListener('click', (event) => {
        const link = event.target.closest('a');

        if (!link) {
            return;
        }

        event.preventDefault();
        fetchFilteredThreads(link.href);
    });
})();
</script>
@endsection
