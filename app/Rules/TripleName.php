<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TripleName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::isValid($value)) {
            $fail(__('validation.triple_name'));
        }
    }

    public static function isValid(string $value): bool
    {
        $parts = self::parts($value);

        if (count($parts) !== 3) {
            return false;
        }

        foreach ($parts as $part) {
            if (mb_strlen($part) < 2 || ! preg_match('/^[\p{L}\p{M}]+$/u', $part)) {
                return false;
            }
        }

        return true;
    }

    public static function normalize(string $value): string
    {
        return implode(' ', self::parts($value));
    }

    /**
     * @return array<int, string>
     */
    public static function parts(string $value): array
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        if ($normalized === '') {
            return [];
        }

        return explode(' ', $normalized);
    }
}
