<?php

namespace App\Settings;

use App\Settings\Casts\NativeTypeCast;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Support\Crypto;
use Throwable;

class NormalizeEncryptedSettingPayloads
{
    public function up(): void
    {
        foreach ([
            'third_party' => [
                'ai_timeout' => 'int',
                'ai_max_tokens' => 'int',
                'ai_temperature' => 'float',
                'is_test' => 'bool',
            ],
            'developer' => [
                'ai_timeout' => 'int',
                'ai_max_tokens' => 'int',
                'ai_temperature' => 'float',
                'debug_mode' => 'bool',
                'otp_code_is_random' => 'bool',
            ],
        ] as $group => $properties) {
            foreach ($properties as $name => $type) {
                $this->normalize($group, $name, $type, $this->isEncryptedProperty($group, $name));
            }
        }

        foreach (['third_party', 'developer'] as $group) {
            foreach ([
                'ai_provider',
                'ai_base_url',
                'ai_api_key',
                'ai_model',
                'google_map_key',
                'tranportal_id',
                'tranportal_password',
                'resource_key',
            ] as $name) {
                $this->unwrapExtraEncryption($group, $name);
            }
        }
    }

    private function normalize(string $group, string $name, string $type, bool $encrypted): void
    {
        $decoded = $this->decoded($group, $name);

        if ($decoded === false) {
            return;
        }

        $cast = new NativeTypeCast($type);
        [$layers, $value] = $this->unwrap($decoded);

        try {
            $normalized = $cast->normalize($value);
        } catch (Throwable) {
            return;
        }

        if (! $encrypted) {
            if ($layers === 0 && get_debug_type($decoded) === $type) {
                return;
            }

            $this->store($group, $name, $normalized);

            return;
        }

        if ($layers === 1 && get_debug_type($value) === $type) {
            return;
        }

        $this->store($group, $name, Crypto::encrypt($normalized));
    }

    private function unwrapExtraEncryption(string $group, string $name): void
    {
        $decoded = $this->decoded($group, $name);

        if ($decoded === false || ! is_string($decoded)) {
            return;
        }

        [$layers, $value] = $this->unwrap($decoded);

        if ($layers <= 1 || (! is_string($value) && $value !== null)) {
            return;
        }

        $this->store($group, $name, $value === null ? null : Crypto::encrypt($value));
    }

    private function isEncryptedProperty(string $group, string $name): bool
    {
        if ($group !== 'third_party') {
            return false;
        }

        return in_array($name, ['ai_timeout', 'ai_max_tokens', 'ai_temperature'], true);
    }

    /**
     * @return array{0: int, 1: mixed}
     */
    private function unwrap(mixed $payload): array
    {
        $layers = 0;

        while ($layers < 3 && is_string($payload) && NativeTypeCast::isCiphertext($payload)) {
            try {
                $payload = Crypto::decrypt($payload);
            } catch (Throwable) {
                break;
            }

            $layers++;
        }

        return [$layers, $payload];
    }

    private function decoded(string $group, string $name): mixed
    {
        $payload = DB::table('settings')
            ->where('group', $group)
            ->where('name', $name)
            ->value('payload');

        if (! is_string($payload) || $payload === '') {
            return false;
        }

        return json_decode($payload, true);
    }

    private function store(string $group, string $name, mixed $payload): void
    {
        DB::table('settings')
            ->where('group', $group)
            ->where('name', $name)
            ->update([
                'payload' => json_encode($payload),
                'updated_at' => now(),
            ]);
    }
}
