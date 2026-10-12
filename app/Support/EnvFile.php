<?php

namespace App\Support;

use RuntimeException;

class EnvFile
{
    /**
     * @param  array<string, mixed>  $values
     */
    public function put(array $values): void
    {
        if (app()->runningUnitTests() && ! config('settings.write_env')) {
            return;
        }

        $path = $this->path();
        $contents = is_file($path) ? (string) file_get_contents($path) : '';
        $newline = str_contains($contents, "\r\n") ? "\r\n" : "\n";
        $lines = $contents === '' ? [] : preg_split("/\r\n|\n|\r/", $contents);
        $lines = $lines === false ? [] : $lines;
        $pending = [];

        foreach ($values as $key => $value) {
            $pending[(string) $key] = $this->format($value);
        }

        foreach ($lines as $index => $line) {
            if (! preg_match('/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=/', (string) $line, $matches)) {
                continue;
            }

            $key = $matches[1];

            if (! array_key_exists($key, $pending)) {
                continue;
            }

            $lines[$index] = $key.'='.$pending[$key];
            unset($pending[$key]);
        }

        foreach ($pending as $key => $value) {
            $lines[] = $key.'='.$value;
        }

        $result = implode($newline, $lines);

        if ($result !== '' && ! str_ends_with($result, $newline)) {
            $result .= $newline;
        }

        if (file_put_contents($path, $result, LOCK_EX) === false) {
            throw new RuntimeException('Unable to update the environment file.');
        }

        $this->syncRuntime($values);
    }

    public function path(): string
    {
        $path = config('settings.env_file');

        return is_string($path) && $path !== '' ? $path : base_path('.env');
    }

    private function format(mixed $value): string
    {
        $value = str_replace(["\r", "\n"], '', (string) ($value ?? ''));

        if ($value === '' || preg_match('/^[A-Za-z0-9_.:\/@+\-]+$/', $value) === 1) {
            return $value;
        }

        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function syncRuntime(array $values): void
    {
        $config = [];

        foreach ($values as $key => $value) {
            $string = $value === null ? '' : (string) $value;
            putenv($key.'='.$string);
            $_ENV[$key] = $string;
            $_SERVER[$key] = $string;

            $configKey = match ($key) {
                'AI_PROVIDER' => 'services.ai.provider',
                'AI_BASE_URL' => 'services.ai.url',
                'AI_API_KEY' => 'services.ai.key',
                'AI_MODEL' => 'services.ai.model',
                'AI_TIMEOUT' => 'services.ai.timeout',
                'AI_MAX_TOKENS' => 'services.ai.max_tokens',
                'AI_TEMPERATURE' => 'services.ai.temperature',
                'GOOGLE_MAP_KEY' => 'services.google_maps.key',
                default => null,
            };

            if ($configKey === null) {
                continue;
            }

            $config[$configKey] = match ($key) {
                'AI_TIMEOUT', 'AI_MAX_TOKENS' => (int) $string,
                'AI_TEMPERATURE' => (float) $string,
                default => $string,
            };
        }

        if ($config !== []) {
            config($config);
        }
    }
}
