<?php

namespace App\Services\Assistant\Providers;

use App\Services\Assistant\Contracts\AiChatProvider;
use App\Services\Providers\ProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * OpenAI Chat Completions API (POST {base_url}/chat/completions). Also works with
 * any OpenAI-compatible provider (OpenRouter, Groq, DeepSeek, Gemini compatibility endpoint...).
 */
class OpenAiCompatibleProvider implements AiChatProvider
{
    public function __construct(
        private readonly string $url,
        private readonly ?string $key,
        private readonly string $model,
        private readonly int $timeout,
        private readonly int $maxTokens,
        private readonly float $temperature,
    ) {}

    public function name(): string
    {
        return 'openai';
    }

    public function model(): string
    {
        return $this->model;
    }

    public function chat(array $messages): string
    {
        if ($this->url === '' || blank($this->key) || $this->model === '') {
            throw $this->error(ProviderException::NOT_CONFIGURED, 'AI provider is not configured (AI_BASE_URL / AI_API_KEY / AI_MODEL).');
        }

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->withToken((string) $this->key)
                ->post(rtrim($this->url, '/').'/chat/completions', [
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                ]);
        } catch (ConnectionException $e) {
            throw $this->error(ProviderException::TIMEOUT, $e->getMessage(), $e);
        }

        if ($response->status() === 429) {
            throw $this->error(ProviderException::RATE_LIMITED, 'AI provider rate limit reached.');
        }

        if (in_array($response->status(), [401, 403], true)) {
            Log::error('AI provider rejected the credentials.', ['status' => $response->status()]);

            throw $this->error(ProviderException::NOT_CONFIGURED, 'AI provider rejected the credentials.');
        }

        if ($response->failed()) {
            Log::warning('AI provider error.', ['status' => $response->status(), 'error' => $response->json('error.message')]);

            throw $this->error(ProviderException::FAILED, 'AI provider error '.$response->status().'.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw $this->error(ProviderException::INVALID_RESPONSE, 'AI provider returned an empty or invalid reply.');
        }

        return trim($content);
    }

    private function error(string $reason, string $message, ?Throwable $previous = null): ProviderException
    {
        return new ProviderException($reason, $message, $previous, ProviderException::SERVICE_AI);
    }
}
