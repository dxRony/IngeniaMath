<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login.index');
        }

        if (!Auth::user()->is_active && !$request->routeIs('account.disabled')) {
            return redirect()->route('account.disabled');
        }

        return $next($request);
    }
}
