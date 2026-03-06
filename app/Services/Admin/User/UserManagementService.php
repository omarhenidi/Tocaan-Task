<?php

namespace App\Services\Admin\User;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Contracts\Admin\User\UserManagementInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class UserManagementService implements UserManagementInterface
{
    public function listUsers(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()->with('roles')->latest('id')->paginate($perPage);
    }

    public function create(array $data, ?string $role = null): User
    {
        $role = $role ?? UserRole::Client;
        if (! in_array($role, UserRole::getValues())) {
            throw new InvalidArgumentException('Invalid role.');
        }

        $user = User::create([
            'name' => $data['name'] ?? '',
            'email' => $data['email'],
            'password' => isset($data['password']) ? Hash::make($data['password']) : Hash::make(str()->random(16)),
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole($role);
        }

        return $user;
    }

    public function update(array $data, User $user): void
    {
        $fillable = array_intersect_key($data, array_flip((new User)->getFillable()));
        if (isset($data['password']) && $data['password']) {
            $fillable['password'] = Hash::make($data['password']);
        }

        $user->update($fillable);

        if (isset($data['role']) && method_exists($user, 'assignRole')) {
            $user->syncRoles([$data['role']]);
        }
    }
}
