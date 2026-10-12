<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach ([
            'ai_provider',
            'ai_base_url',
            'ai_api_key',
            'ai_model',
            'ai_timeout',
            'ai_max_tokens',
            'ai_temperature',
            'google_map_key',
        ] as $name) {
            $from = 'developer.'.$name;
            $to = 'third_party.'.$name;

            if (! $this->migrator->exists($from)) {
                continue;
            }

            if ($this->migrator->exists($to)) {
                $this->migrator->delete($to);
            }

            $this->migrator->rename($from, $to);
        }
    }
};
