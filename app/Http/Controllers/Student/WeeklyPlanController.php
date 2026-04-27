<?php

namespace App\Http\Controllers\Student;

use App\Models\LearningPath;
use App\Models\WeeklyPlan;
use App\Services\Student\WeeklyPlanService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeeklyPlanController extends Controller
{
    public function __construct(
        protected WeeklyPlanService $weeklyPlanService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activePath = LearningPath::where('student_id', Auth::id())
            ->where('is_active', true)
            ->first();

        $plan = $activePath
            ? WeeklyPlan::where('path_id', $activePath->id)
            ->with(['planDays' => fn($q) => $q->with('subtopic.topic')->orderBy('weekday')->orderBy('id')])
            ->latest()
            ->first()
            : null;

        return view('student.weekly-plans.index', [
            'plan' => $plan,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'available_hours' => 'required|numeric|min:1|max:70',
        ]);

        $this->weeklyPlanService->generate(
            Auth::id(),
            (float) $request->available_hours
        );

        return redirect()->route('weekly-plans.index')
            ->with('success', 'Plan semanal generado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
