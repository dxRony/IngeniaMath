<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('shared.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function showPublic(User $user): View
    {
        $user->loadMissing('role');

        return view('shared.profile-show', [
            'user' => $user,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->has('remove_profile_photo') && $request->input('remove_profile_photo') == '1') {
            $oldPhotoPath = $user->profile_photo_url;

            if ($oldPhotoPath) {
                if (str_starts_with($oldPhotoPath, 'profile-photos/')) {
                    Storage::disk('public')->delete($oldPhotoPath);
                }

                if (str_starts_with($oldPhotoPath, 'img/profile-photos/')) {
                    $absoluteOldPath = public_path($oldPhotoPath);
                    if (file_exists($absoluteOldPath)) {
                        unlink($absoluteOldPath);
                    }
                }
            }

            $data['profile_photo_url'] = null;
        } elseif ($request->hasFile('profile_photo')) {
            $oldPhotoPath = $user->profile_photo_url;

            $data['profile_photo_url'] = $request->file('profile_photo')->store('profile-photos', 'public');

            if ($oldPhotoPath) {
                if (str_starts_with($oldPhotoPath, 'profile-photos/')) {
                    Storage::disk('public')->delete($oldPhotoPath);
                }

                if (str_starts_with($oldPhotoPath, 'img/profile-photos/')) {
                    $absoluteOldPath = public_path($oldPhotoPath);
                    if (file_exists($absoluteOldPath)) {
                        unlink($absoluteOldPath);
                    }
                }
            }
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['profile_photo'], $data['remove_profile_photo'], $data['current_password'], $data['password_confirmation']);

        $user->update($data);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Perfil actualizado exitosamente.');
    }
}
