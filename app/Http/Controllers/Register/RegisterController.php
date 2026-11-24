<?php

namespace App\Http\Controllers\Register;

use App\Http\Controllers\Controller;
use App\Services\Register\RegisterService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private RegisterService $registerService)
    {
    }

    public function index(): View
    {
        return view('register.register');
    }

    public function store(Request $request): RedirectResponse
    {
        //validando form de registro
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = $this->registerService->registerStudent($request);

        Auth::login($user);

        return redirect()->route('initial-assessments.index');
    }
}
