<?php

namespace App\Http\Controllers\Api\V1\Admin\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use ResponseTrait;

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), (string) $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        if (! $user->hasRole(UserRole::Admin)) {
            return $this->errorResponse('Forbidden', 403);
        }

        if (property_exists($user, 'is_active') && $user->is_active === false) {
            return $this->errorResponse('Account is inactive', 403);
        }

        $token = auth('api')->login($user);

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl', 60) * 60,
            'user' => new UserResource($user),
        ], 'Logged in successfully');
    }
}


