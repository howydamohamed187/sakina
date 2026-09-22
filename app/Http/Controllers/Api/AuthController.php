<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return $this->error(__('api.login_failed'), [], 401);
        }

        if ($user->status !== 'active') {
            return $this->error(__('api.account_inactive'), [], 403);
        }

        if (! $user->canAccessAdmin()) {
            return $this->error(__('api.login_no_role'), [], 403);
        }

        $token = $user->createToken('api')->plainTextToken;

        if ($request->filled('locale')) {
            $user->forceFill(['locale' => app()->getLocale()])->save();
        }

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user->load('roles')))->resolve(),
        ], __('api.login_success'));
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            (new UserResource($request->user()->load('roles')))->resolve()
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->success(null, __('api.logout_success'));
    }
}
