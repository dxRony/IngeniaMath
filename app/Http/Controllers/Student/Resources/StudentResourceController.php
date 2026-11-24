<?php

namespace App\Http\Controllers\Student\Resources;

use App\Http\Controllers\Controller;
use App\Services\Student\Resources\StudentResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentResourceController extends Controller
{
    public function __construct(
        private StudentResourceService $resourceService
    ) {}

    /**
     * Devuelve el listado de recursos aprobados (JSON).
     * Usado por el modal del navbar.
     */
    public function index(Request $request): JsonResponse
    {
        $topicId = $request->input('topic') ? (int) $request->input('topic') : null;
        $page    = (int) $request->input('page', 1);

        $resources = $this->resourceService->getApprovedResources($topicId, 12);
        $topics    = $this->resourceService->getTopicsWithCounts();

        return response()->json([
            'resources' => $resources->getCollection()->map(fn($r) => [
                'id'        => $r->id,
                'title'     => $r->title,
                'type_id'   => $r->type_id,
                'type_name' => $r->type?->name,
                'icon'      => $this->getIcon($r->type_id),
            ]),
            'pagination' => [
                'current_page' => $resources->currentPage(),
                'last_page'    => $resources->lastPage(),
                'total'        => $resources->total(),
                'has_pages'    => $resources->hasPages(),
            ],
            'topics' => $topics->map(fn($t) => [
                'id'    => $t->id,
                'name'  => $t->name,
                'count' => $t->count,
            ]),
            'selected_topic' => $topicId,
        ]);
    }

    /**
     * Devuelve los datos de un recurso específico (JSON).
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

    private function getIcon(int $typeId): string
    {
        return match($typeId) {
            1 => '🎬',
            2 => '📄',
            3 => '🃏',
            4 => '📐',
            default => '📎',
        };
    }
}
