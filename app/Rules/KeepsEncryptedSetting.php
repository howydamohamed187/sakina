<?php

namespace App\Rules;

use App\Filament\Forms\Components\LockedSecretInput;
use App\Settings\ThirdPartySettings;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KeepsEncryptedSetting implements ValidationRule
{
    /**
     * @param  list<string>  $rules
     */
    public function __construct(
        private readonly string $name,
        private readonly array $rules,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (LockedSecretInput::isUnchanged($value) || ThirdPartySettings::isSameCiphertext($this->name, $value)) {
            return;
        }

        $validator = validator(
            [$this->name => is_string($value) ? trim($value) : $value],
            [$this->name => $this->rules],
        );

        if ($validator->fails()) {
            $fail((string) $validator->errors()->first($this->name));
        }
    }
}
