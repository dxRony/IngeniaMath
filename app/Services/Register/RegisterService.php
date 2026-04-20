<?php

namespace App\Services\Register;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function registerStudent(Request $request): void
    {
        User::create([
            'role_id' => 4, // es student
            'name' => $request->name,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => true,
        ]);
    }
}
