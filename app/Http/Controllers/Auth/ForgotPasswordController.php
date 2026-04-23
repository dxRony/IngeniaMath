<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService
    ) {
    }

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $this->passwordResetService->sendResetLink($request->string('email')->toString());

        // Keep response neutral for security.
        return back()->with('status', 'Si el correo existe en el sistema, enviaremos un enlace para restablecer tu contraseña.');
    }
}
