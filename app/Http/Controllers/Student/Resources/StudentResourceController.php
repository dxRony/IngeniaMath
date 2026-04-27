<?php

namespace App\Http\Controllers\Student\Resources;

use App\Http\Controllers\Controller;
use App\Services\Student\Resources\StudentResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentResourceController extends Controller
{
    public function __construct(
        private StudentResourceService $resourceService
    ) {}

    /**
     * Listado de recursos aprobados con filtro por módulo.
     */
    public function index(Request $request): View
    {
        $topicId = $request->input('topic') ? (int) $request->input('topic') : null;

        $resources = $this->resourceService->getApprovedResources($topicId);
        $topics    = $this->resourceService->getTopicsWithCounts();

        return view('student.resources.index', [
            'resources'      => $resources,
            'topics'         => $topics,
            'selectedTopic'  => $topicId,
        ]);
    }

    /**
     * Devuelve los datos de un recurso para mostrarlo en el modal (JSON).
     */
    public function show(int $id): JsonResponse
    {
        $resource = $this->resourceService->findApprovedResource($id);

        return response()->json([
            'id'           => $resource->id,
            'title'        => $resource->title,
            'description'  => $resource->description,
            'type_id'      => $resource->type_id,
            'type_name'    => $resource->type?->name,
            'url'          => $resource->url ? asset('storage/' . $resource->url) : null,
            'simulator_state' => $resource->simulator_state,
            'subtopics'    => $resource->subtopics->map(fn($s) => [
                'topic' => $s->topic?->name,
                'name'  => $s->name,
            ]),
            'creator'      => trim(($resource->creator?->first_name ?? '') . ' ' . ($resource->creator?->last_name ?? '')),
            'flashcards'   => $resource->flashcards->sortBy('order')->values()->map(fn($f) => [
                'order'      => $f->order,
                'front_text' => $f->front_text,
                'back_text'  => $f->back_text,
            ]),
        ]);
    }
}
