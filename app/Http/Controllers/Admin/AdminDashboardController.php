<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly AdminDashboardService $dashboardService) {
    }

    public function index(): View
    {
        //obteniendo info del dashboard con service
        $data = $this->dashboardService->getDashboardData();
        //mandando a vista con la inf
        return view('admin.index', $data);
    }
}
