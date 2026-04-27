<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Services\Tutor\TutorDashboardService;
use Illuminate\View\View;

class TutorDashboardController extends Controller
{
    public function __construct(private readonly TutorDashboardService $dashboardService) {
    }

    public function index(): View
    {
        $data = $this->dashboardService->getDashboardData(auth()->id());

        return view('tutor.index', $data);
    }
}
