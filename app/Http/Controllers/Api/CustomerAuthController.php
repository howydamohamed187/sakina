<?php

namespace App\Http\Controllers\Api;

use App\Actions\SendVerificationCode;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Rules\TripleName;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerAuthController extends ApiController
{
    public function __construct(private readonly OtpService $otp) {}

    public function register(Request $request): JsonResponse
    {
        if ($request->filled('address') && ! $request->filled('location')) {
            $request->merge(['location' => $request->input('address')]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', new TripleName],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'password' => ['required', 'string', 'min:6'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('customers', 'public');
        }

        $customer = Customer::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'avatar' => $data['avatar'] ?? null,
            'location' => $data['location'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'locale' => app()->getLocale(),
            'status' => 'pending',
            'sort_order' => (int) Customer::query()->max('sort_order') + 1,
        ]);

        $this->sendOtp($customer);

        return $this->success(
            $this->tokenPayload($customer),
            __('api.register_success'),
            201
        );
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = $this->findCustomerAccount($credentials['email']);

        if (! $customer || ! Hash::check($credentials['password'], $customer->password)) {
            return $this->error(__('api.login_failed'), [], 401);
        }

        if ($customer->isSuspended() || ! in_array($customer->status, ['active', 'pending'], true)) {
            return $this->error(__('api.account_inactive'), [], 403);
        }

        if (! $customer->isActive() || $customer->needsActivation()) {
            $this->sendOtp($customer);

            return $this->error(__('api.account_needs_activation'), [], 400, [
                'need_activation' => 1,
            ]);
        }

        return $this->success($this->tokenPayload($customer), __('api.login_success'));
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        [$customer, $code] = $this->validateVerificationRequest($request);

        if ($customer instanceof JsonResponse) {
            return $customer;
        }

        if (! $this->otp->check($customer, $code)) {
            return $this->error(__('api.otp_invalid'), [], 422);
        }

        return $this->success([
            'email' => $customer->email,
            'valid' => 1,
            'need_activation' => $customer->needsActivation() ? 1 : 0,
        ], __('api.otp_valid'));
    }

    public function verifyAccount(Request $request): JsonResponse
    {
        [$customer, $code] = $this->validateVerificationRequest($request);

        if ($customer instanceof JsonResponse) {
            return $customer;
        }

        if (! $this->otp->verify($customer, $code)) {
            return $this->error(__('api.otp_invalid'), [], 422);
        }

        $customer->forceFill([
            'email_verified_at' => $customer->email_verified_at ?? now(),
            'status' => 'active',
            'locale' => app()->getLocale(),
        ])->save();

        return $this->success($this->tokenPayload($customer), __('api.account_activated'));
    }

    public function resendCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $customer = $this->findCustomerAccount($data['email']);

        if (! $customer) {
            return $this->error(__('api.login_failed'), [], 401);
        }

        if ($customer->isSuspended()) {
            return $this->error(__('api.account_inactive'), [], 403);
        }

        $payload = $this->sendOtp($customer);

        if ($customer->needsActivation()) {
            $payload['need_activation'] = 1;
        }

        return $this->success($payload, __('api.otp_resent'));
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $customer = $this->findCustomerAccount($data['email']);

        if (! $customer) {
            return $this->error(__('api.login_failed'), [], 401);
        }

        if ($customer->isSuspended()) {
            return $this->error(__('api.account_inactive'), [], 403);
        }

        return $this->success(
            $this->sendOtp($customer, 'password_reset'),
            __('api.password_reset_sent')
        );
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:4'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $customer = $this->findCustomerAccount($data['email']);

        if (! $customer) {
            return $this->error(__('api.login_failed'), [], 401);
        }

        if ($customer->isSuspended()) {
            return $this->error(__('api.account_inactive'), [], 403);
        }

        if (! $this->otp->verify($customer, (string) $data['code'], 'password_reset')) {
            return $this->error(__('api.otp_invalid'), [], 422);
        }

        $customer->forceFill([
            'password' => $data['password'],
        ])->save();

        $customer->tokens()->delete();

        return $this->success(null, __('api.password_reset_success'));
    }

    public function me(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer || ! $customer->isCustomer()) {
            return $this->error(__('api.not_found'), [], 403);
        }

        return $this->success((new CustomerResource($customer))->resolve());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success(null, __('api.logout_success'));
    }

    /**
     * @return array{0: Customer|JsonResponse, 1: string}
     */
    private function validateVerificationRequest(Request $request): array
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:4'],
        ]);

        $customer = $this->findCustomerAccount($data['email']);

        if (! $customer) {
            return [$this->error(__('api.otp_invalid'), [], 422), ''];
        }

        if ($customer->isSuspended()) {
            return [$this->error(__('api.account_inactive'), [], 403), ''];
        }

        return [$customer, (string) $data['code']];
    }

    private function findCustomerAccount(?string $email): ?Customer
    {
        $customer = Customer::query()->where('email', $email)->first();

        if (! $customer || ! $customer->isCustomer()) {
            return null;
        }

        return $customer;
    }

    private function tokenPayload(Customer $customer): array
    {
        $token = $customer->createToken('customer')->plainTextToken;

        return array_merge(
            (new CustomerResource($customer->fresh()))->resolve(),
            [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        );
    }

    private function sendOtp(Customer $customer, string $type = 'otp'): array
    {
        $code = SendVerificationCode::run(
            user: $customer,
            email: $customer->email,
            phoneForSms: $customer->getAttribute('phone'),
            type: $type,
        );

        $payload = [
            'email' => $customer->email,
            'expires_in' => (int) config('customers.otp_ttl_minutes') * 60,
        ];

        if (app()->environment(['local', 'testing'])) {
            $payload['code'] = $code;
        }

        return $payload;
    }
}
