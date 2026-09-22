<?php

namespace App\Rules;

use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidMobileNumber implements ValidationRule
{
    public function __construct(private readonly ?string $country = PhoneNumber::DEFAULT_COUNTRY) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (! PhoneNumber::isValid($this->country, is_string($value) ? $value : null)) {
            $fail(__('app.phone.invalid'));
        }
    }
}
