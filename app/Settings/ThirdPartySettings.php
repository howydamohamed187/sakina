<?php

namespace App\Settings;

use App\Settings\Casts\NativeTypeCast;
use Spatie\LaravelSettings\Settings;

class ThirdPartySettings extends Settings
{
    public ?string $project_name = null;

    public ?string $firebase_file = null;

    public ?string $tranportal_id = null;

    public ?string $tranportal_password = null;

    public ?string $resource_key = null;

    public ?string $url = null;

    public bool $is_test = true;

    public string $ai_provider = 'openai';

    public string $ai_base_url = 'https://api.openai.com/v1';

    public ?string $ai_api_key = null;

    public string $ai_model = 'gpt-6-astra';

    public int $ai_timeout = 30;

    public int $ai_max_tokens = 1000;

    public float $ai_temperature = 0.3;

    public ?string $google_map_key = null;

    public static function group(): string
    {
        return 'third_party';
    }

    public static function casts(): array
    {
        return [
            'is_test' => new NativeTypeCast('bool'),
            'ai_provider' => new NativeTypeCast('string'),
            'ai_base_url' => new NativeTypeCast('string'),
            'ai_api_key' => new NativeTypeCast('string'),
            'ai_model' => new NativeTypeCast('string'),
            'ai_timeout' => new NativeTypeCast('int'),
            'ai_max_tokens' => new NativeTypeCast('int'),
            'ai_temperature' => new NativeTypeCast('float'),
            'google_map_key' => new NativeTypeCast('string'),
        ];
    }

    /**
     * Settings mirrored to the environment file.
     *
     * @return array<string, string>
     */
    public static function envMap(): array
    {
        return [
            'ai_provider' => 'AI_PROVIDER',
            'ai_base_url' => 'AI_BASE_URL',
            'ai_api_key' => 'AI_API_KEY',
            'ai_model' => 'AI_MODEL',
            'ai_timeout' => 'AI_TIMEOUT',
            'ai_max_tokens' => 'AI_MAX_TOKENS',
            'ai_temperature' => 'AI_TEMPERATURE',
            'google_map_key' => 'GOOGLE_MAP_KEY',
        ];
    }

    public static function encrypted(): array
    {
        return [
            'tranportal_id',
            'tranportal_password',
            'resource_key',
        ];
    }
}
