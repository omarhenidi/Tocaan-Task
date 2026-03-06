<?php

namespace App\Http\Controllers\Api\V1\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Contracts\Admin\User\UserManagementInterface;
use App\Support\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    use ResponseTrait;

    public function __construct(protected UserManagementInterface $userService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $users = $this->userService->listUsers($perPage);

        return $this->successResponse([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 'Users retrieved successfully');
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->userService->create($data, $data['role'] ?? null);

        return $this->successResponse(new UserResource($user), 'User created successfully.', 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $this->userService->update($data, $user);

        return $this->successResponse(new UserResource($user->load('roles')), 'User updated successfully.');
    }

    public function show(User $user): JsonResponse
    {
        return $this->successResponse(new UserResource($user->load('roles')), 'User retrieved successfully.');
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return $this->successResponse([], 'User deleted successfully.');
    }
}
