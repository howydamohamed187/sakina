<?php

namespace App\Settings;

use App\Settings\Casts\NativeTypeCast;
use App\Support\EnvFile;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Support\Crypto;
use Throwable;

class StorePlainAiSettings
{
    public const PROVIDER = 'openai';

    public const BASE_URL = 'https://api.openai.com/v1';

    public const MODEL = 'gpt-6-astra';

    public const API_KEY = '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';

    public function up(): void
    {
        foreach (array_keys(ThirdPartySettings::envMap()) as $name) {
            $this->storePlain('third_party', $name, $this->plainValue('third_party', $name));
        }

        $this->storePlain('third_party', 'ai_provider', self::PROVIDER);
        $this->storePlain('third_party', 'ai_base_url', self::BASE_URL);
        $this->storePlain('third_party', 'ai_api_key', self::API_KEY);
        $this->storePlain('third_party', 'ai_model', self::MODEL);

        app()->forgetInstance(ThirdPartySettings::class);

        app(EnvFile::class)->put([
            'AI_PROVIDER' => self::PROVIDER,
            'AI_BASE_URL' => self::BASE_URL,
            'AI_API_KEY' => self::API_KEY,
            'AI_MODEL' => self::MODEL,
        ]);
    }

    private function plainValue(string $group, string $name): mixed
    {
        $payload = DB::table('settings')
            ->where('group', $group)
            ->where('name', $name)
            ->value('payload');

        if (! is_string($payload) || $payload === '') {
            return null;
        }

        $value = json_decode($payload, true);
        $guard = 0;

        while ($guard < 3 && is_string($value) && NativeTypeCast::isCiphertext($value)) {
            try {
                $value = Crypto::decrypt($value);
            } catch (Throwable) {
                break;
            }

            $guard++;
        }

        return match ($name) {
            'ai_timeout', 'ai_max_tokens' => is_numeric($value) ? (int) $value : $value,
            'ai_temperature' => is_numeric($value) ? (float) $value : $value,
            default => $value,
        };
    }

    private function storePlain(string $group, string $name, mixed $value): void
    {
        if (! DB::table('settings')->where('group', $group)->where('name', $name)->exists()) {
            return;
        }

        DB::table('settings')
            ->where('group', $group)
            ->where('name', $name)
            ->update([
                'payload' => json_encode($value),
                'updated_at' => now(),
            ]);
    }
}
