<?php

namespace App\Services\Login;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginService
{
    public function authenticate(Request $request): array
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials.',
            ];
        }
        $request->session()->regenerate();
        $user = Auth::user();

        if (!$user->active) {
            Auth::logout();

            return [
                'success' => false,
                'message' => 'Inactive user.',
            ];
        }
        $redirectRoute = $this->getRedirectRouteByRole($user->role_id);
        if (!$redirectRoute) {
            Auth::logout();

            return [
                'success' => false,
                'message' => 'Invalid role.',
            ];
        }
        return [
            'success' => true,
            'route' => $redirectRoute,
        ];
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function getRedirectRouteByRole(int $roleId): ?string
    {
        return match ($roleId) {
            1 => 'admin.dashboard',
            2 => 'moderator.dashboard',
            3 => 'tutor.dashboard',
            4 => 'student.dashboard',
            default => null,
        };
    }
}
