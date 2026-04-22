<?php

namespace App\Services\Tutor\Resources;

use App\Models\EducationalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResourceService
{
    // Estado inicial: "Pendiente de revisión" (id = 1)
    private const STATUS_PENDING_REVIEW = 1;

    // Carpeta base dentro de storage/app/public/
    private const STORAGE_FOLDER = 'resources';

    public function createResource(Request $request): EducationalResource
    {
        // 1. Subimos primero el archivo. Si falla, ni tocamos la BD.
        $file = $request->file('file');
        $typeFolder = $this->resolveTypeFolder((int) $request->type_id);

        // Laravel genera un nombre único automáticamente
        $storedPath = $file->store(
            self::STORAGE_FOLDER . '/' . $typeFolder,
            'public'
        );

        // 2. Insertamos en BD dentro de una transacción
        try {
            return DB::transaction(function () use ($request, $storedPath) {
                $resource = EducationalResource::create([
                    'title'       => trim($request->title),
                    'description' => $request->description ? trim($request->description) : null,
                    'url'         => $storedPath, // guardamos la ruta local aquí
                    'type_id'     => $request->type_id,
                    'status_id'   => self::STATUS_PENDING_REVIEW,
                    'created_by'  => Auth::id(),
                ]);

                // Un solo subtema aunque la tabla sea pivote (enfoque rápido)
                $resource->subtopics()->attach($request->subtopic_id);

                return $resource;
            });
        } catch (\Throwable $e) {
            // Si la BD falla, borramos el archivo huérfano
            Storage::disk('public')->delete($storedPath);
            throw $e;
        }
    }

    /**
     * Devuelve la subcarpeta según el tipo de recurso.
     * 1 = Videos, 2 = PDFs, 3 = Flashcards, 4 = Simuladores
     */
    private function resolveTypeFolder(int $typeId): string
    {
        return match ($typeId) {
            1 => 'videos',
            2 => 'pdfs',
            3 => 'flashcards',
            4 => 'simulators',
            default => 'others',
        };
    }
}
