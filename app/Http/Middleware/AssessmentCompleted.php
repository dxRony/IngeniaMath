<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\InitialAssessment;

class AssessmentCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->role_id === 4) { // rol estudiante
            $pendingAssessment = InitialAssessment::where('student_id', $user->id)
                ->where('is_completed', false)
                ->first();

            if ($pendingAssessment) {
                return redirect()->route('initial-assessments.index');
            }
        }

        return $next($request);
    }
}
