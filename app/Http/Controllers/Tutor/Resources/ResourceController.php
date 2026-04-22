<?php

namespace App\Http\Controllers\Tutor\Resources;

use App\Http\Controllers\Controller;
use App\Models\ResourceType;
use App\Models\Topic;
use App\Services\Tutor\Resources\ResourceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function __construct(
        private ResourceService $resourceService
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
        // Flashcards tienen su propio flujo, no se crean desde este formulario
        if ((int) $request->type_id === 3) {
            return back()
                ->withInput()
                ->withErrors([
                    'type_id' => 'Los recursos tipo "Flashcards" se crean desde su propia sección. Esta funcionalidad aún no está disponible.',
                ]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type_id' => 'required|exists:resource_types,id',
            'subtopic_id' => 'required|exists:subtopics,id',
            'file' => [
                'required',
                'file',
                'max:102400', // 100 MB en kilobytes
                'mimes:mp4,webm,mov,pdf,html,htm',
            ],
        ], [
            'title.required' => 'El título es obligatorio.',
            'type_id.required' => 'Debes seleccionar el tipo de recurso.',
            'type_id.exists' => 'El tipo de recurso seleccionado no es válido.',
            'subtopic_id.required' => 'Debes seleccionar un subtema.',
            'subtopic_id.exists' => 'El subtema seleccionado no es válido.',
            'file.required' => 'Debes subir un archivo.',
            'file.file' => 'El archivo no es válido.',
            'file.max' => 'El archivo no puede superar los 100 MB.',
            'file.mimes' => 'Formato no permitido. Usa mp4, webm, mov, pdf, html o htm.',
        ]);

        $this->resourceService->createResource($request);

        return redirect()->route('tutor.resources.create')
            ->with('success', 'Recurso enviado a revisión correctamente.');
    }
}
