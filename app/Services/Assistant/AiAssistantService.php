<?php

namespace App\Services\Assistant;

use App\Models\AiMessage;
use App\Services\Assistant\Contracts\AiChatProvider;
use App\Services\Providers\ProviderException;
use Illuminate\Support\Collection;

/**
 * Builds the prompt (Islamic assistant instructions + recent history + the new message)
 * and asks the configured AI provider for a reply.
 */
class AiAssistantService
{
    public function __construct(private readonly AiChatProvider $provider) {}

    /**
     * @param  Collection<int, AiMessage>  $history  previous messages, oldest first
     *
     * @throws ProviderException
     */
    public function reply(Collection $history, string $message, ?string $locale = null): string
    {
        return $this->provider->chat($this->buildMessages($history, $message, $locale));
    }

    /**
     * @param  Collection<int, AiMessage>  $history
     * @return array<int, array{role: string, content: string}>
     */
    public function buildMessages(Collection $history, string $message, ?string $locale = null): array
    {
        $system = trim((string) config('assistant.system_prompt'));

        if ($locale) {
            $system .= "\n- The app interface language is \"{$locale}\"; prefer it when the user's language is ambiguous.";
        }

        return [
            ['role' => 'system', 'content' => $system],
            ...$history
                ->filter(fn (AiMessage $item): bool => in_array($item->role, [AiMessage::ROLE_USER, AiMessage::ROLE_ASSISTANT], true))
                ->map(fn (AiMessage $item): array => ['role' => $item->role, 'content' => $item->content])
                ->values()
                ->all(),
            ['role' => 'user', 'content' => $message],
        ];
    }

    public function providerName(): string
    {
        return $this->provider->name();
    }

    public function model(): string
    {
        return $this->provider->model();
    }
}
