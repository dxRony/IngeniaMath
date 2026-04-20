<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ModeratorDashboardController extends Controller
{
    public function index(): View
    {
        return view('moderator.index');
    }
}
