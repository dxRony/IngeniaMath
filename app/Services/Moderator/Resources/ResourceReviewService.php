<?php

namespace App\Services\Moderator\Resources;

use App\Models\EducationalResource;
use App\Models\ResourceReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResourceReviewService
{
    // IDs de estados en la tabla resource_statuses
    private const STATUS_APPROVED = 2;
    private const STATUS_REJECTED = 3;

    /**
     * Aprueba un recurso: cambia su status_id a Aprobado
     * y registra la revisión en resource_reviews.
     */
    public function approve(EducationalResource $resource, ?string $feedback = null): void
    {
        DB::transaction(function () use ($resource, $feedback) {
            $resource->update(['status_id' => self::STATUS_APPROVED]);

            ResourceReview::create([
                'resource_id' => $resource->id,
                'reviewed_by' => Auth::id(),
                'is_approved' => true,
                'feedback'    => $feedback ? trim($feedback) : null,
                'reviewed_at' => now(),
            ]);
        });
    }

    /**
     * Rechaza un recurso: cambia su status_id a Rechazado
     * y registra la revisión con el feedback (opcional).
     */
    public function reject(EducationalResource $resource, ?string $feedback = null): void
    {
        DB::transaction(function () use ($resource, $feedback) {
            $resource->update(['status_id' => self::STATUS_REJECTED]);

            ResourceReview::create([
                'resource_id' => $resource->id,
                'reviewed_by' => Auth::id(),
                'is_approved' => false,
                'feedback'    => $feedback ? trim($feedback) : null,
                'reviewed_at' => now(),
            ]);
        });
    }
}
