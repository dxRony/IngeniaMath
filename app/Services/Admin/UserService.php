<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
	public function create(array $data): User
	{
		return User::create([
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'email' => $data['email'],
			'password' => Hash::make($data['password']),
			'role_id' => $data['role_id'],
		]);
	}

	public function update(User $user, array $data): User
	{
		$payload = [
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'email' => $data['email'],
		];

		if (!empty($data['password'])) {
			$payload['password'] = Hash::make($data['password']);
		}

		$user->update($payload);

		return $user->refresh();
	}

	public function delete(User $user): void
	{
		$user->delete();
	}

	public function toggleState(User $user): User
	{
		$user->update([
			'is_active' => ! $user->is_active,
		]);

		return $user->refresh();
	}
}
