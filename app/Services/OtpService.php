<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\VerificationCode;
use App\Support\StoredSettings;

class OtpService
{
    public function issue(Customer $customer, string $type = 'otp', ?string $channel = 'email'): string
    {
        $this->invalidateUnused($customer, $type);

        $code = $this->generateCode();
        $ttl = now()->addMinutes((int) config('customers.otp_ttl_minutes', 10));

        VerificationCode::query()->create([
            'customer_id' => $customer->id,
            'code' => $code,
            'type' => $type,
            'channel' => $channel,
            'expires_at' => $ttl,
        ]);

        return $code;
    }

    public function send(Customer|string $customer): string
    {
        if (is_string($customer)) {
            $customer = Customer::query()->where('email', $customer)->firstOrFail();
        }

        return $this->issue($customer);
    }

    public function check(Customer|string $customer, string $code, string $type = 'otp'): bool
    {
        return $this->findValid($customer, $code, $type) !== null;
    }

    public function verify(Customer|string $customer, string $code, string $type = 'otp'): bool
    {
        $record = $this->findValid($customer, $code, $type);

        if (! $record) {
            return false;
        }

        $record->forceFill(['used_at' => now()])->save();

        return true;
    }

    private function findValid(Customer|string $customer, string $code, string $type = 'otp'): ?VerificationCode
    {
        if (is_string($customer)) {
            $customer = Customer::query()->where('email', $customer)->first();
        }

        if (! $customer) {
            return null;
        }

        $record = VerificationCode::query()
            ->where('customer_id', $customer->id)
            ->where('type', $type)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $record || ! $record->isValid($code)) {
            return null;
        }

        return $record;
    }

    public function peek(Customer|string $customer, ?string $type = null): ?string
    {
        $query = VerificationCode::query()
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->when($type, fn ($builder) => $builder->where('type', $type))
            ->latest('id');

        if ($customer instanceof Customer) {
            $query->where('customer_id', $customer->id);
        } else {
            $query->whereHas('customer', fn ($builder) => $builder->where('email', $customer));
        }

        $code = $query->value('code');

        return is_string($code) ? $code : null;
    }

    public function forget(Customer|string $customer): void
    {
        if (is_string($customer)) {
            $customer = Customer::query()->where('email', $customer)->first();
        }

        if (! $customer) {
            return;
        }

        $this->invalidateUnused($customer);
    }

    private function invalidateUnused(Customer $customer, ?string $type = null): void
    {
        VerificationCode::query()
            ->where('customer_id', $customer->id)
            ->whereNull('used_at')
            ->when($type, fn ($builder) => $builder->where('type', $type))
            ->update(['used_at' => now()]);
    }

    public function generateCode(): string
    {
        $length = (int) config('customers.otp_length', 4);

        if (! StoredSettings::otpIsRandom()) {
            $static = (string) config('customers.otp_static', '1234');

            return str_pad(substr($static, 0, $length), $length, '0');
        }

        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}
