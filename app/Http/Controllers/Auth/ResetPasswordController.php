<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService
    ) {
    }

    public function create(string $token, Request $request): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = $this->passwordResetService->reset(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->string('password_confirmation')->toString(),
            $request->string('token')->toString(),
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login.index')
                ->with('success', 'Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
