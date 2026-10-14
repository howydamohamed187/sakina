<?php

namespace App\Services\Assistant\Providers;

use App\Services\Assistant\Contracts\AiChatProvider;
use App\Services\Providers\ProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
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

        if ($response->failed()) {
            $message = $this->upstreamMessage($response);

            Log::warning('AI provider error.', ['status' => $response->status(), 'error' => $message]);

            $reason = match ($response->status()) {
                429 => ProviderException::RATE_LIMITED,
                401, 403 => ProviderException::NOT_CONFIGURED,
                default => ProviderException::FAILED,
            };

            throw $this->error($reason, $message);
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw $this->error(ProviderException::INVALID_RESPONSE, 'AI provider returned an empty or invalid reply.');
        }

        return trim($content);
    }

    private function upstreamMessage(Response $response): string
    {
        $body = trim($response->body());

        if ($this->looksLikeHtml($body)) {
            $message = $this->htmlError($response, $body);
        } else {
            $message = $this->findMessage($response->json());

            if ($message === null) {
                $message = $body !== '' ? $body : 'AI provider error '.$response->status().'.';
            }
        }

        if (filled($this->key)) {
            $message = str_replace((string) $this->key, '[redacted]', $message);
        }

        return mb_substr($message, 0, 2000);
    }

    private function looksLikeHtml(string $body): bool
    {
        $start = strtolower(ltrim($body));

        return str_starts_with($start, '<!doctype html') || str_starts_with($start, '<html');
    }

    private function htmlError(Response $response, string $body): string
    {
        $title = null;

        if (preg_match('/<title>(.*?)<\/title>/si', $body, $matches) === 1) {
            $title = trim(html_entity_decode(strip_tags($matches[1])));
            $title = preg_replace('/\s+/', ' ', $title) ?: $title;
        }

        $url = (string) ($response->effectiveUri() ?? '');
        $status = $response->status();

        if (is_string($title) && $title !== '') {
            return $url !== '' ? "{$title} ({$status} {$url})" : "{$title} ({$status})";
        }

        return $url !== ''
            ? "AI provider returned an HTML error {$status} from {$url}."
            : "AI provider returned an HTML error {$status}.";
    }

    private function findMessage(mixed $payload): ?string
    {
        if (is_string($payload)) {
            $text = trim($payload);

            return $text === '' ? null : $text;
        }

        if (! is_array($payload)) {
            return null;
        }

        if (array_is_list($payload)) {
            foreach ($payload as $item) {
                $found = $this->findMessage($item);

                if ($found !== null) {
                    return $found;
                }
            }

            return null;
        }

        if (isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
            return trim($payload['message']);
        }

        foreach ($payload as $value) {
            if (is_array($value)) {
                $found = $this->findMessage($value);

                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function error(string $reason, string $message, ?Throwable $previous = null): ProviderException
    {
        return new ProviderException($reason, $message, $previous, ProviderException::SERVICE_AI);
    }
}
