<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('developer.ai_provider', (string) config('services.ai.provider', 'openai'));
        $this->migrator->add('developer.ai_base_url', (string) config('services.ai.url', 'https://api.openai.com/v1'));
        $this->migrator->addEncrypted('developer.ai_api_key', config('services.ai.key'));
        $this->migrator->add('developer.ai_model', (string) config('services.ai.model', 'gpt-4o-mini'));
        $this->migrator->add('developer.ai_timeout', (int) config('services.ai.timeout', 30));
        $this->migrator->add('developer.ai_max_tokens', (int) config('services.ai.max_tokens', 1000));
        $this->migrator->add('developer.ai_temperature', (float) config('services.ai.temperature', 0.3));

        if ($this->migrator->exists('third_party.google_map_key')) {
            $this->migrator->rename('third_party.google_map_key', 'developer.google_map_key');
        } elseif (! $this->migrator->exists('developer.google_map_key')) {
            $this->migrator->addEncrypted('developer.google_map_key', null);
        }
    }
};
