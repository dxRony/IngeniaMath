<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Models\PathItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LearningPathController extends Controller
{
    public function index(): View
    {
        $path = LearningPath::where('student_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if (!$path) {
            return view('student.learning-path.index', [
                'path'        => null,
                'groupedItems' => collect(),
                'total'       => 0,
                'completed'   => 0,
                'pending'     => 0,
            ]);
        }

        $items = PathItem::where('path_id', $path->id)
            ->with(['subtopic.topic', 'subtopic.complexity', 'masteryLevel'])
            ->orderBy('order')
            ->get();

        // Agrupar por módulo manteniendo el orden de aparición
        $groupedItems = $items->groupBy(fn($i) => $i->subtopic->topic->name);

        $total     = $items->count();
        $completed = $items->where('is_completed', true)->count();

        return view('student.learning-paths.index', [
            'path'         => $path,
            'groupedItems' => $groupedItems,
            'total'        => $total,
            'completed'    => $completed,
            'pending'      => $total - $completed,
        ]);
    }
}
