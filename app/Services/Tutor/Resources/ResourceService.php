<?php

namespace App\Services\Tutor\Resources;

use App\Models\EducationalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResourceService
{
    // Estado "Pendiente de revisión"
    private const STATUS_PENDING_REVIEW = 1;

    // Carpeta base dentro de storage/app/public/
    private const STORAGE_FOLDER = 'resources';

    public function createResource(Request $request): EducationalResource
    {
        $file = $request->file('file');
        $typeFolder = $this->resolveTypeFolder((int) $request->type_id);

        $storedPath = $file->store(
            self::STORAGE_FOLDER . '/' . $typeFolder,
            'public'
        );

        try {
            return DB::transaction(function () use ($request, $storedPath) {
                $resource = EducationalResource::create([
                    'title'       => trim($request->title),
                    'description' => $request->description ? trim($request->description) : null,
                    'url'         => $storedPath,
                    'type_id'     => $request->type_id,
                    'status_id'   => self::STATUS_PENDING_REVIEW,
                    'created_by'  => Auth::id(),
                ]);

                $resource->subtopics()->attach($request->subtopic_id);

                return $resource;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedPath);
            throw $e;
        }
    }

    /**
     * Actualiza un recurso de archivo (video/PDF).
     * Si se envía un archivo nuevo, se reemplaza el anterior.
     * El recurso vuelve a estado "Pendiente de revisión".
     */
    public function updateResource(Request $request, EducationalResource $resource): EducationalResource
    {
        $newStoredPath = null;
        $oldPath = $resource->url;

        if ($request->hasFile('file')) {
            $typeFolder = $this->resolveTypeFolder((int) $resource->type_id);
            $newStoredPath = $request->file('file')->store(
                self::STORAGE_FOLDER . '/' . $typeFolder,
                'public'
            );
        }

        try {
            $updatedResource = DB::transaction(function () use ($request, $resource, $newStoredPath) {
                $data = [
                    'title'       => trim($request->title),
                    'description' => $request->description ? trim($request->description) : null,
                    'status_id'   => self::STATUS_PENDING_REVIEW,
                ];

                if ($newStoredPath !== null) {
                    $data['url'] = $newStoredPath;
                }

                $resource->update($data);
                $resource->subtopics()->sync([$request->subtopic_id]);

                return $resource->fresh();
            });

            // Éxito: borramos el archivo viejo si fue reemplazado
            if ($newStoredPath !== null && $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            return $updatedResource;
        } catch (\Throwable $e) {
            if ($newStoredPath !== null) {
                Storage::disk('public')->delete($newStoredPath);
            }
            throw $e;
        }
    }


    /**
     * Marca un recurso como "Eliminado" (status_id = 4).
     * No borra físicamente: mantiene el histórico.
     */
    public function softDelete(EducationalResource $resource): void
    {
        $resource->update(['status_id' => 4]);
    }

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
