<?php

use App\Support\EnvFile;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Support\Crypto;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach ([
            'ai_provider',
            'ai_base_url',
            'ai_model',
            'ai_timeout',
            'ai_max_tokens',
            'ai_temperature',
        ] as $name) {
            $property = 'developer.'.$name;

            if (! $this->migrator->exists($property) || $this->alreadyEncrypted($name)) {
                continue;
            }

            $this->migrator->encrypt($property);
        }

        $values = [];

        foreach ([
            'ai_provider' => 'AI_PROVIDER',
            'ai_base_url' => 'AI_BASE_URL',
            'ai_api_key' => 'AI_API_KEY',
            'ai_model' => 'AI_MODEL',
            'ai_timeout' => 'AI_TIMEOUT',
            'ai_max_tokens' => 'AI_MAX_TOKENS',
            'ai_temperature' => 'AI_TEMPERATURE',
            'google_map_key' => 'GOOGLE_MAP_KEY',
        ] as $name => $envKey) {
            if (! $this->migrator->exists('developer.'.$name)) {
                continue;
            }

            $values[$envKey] = $this->plainValue($name);
        }

        if ($values !== []) {
            app(EnvFile::class)->put($values);
        }
    }

    private function alreadyEncrypted(string $name): bool
    {
        $payload = $this->storedPayload($name);

        if (! is_string($payload)) {
            return false;
        }

        try {
            Crypto::decrypt($payload);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function plainValue(string $name): mixed
    {
        $payload = $this->storedPayload($name);

        if (! is_string($payload)) {
            return $payload;
        }

        try {
            return Crypto::decrypt($payload);
        } catch (\Throwable) {
            return $payload;
        }
    }

    private function storedPayload(string $name): mixed
    {
        $payload = DB::table('settings')
            ->where('group', 'developer')
            ->where('name', $name)
            ->value('payload');

        if (! is_string($payload) || $payload === '') {
            return null;
        }

        return json_decode($payload, true);
    }
};
