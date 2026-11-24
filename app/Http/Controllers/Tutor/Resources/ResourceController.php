<?php

namespace App\Http\Controllers\Tutor\Resources;

use App\Http\Controllers\Controller;
use App\Models\EducationalResource;
use App\Models\ResourceType;
use App\Models\Topic;
use App\Services\Tutor\Resources\FlashcardDeckService;
use App\Services\Tutor\Resources\ResourceService;
use App\Services\Tutor\Resources\SimulatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceController extends Controller
{
    // IDs de tipos en la tabla resource_types
    private const TYPE_VIDEO = 1;
    private const TYPE_PDF = 2;
    private const TYPE_FLASHCARDS = 3;
    private const TYPE_SIMULATOR = 4;

    // IDs de estados en la tabla resource_statuses
    private const STATUS_PENDING = 1;
    private const STATUS_APPROVED = 2;
    private const STATUS_REJECTED = 3;
    private const STATUS_DELETED = 4;

    public function __construct(
        private ResourceService $resourceService,
        private FlashcardDeckService $flashcardDeckService,
        private SimulatorService $simulatorService
    ) {}

    /**
     * Listado de recursos del tutor autenticado, con filtros por estado.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status'); // pending|approved|rejected|deleted|null

        $query = EducationalResource::with(['type', 'status', 'subtopics.topic'])
            ->where('created_by', auth()->id());

        // Filtro por estado
        match ($statusFilter) {
            'pending'  => $query->where('status_id', self::STATUS_PENDING),
            'approved' => $query->where('status_id', self::STATUS_APPROVED),
            'rejected' => $query->where('status_id', self::STATUS_REJECTED),
            'deleted'  => $query->where('status_id', self::STATUS_DELETED),
            default    => $query->whereIn('status_id', [
                self::STATUS_PENDING,
                self::STATUS_APPROVED,
                self::STATUS_REJECTED,
            ]),
        };

        $resources = $query->latest()->paginate(10);

        // Conteos para los tabs de filtros
        $baseQuery = EducationalResource::where('created_by', auth()->id());

        $counts = [
            'all'      => (clone $baseQuery)->whereIn('status_id', [
                self::STATUS_PENDING,
                self::STATUS_APPROVED,
                self::STATUS_REJECTED,
            ])->count(),
            'pending'  => (clone $baseQuery)->where('status_id', self::STATUS_PENDING)->count(),
            'approved' => (clone $baseQuery)->where('status_id', self::STATUS_APPROVED)->count(),
            'rejected' => (clone $baseQuery)->where('status_id', self::STATUS_REJECTED)->count(),
            'deleted'  => (clone $baseQuery)->where('status_id', self::STATUS_DELETED)->count(),
        ];

        return view('tutor.resources.index', compact('resources', 'statusFilter', 'counts'));
    }

    public function create(): View
    {
        $types = ResourceType::orderBy('name')->get();
        $topics = Topic::with(['subtopics' => function ($query) {
            $query->orderBy('order');
        }])->orderBy('order')->get();

        return view('tutor.resources.create', compact('types', 'topics'));
    }

    public function store(Request $request): RedirectResponse
    {
        $typeId = (int) $request->type_id;

        return match ($typeId) {
            self::TYPE_FLASHCARDS => $this->storeFlashcardDeck($request),
            self::TYPE_SIMULATOR  => $this->storeSimulator($request),
            default               => $this->storeFileResource($request),
        };
    }

    /**
     * DEBUG: lista todos los recursos para verificación visual.
     * Eliminar antes del merge a develop.
     */
    public function debug(): View
    {
        $resources = EducationalResource::with([
            'type',
            'status',
            'subtopics.topic',
            'flashcards',
            'creator',
        ])
            ->latest()
            ->get();

        return view('tutor.resources.debug', compact('resources'));
    }

    private function storeFileResource(Request $request): RedirectResponse
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string|max:2000',
            'type_id'      => 'required|exists:resource_types,id',
            'subtopic_id'  => 'required|exists:subtopics,id',
            'file' => [
                'required',
                'file',
                'max:102400',
                'mimes:mp4,webm,mov,pdf',
            ],
        ], [
            'title.required'       => 'El título es obligatorio.',
            'type_id.required'     => 'Debes seleccionar el tipo de recurso.',
            'type_id.exists'       => 'El tipo de recurso seleccionado no es válido.',
            'subtopic_id.required' => 'Debes seleccionar un subtema.',
            'subtopic_id.exists'   => 'El subtema seleccionado no es válido.',
            'file.required'        => 'Debes subir un archivo.',
            'file.file'            => 'El archivo no es válido.',
            'file.max'             => 'El archivo no puede superar los 100 MB.',
            'file.mimes'           => 'Formato no permitido. Usa mp4, webm, mov o pdf.',
        ]);

        $this->resourceService->createResource($request);

        return redirect()->route('tutor.resources.index')
            ->with('success', 'Recurso enviado a revisión correctamente.');
    }

    private function storeFlashcardDeck(Request $request): RedirectResponse
    {
        $request->validate([
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string|max:2000',
            'subtopic_id'          => 'required|exists:subtopics,id',
            'cards'                => 'required|array|min:1',
            'cards.*.front_text'   => 'required|string|max:1000',
            'cards.*.back_text'    => 'required|string|max:1000',
        ], [
            'title.required'              => 'El título del mazo es obligatorio.',
            'subtopic_id.required'        => 'Debes seleccionar un subtema.',
            'subtopic_id.exists'          => 'El subtema seleccionado no es válido.',
            'cards.required'              => 'Debes agregar al menos una tarjeta.',
            'cards.min'                   => 'Debes agregar al menos una tarjeta.',
            'cards.*.front_text.required' => 'Cada tarjeta debe tener texto en el frente.',
            'cards.*.front_text.max'      => 'El frente de cada tarjeta no puede superar los 1000 caracteres.',
            'cards.*.back_text.required'  => 'Cada tarjeta debe tener texto en el reverso.',
            'cards.*.back_text.max'       => 'El reverso de cada tarjeta no puede superar los 1000 caracteres.',
        ]);

        $this->flashcardDeckService->createDeck($request);

        return redirect()->route('tutor.resources.index')
            ->with('success', 'Mazo de flashcards enviado a revisión correctamente.');
    }

    private function storeSimulator(Request $request): RedirectResponse
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:2000',
            'subtopic_id'     => 'required|exists:subtopics,id',
            'simulator_state' => 'required|string|min:10',
        ], [
            'title.required'           => 'El título es obligatorio.',
            'subtopic_id.required'     => 'Debes seleccionar un subtema.',
            'subtopic_id.exists'       => 'El subtema seleccionado no es válido.',
            'simulator_state.required' => 'Debes crear la construcción en el editor antes de enviar.',
            'simulator_state.min'      => 'La construcción del simulador está vacía o es inválida.',
        ]);

        $this->simulatorService->createSimulator($request);

        return redirect()->route('tutor.resources.index')
            ->with('success', 'Simulador enviado a revisión correctamente.');
    }
    /**
     * Muestra el formulario de edición de un recurso.
     */
    public function edit(int $id): View
    {
        $resource = EducationalResource::with([
            'subtopics',
            'flashcards' => function ($q) {
                $q->orderBy('order');
            },
            'latestReview.reviewer',
        ])->findOrFail($id);

        // Solo el dueño puede editar
        if ($resource->created_by !== auth()->id()) {
            abort(403, 'No tienes permiso para editar este recurso.');
        }

        // No se puede editar un recurso eliminado
        if ($resource->status_id === self::STATUS_DELETED) {
            return redirect()->route('tutor.resources.index')
                ->withErrors(['status' => 'No puedes editar un recurso eliminado.']);
        }

        $types = ResourceType::orderBy('name')->get();
        $topics = Topic::with(['subtopics' => function ($query) {
            $query->orderBy('order');
        }])->orderBy('order')->get();

        return view('tutor.resources.edit', compact('resource', 'types', 'topics'));
    }

    /**
     * Procesa la actualización de un recurso.
     * Delega al service correcto según el tipo.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $resource = EducationalResource::findOrFail($id);

        // Solo el dueño puede editar
        if ($resource->created_by !== auth()->id()) {
            abort(403);
        }

        if ($resource->status_id === self::STATUS_DELETED) {
            return redirect()->route('tutor.resources.index')
                ->withErrors(['status' => 'No puedes editar un recurso eliminado.']);
        }

        return match ((int) $resource->type_id) {
            self::TYPE_FLASHCARDS => $this->updateFlashcardDeck($request, $resource),
            self::TYPE_SIMULATOR  => $this->updateSimulator($request, $resource),
            default               => $this->updateFileResource($request, $resource),
        };
    }

    private function updateFileResource(Request $request, EducationalResource $resource): RedirectResponse
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string|max:2000',
            'subtopic_id'  => 'required|exists:subtopics,id',
            'file' => [
                'nullable', // en edición el archivo es opcional (si no se sube, se mantiene el existente)
                'file',
                'max:102400',
                'mimes:mp4,webm,mov,pdf',
            ],
        ], [
            'title.required'       => 'El título es obligatorio.',
            'subtopic_id.required' => 'Debes seleccionar un subtema.',
            'subtopic_id.exists'   => 'El subtema seleccionado no es válido.',
            'file.max'             => 'El archivo no puede superar los 100 MB.',
            'file.mimes'           => 'Formato no permitido. Usa mp4, webm, mov o pdf.',
        ]);

        $this->resourceService->updateResource($request, $resource);

        return redirect()->route('tutor.resources.index')
            ->with('success', 'Recurso actualizado y enviado a revisión.');
    }

    private function updateFlashcardDeck(Request $request, EducationalResource $resource): RedirectResponse
    {
        $request->validate([
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string|max:2000',
            'subtopic_id'          => 'required|exists:subtopics,id',
            'cards'                => 'required|array|min:1',
            'cards.*.front_text'   => 'required|string|max:1000',
            'cards.*.back_text'    => 'required|string|max:1000',
        ], [
            'title.required'              => 'El título del mazo es obligatorio.',
            'subtopic_id.required'        => 'Debes seleccionar un subtema.',
            'cards.required'              => 'Debes tener al menos una tarjeta.',
            'cards.min'                   => 'Debes tener al menos una tarjeta.',
            'cards.*.front_text.required' => 'Cada tarjeta debe tener texto en el frente.',
            'cards.*.back_text.required'  => 'Cada tarjeta debe tener texto en el reverso.',
        ]);

        $this->flashcardDeckService->updateDeck($request, $resource);

        return redirect()->route('tutor.resources.index')
            ->with('success', 'Mazo actualizado y enviado a revisión.');
    }

    private function updateSimulator(Request $request, EducationalResource $resource): RedirectResponse
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:2000',
            'subtopic_id'     => 'required|exists:subtopics,id',
            'simulator_state' => 'nullable|string|min:10',
        ], [
            'title.required'       => 'El título es obligatorio.',
            'subtopic_id.required' => 'Debes seleccionar un subtema.',
            'simulator_state.min'  => 'La construcción del simulador está vacía o es inválida.',
        ]);

        $this->simulatorService->updateSimulator($request, $resource);

        return redirect()->route('tutor.resources.index')
            ->with('success', 'Simulador actualizado y enviado a revisión.');
    }

    /**
     * "Elimina" un recurso cambiando su status_id a 4 (Eliminado).
     * No borra físicamente, preserva el histórico.
     */
    public function destroy(int $id): RedirectResponse
    {
        $resource = EducationalResource::findOrFail($id);

        // Solo el dueño puede eliminar
        if ($resource->created_by !== auth()->id()) {
            abort(403);
        }

        // Evitar "eliminar dos veces"
        if ($resource->status_id === self::STATUS_DELETED) {
            return redirect()->route('tutor.resources.index')
                ->withErrors(['status' => 'Este recurso ya estaba eliminado.']);
        }

        $this->resourceService->softDelete($resource);

        return redirect()->route('tutor.resources.index')
            ->with('success', 'Recurso eliminado correctamente.');
    }

}
