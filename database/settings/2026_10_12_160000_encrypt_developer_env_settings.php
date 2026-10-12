<?php

use App\Settings\DeveloperSettings;
use App\Support\EnvFile;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach ([
            'developer.ai_provider',
            'developer.ai_base_url',
            'developer.ai_model',
            'developer.ai_timeout',
            'developer.ai_max_tokens',
            'developer.ai_temperature',
        ] as $property) {
            if ($this->migrator->exists($property)) {
                $this->migrator->encrypt($property);
            }
        }

        if (app()->runningUnitTests()) {
            return;
        }

        $settings = app(DeveloperSettings::class);

        app(EnvFile::class)->put([
            'AI_PROVIDER' => $settings->ai_provider,
            'AI_BASE_URL' => $settings->ai_base_url,
            'AI_API_KEY' => $settings->ai_api_key,
            'AI_MODEL' => $settings->ai_model,
            'AI_TIMEOUT' => $settings->ai_timeout,
            'AI_MAX_TOKENS' => $settings->ai_max_tokens,
            'AI_TEMPERATURE' => $settings->ai_temperature,
            'GOOGLE_MAP_KEY' => $settings->google_map_key,
        ]);
    }
};
