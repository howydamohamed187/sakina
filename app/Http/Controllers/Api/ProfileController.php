<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Rules\ValidMobileNumber;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends ApiController
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user instanceof Customer) {
            return $this->success((new CustomerResource($user))->resolve());
        }

        return $this->success(
            (new UserResource($user->load('roles')))->resolve()
        );
    }

    public function update(Request $request): JsonResponse
    {
        if ($request->user() instanceof Customer) {
            return app(CustomerProfileController::class)->update($request);
        }

        $user = $request->user();
        $country = $request->input('phone_country', PhoneNumber::DEFAULT_COUNTRY);

        if ($request->filled('phone')) {
            $request->merge([
                'phone' => PhoneNumber::toE164($country, $request->string('phone')->toString()),
            ]);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_country' => ['nullable', 'string', Rule::in(array_keys(PhoneNumber::countries()))],
            'phone' => ['nullable', 'string', 'max:20', new ValidMobileNumber($country), Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        unset($data['phone_country']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->fill($data)->save();

        return $this->success(
            (new UserResource($user->fresh()->load('roles')))->resolve(),
            __('api.profile_updated')
        );
    }
}
