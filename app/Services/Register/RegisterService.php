<?php

namespace App\Services\Register;

use App\Models\InitialAssessment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function registerStudent(Request $request): User
    {
        $firstName = trim($request->first_name);
        $lastName = trim($request->last_name);

        $user = User::create([
            'role_id' => 4, // es student
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        InitialAssessment::create([
            'student_id' => $user->id,
            'is_completed' => false,
        ]);

        return $user;
    }
}
