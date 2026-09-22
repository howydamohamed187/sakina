<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Rules\TripleName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerProfileController extends ApiController
{
    public function update(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', new TripleName],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('customers', 'public');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $customer->fill($data)->save();

        return $this->success(
            (new CustomerResource($customer->fresh()))->resolve(),
            __('api.profile_updated')
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
