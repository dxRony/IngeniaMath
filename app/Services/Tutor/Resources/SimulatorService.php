<?php

namespace App\Services\Tutor\Resources;

use App\Models\EducationalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimulatorService
{
    private const STATUS_PENDING_REVIEW = 1;
    private const TYPE_SIMULATOR = 4;

    public function createSimulator(Request $request): EducationalResource
    {
        return DB::transaction(function () use ($request) {
            $resource = EducationalResource::create([
                'title'           => trim($request->title),
                'description'     => $request->description ? trim($request->description) : null,
                'url'             => null,
                'simulator_state' => $request->simulator_state,
                'type_id'         => self::TYPE_SIMULATOR,
                'status_id'       => self::STATUS_PENDING_REVIEW,
                'created_by'      => Auth::id(),
            ]);

            $resource->subtopics()->attach($request->subtopic_id);

            return $resource;
        });
    }

    /**
     * Actualiza un simulador.
     * Si se envía un nuevo estado, se actualiza; si no, se mantiene el actual.
     * El recurso vuelve a estado "Pendiente de revisión".
     */
    public function updateSimulator(Request $request, EducationalResource $resource): EducationalResource
    {
        return DB::transaction(function () use ($request, $resource) {
            $data = [
                'title'       => trim($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'status_id'   => self::STATUS_PENDING_REVIEW,
            ];

            // Solo actualizamos el estado del simulador si se envió uno nuevo
            if ($request->filled('simulator_state')) {
                $data['simulator_state'] = $request->simulator_state;
            }

            $resource->update($data);
            $resource->subtopics()->sync([$request->subtopic_id]);

            return $resource->fresh();
        });
    }
}
