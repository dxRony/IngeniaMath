<?php

namespace App\Services\Tutor\Resources;

use App\Models\EducationalResource;
use App\Models\Flashcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FlashcardDeckService
{
    // Estado inicial: "Pendiente de revisión" (id = 1)
    private const STATUS_PENDING_REVIEW = 1;

    // Tipo Flashcards (id = 3)
    private const TYPE_FLASHCARDS = 3;

    public function createDeck(Request $request): EducationalResource
    {
        return DB::transaction(function () use ($request) {
            // 1. Crear el mazo (EducationalResource tipo Flashcards)
            $resource = EducationalResource::create([
                'title'       => trim($request->title),
                'description' => $request->description ? trim($request->description) : null,
                'url'         => null, // Flashcards no tienen archivo
                'type_id'     => self::TYPE_FLASHCARDS,
                'status_id'   => self::STATUS_PENDING_REVIEW,
                'created_by'  => Auth::id(),
            ]);

            // 2. Asociar el subtema (pivote)
            $resource->subtopics()->attach($request->subtopic_id);

            // 3. Crear cada tarjeta, respetando el orden enviado
            $cards = $request->input('cards', []);

            foreach ($cards as $index => $card) {
                Flashcard::create([
                    'resource_id' => $resource->id,
                    'front_text'  => trim($card['front_text']),
                    'back_text'   => trim($card['back_text']),
                    'order'       => $index + 1, // orden 1-based
                ]);
            }

            return $resource;
        });
    }
}
