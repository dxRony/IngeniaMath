<?php

namespace App\Services\Tutor\Resources;

use App\Models\EducationalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimulatorService
{
    // Estado inicial: "Pendiente de revisión" (id = 1)
    private const STATUS_PENDING_REVIEW = 1;

    // Tipo Simulador Visual (id = 4)
    private const TYPE_SIMULATOR = 4;

    public function createSimulator(Request $request): EducationalResource
    {
        return DB::transaction(function () use ($request) {
            $resource = EducationalResource::create([
                'title'           => trim($request->title),
                'description'    => $request->description ? trim($request->description) : null,
                'url'             => null, // los simuladores no tienen archivo ni URL externa
                'simulator_state' => $request->simulator_state,
                'type_id'         => self::TYPE_SIMULATOR,
                'status_id'       => self::STATUS_PENDING_REVIEW,
                'created_by'      => Auth::id(),
            ]);

            // Asociar el subtema
            $resource->subtopics()->attach($request->subtopic_id);

            return $resource;
        });
    }
}
