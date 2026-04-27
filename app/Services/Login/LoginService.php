<?php

namespace App\Services\Login;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginService
{
    public function authenticate(Request $request): array
    {
        //guardando credenciales
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        //intentando login
        if (!Auth::attempt($credentials)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials.',
            ];
        }
        //guardando usuario en sesion
        $request->session()->regenerate();
        $user = Auth::user();
        //validando si usuario esta activo
        if (!$user->is_active) {
            return ['success' => true, 'route' => 'account.disabled',];
        }
        //redirigiendo por rol
        $redirectRoute = $this->getRedirectRouteByRole($user->role_id);
        //si no pudo ser redirigido se cierra la sesion
        if (!$redirectRoute) {
            Auth::logout();
            return ['success' => false, 'message' => 'Invalid role.',];
        }
        return ['success' => true, 'route' => $redirectRoute,];
    }

    public function logout(Request $request): void
    {
        //removiendo usuario de la sesion
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function getRedirectRouteByRole(int $roleId): ?string
    {
        //evaluando id del rol
        return match ($roleId) {
            1 => 'admin.dashboard',
            2 => 'moderator.dashboard',
            3 => 'tutor.dashboard',
            4 => 'student.dashboard',
            default => null,
        };
    }
}
