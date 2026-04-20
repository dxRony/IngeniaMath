<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TutorDashboardController extends Controller
{
    public function index(): View
    {
        return view('tutor.index');
    }
}
