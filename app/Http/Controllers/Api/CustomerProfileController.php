<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Rules\TripleName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CustomerProfileController extends ApiController
{
    public function update(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        if ($request->filled('address') && ! $request->filled('location')) {
            $request->merge(['location' => $request->input('address')]);
        }

        if ($request->filled('email')) {
            $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', new TripleName],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'latitude' => ['required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['required_with:latitude', 'numeric', 'between:-180,180'],
        ]);

        unset($data['avatar']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $oldAvatar = $customer->avatar;

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('customers', 'public');
        }

        $emailChanged = array_key_exists('email', $data) && $data['email'] !== mb_strtolower((string) $customer->email);

        $customer->forceFill($data)->save();

        if (isset($data['avatar']) && $oldAvatar && $oldAvatar !== $data['avatar']) {
            Storage::disk('public')->delete($oldAvatar);
        }

        if ($emailChanged) {
            $customer->tokens()->delete();
        }

        return $this->success(
            [
                ...(new CustomerResource($customer->fresh()))->resolve(),
                'logged_out' => $emailChanged,
            ],
            __($emailChanged ? 'api.profile_updated_relogin' : 'api.profile_updated')
        );
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        if ($request->filled('address') && ! $request->filled('location')) {
            $request->merge(['location' => $request->input('address')]);
        }

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $customer->forceFill(array_filter($data, fn (mixed $value): bool => $value !== null))->save();

        return $this->success(
            (new CustomerResource($customer->fresh()))->resolve(),
            __('api.location_updated')
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $customer->forceDelete();

        return $this->success(null, __('api.account_deleted'));
    }

    private function customer(Request $request): Customer|JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        return $user;
    }
}
