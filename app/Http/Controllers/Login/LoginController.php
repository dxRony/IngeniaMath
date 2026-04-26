<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Services\Login\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private LoginService $loginService
    )
    {
    }

    public function index(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $result = $this->loginService->authenticate($request);
        if (!$result['success']) {
            return back()->withInput($request->only('email'))
                ->withErrors(['login' => $result['message'],]);
        }

        $pendingInitialAssessment = Auth::user()->initialAssessments->where('is_completed', false)->first();
        if ($pendingInitialAssessment) {
            return redirect()->route('initial-assessments.index');
        }

        return redirect()->route($result['route']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->loginService->logout($request);
        return redirect()->route('login.index');
    }
}
