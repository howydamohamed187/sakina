<?php

namespace App\Settings\Casts;

use Spatie\LaravelSettings\SettingsCasts\SettingsCast;
use Spatie\LaravelSettings\Support\Crypto;
use Throwable;
use UnexpectedValueException;

/**
 * Turns decrypted setting payloads back into the property's real PHP type.
 * A numeric value that was encrypted as a string, or wrapped more than once, still loads as int, float, or bool.
 */
class NativeTypeCast implements SettingsCast
{
    public function __construct(private readonly string $type) {}

    public function get(mixed $payload): mixed
    {
        return $this->normalize($payload);
    }

    public function set(mixed $payload): mixed
    {
        return $this->normalize($payload);
    }

    public function normalize(mixed $payload): mixed
    {
        return match ($this->type) {
            'int' => $this->asInt($this->unwrap($payload)),
            'float' => $this->asFloat($this->unwrap($payload)),
            'bool' => $this->asBool($this->unwrap($payload)),
            default => $this->unwrap($payload),
        };
    }

    private function unwrap(mixed $payload): mixed
    {
        $guard = 0;

        while ($guard < 3 && is_string($payload) && self::isCiphertext($payload)) {
            try {
                $payload = Crypto::decrypt($payload);
            } catch (Throwable) {
                break;
            }

            $guard++;
        }

        return $payload;
    }

    public static function isCiphertext(string $value): bool
    {
        if (! str_starts_with($value, 'eyJ')) {
            return false;
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return false;
        }

        $json = json_decode($decoded, true);

        return is_array($json) && isset($json['iv'], $json['value'], $json['mac']);
    }

    private function asInt(mixed $payload): int
    {
        if (is_int($payload)) {
            return $payload;
        }

        if (is_float($payload) || (is_string($payload) && is_numeric($payload))) {
            return (int) $payload;
        }

        throw new UnexpectedValueException('Integer setting did not contain a number.');
    }

    private function asFloat(mixed $payload): float
    {
        if (is_float($payload)) {
            return $payload;
        }

        if (is_int($payload) || (is_string($payload) && is_numeric($payload))) {
            return (float) $payload;
        }

        throw new UnexpectedValueException('Float setting did not contain a number.');
    }

    private function asBool(mixed $payload): bool
    {
        if (is_bool($payload)) {
            return $payload;
        }

        if (is_int($payload) || is_float($payload)) {
            return $payload != 0;
        }

        if (is_string($payload)) {
            $parsed = filter_var($payload, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($parsed !== null) {
                return $parsed;
            }
        }

        throw new UnexpectedValueException('Boolean setting did not contain a boolean.');
    }
}
