<?php

namespace App\Filament\Forms\Components;

use App\Settings\ThirdPartySettings;
use Filament\Forms\Components\Field;

class LockedSecretInput extends Field
{
    public const MASK = '••••••••••••••••';

    protected string $view = 'filament.forms.components.locked-secret-input';

    public static function isUnchanged(mixed $value): bool
    {
        if (is_int($value) || is_float($value)) {
            return false;
        }

        if (! is_string($value)) {
            return true;
        }

        $value = trim($value);

        return $value === '' || $value === self::MASK;
    }

    protected bool $password = true;

    public function password(bool $condition = true): static
    {
        $this->password = $condition;

        return $this;
    }

    public function getIsPassword(): bool
    {
        return $this->password;
    }

    public function getHasStoredValue(): bool
    {
        return filled($this->getEncryptedPayload());
    }

    public function getEncryptedPayload(): ?string
    {
        return ThirdPartySettings::ciphertext($this->getName());
    }

    public function getMask(): string
    {
        return $this->getEncryptedPayload() ?: self::MASK;
    }
}
