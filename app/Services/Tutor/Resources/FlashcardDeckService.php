<?php

namespace App\Services\Tutor\Resources;

use App\Models\EducationalResource;
use App\Models\Flashcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FlashcardDeckService
{
    private const STATUS_PENDING_REVIEW = 1;
    private const TYPE_FLASHCARDS = 3;

    public function createDeck(Request $request): EducationalResource
    {
        return DB::transaction(function () use ($request) {
            $resource = EducationalResource::create([
                'title'       => trim($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'url'         => null,
                'type_id'     => self::TYPE_FLASHCARDS,
                'status_id'   => self::STATUS_PENDING_REVIEW,
                'created_by'  => Auth::id(),
            ]);

            $resource->subtopics()->attach($request->subtopic_id);

            $cards = $request->input('cards', []);

            foreach ($cards as $index => $card) {
                Flashcard::create([
                    'resource_id' => $resource->id,
                    'front_text'  => trim($card['front_text']),
                    'back_text'   => trim($card['back_text']),
                    'order'       => $index + 1,
                ]);
            }

            return $resource;
        });
    }

    /**
     * Actualiza un mazo de flashcards.
     * Estrategia: borra todas las tarjetas existentes y las recrea
     * con los datos nuevos. Es simple y garantiza consistencia.
     * El recurso vuelve a estado "Pendiente de revisión".
     */
    public function updateDeck(Request $request, EducationalResource $resource): EducationalResource
    {
        return DB::transaction(function () use ($request, $resource) {
            $resource->update([
                'title'       => trim($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'status_id'   => self::STATUS_PENDING_REVIEW,
            ]);

            $resource->subtopics()->sync([$request->subtopic_id]);

            // Borra todas las tarjetas actuales y las recrea
            $resource->flashcards()->delete();

            $cards = $request->input('cards', []);
            foreach ($cards as $index => $card) {
                Flashcard::create([
                    'resource_id' => $resource->id,
                    'front_text'  => trim($card['front_text']),
                    'back_text'   => trim($card['back_text']),
                    'order'       => $index + 1,
                ]);
            }

            return $resource->fresh();
        });
    }
}
