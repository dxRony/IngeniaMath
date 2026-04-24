<?php

namespace App\Http\Controllers\Moderator\Resources;

use App\Http\Controllers\Controller;
use App\Models\EducationalResource;
use App\Services\Moderator\Resources\ResourceReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceReviewController extends Controller
{
    // IDs de estados
    private const STATUS_PENDING = 1;
    private const STATUS_APPROVED = 2;
    private const STATUS_REJECTED = 3;

    public function __construct(
        private ResourceReviewService $reviewService
    ) {}

    /**
     * Lista los recursos pendientes de revisión.
     * También acepta ?status= para ver aprobados y rechazados recientes.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'pending');

        $statusId = match ($statusFilter) {
            'approved' => self::STATUS_APPROVED,
            'rejected' => self::STATUS_REJECTED,
            default    => self::STATUS_PENDING,
        };

        $resources = EducationalResource::with(['type', 'status', 'subtopics.topic', 'creator'])
            ->where('status_id', $statusId)
            ->latest()
            ->paginate(10);

        // Conteos para los tabs
        $counts = [
            'pending'  => EducationalResource::where('status_id', self::STATUS_PENDING)->count(),
            'approved' => EducationalResource::where('status_id', self::STATUS_APPROVED)->count(),
            'rejected' => EducationalResource::where('status_id', self::STATUS_REJECTED)->count(),
        ];

        return view('moderator.resources.index', compact('resources', 'statusFilter', 'counts'));
    }

    /**
     * Muestra el detalle de un recurso para revisión.
     * Incluye renderizado del contenido y botones de aprobar/rechazar.
     */
    public function show(int $id): View
    {
        $resource = EducationalResource::with([
            'type',
            'status',
            'subtopics.topic',
            'creator',
            'flashcards',
            'reviews.reviewer',
        ])->findOrFail($id);

        return view('moderator.resources.show', compact('resource'));
    }

    /**
     * Aprueba un recurso.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'feedback' => 'nullable|string|max:2000',
        ]);

        $resource = EducationalResource::findOrFail($id);

        // Solo se pueden aprobar recursos pendientes
        if ($resource->status_id !== self::STATUS_PENDING) {
            return back()->withErrors([
                'status' => 'Solo se pueden aprobar recursos que estén pendientes de revisión.',
            ]);
        }

        $this->reviewService->approve($resource, $request->feedback);

        return redirect()->route('moderator.resources.index')
            ->with('success', "Recurso \"{$resource->title}\" aprobado correctamente.");
    }

    /**
     * Rechaza un recurso.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'feedback' => 'nullable|string|max:2000',
        ]);

        $resource = EducationalResource::findOrFail($id);

        if ($resource->status_id !== self::STATUS_PENDING) {
            return back()->withErrors([
                'status' => 'Solo se pueden rechazar recursos que estén pendientes de revisión.',
            ]);
        }

        $this->reviewService->reject($resource, $request->feedback);

        return redirect()->route('moderator.resources.index')
            ->with('success', "Recurso \"{$resource->title}\" rechazado.");
    }
}
