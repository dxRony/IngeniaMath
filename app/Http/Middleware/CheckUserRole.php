<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    public function handle(Request $request, Closure $next, int $roleId): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login.index');
        }

        $user = Auth::user();

        if ((int) $user->role_id !== (int) $roleId) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }
}
