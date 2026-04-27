<?php

namespace App\Services\Student\Resources;

use App\Models\EducationalResource;
use App\Models\Topic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StudentResourceService
{
    private const STATUS_APPROVED = 2;

    /**
     * Lista de recursos aprobados, opcionalmente filtrados por topic.
     */
    public function getApprovedResources(?int $topicId = null, int $perPage = 12): LengthAwarePaginator
    {
        $query = EducationalResource::with([
            'type',
            'subtopics.topic',
            'creator',
            'flashcards',
        ])
            ->where('status_id', self::STATUS_APPROVED);

        if ($topicId) {
            $query->whereHas('subtopics', function ($q) use ($topicId) {
                $q->where('topic_id', $topicId);
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Devuelve todos los topics con conteo de recursos aprobados (para el filtro).
     */
    public function getTopicsWithCounts(): Collection
    {
        return Topic::orderBy('order')
            ->get()
            ->map(function ($topic) {
                $count = EducationalResource::where('status_id', self::STATUS_APPROVED)
                    ->whereHas('subtopics', fn($q) => $q->where('topic_id', $topic->id))
                    ->count();

                return (object) [
                    'id'    => $topic->id,
                    'name'  => $topic->name,
                    'count' => $count,
                ];
            });
    }

    /**
     * Carga un recurso aprobado específico con todas sus relaciones (para el modal).
     */
    public function findApprovedResource(int $id): EducationalResource
    {
        return EducationalResource::with([
            'type',
            'subtopics.topic',
            'creator',
            'flashcards',
        ])
            ->where('status_id', self::STATUS_APPROVED)
            ->findOrFail($id);
    }
}
