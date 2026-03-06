<?php

namespace App\Services\Contracts\Admin\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserManagementInterface
{
    public function listUsers(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data, ?string $role = null): User;

    public function update(array $data, User $user): void;
}
