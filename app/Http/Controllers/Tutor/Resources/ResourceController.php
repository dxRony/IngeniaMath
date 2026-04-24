<?php

namespace App\Http\Controllers\Tutor\Resources;

use App\Http\Controllers\Controller;
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

    public function __construct(
        private ResourceService $resourceService,
        private FlashcardDeckService $flashcardDeckService,
        private SimulatorService $simulatorService
    ) {}

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
            default                => $this->storeFileResource($request),
        };
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

        return redirect()->route('tutor.resources.create')
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

        return redirect()->route('tutor.resources.create')
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

        return redirect()->route('tutor.resources.create')
            ->with('success', 'Simulador enviado a revisión correctamente.');
    }

}
