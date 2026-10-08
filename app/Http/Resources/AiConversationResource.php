<?php

namespace App\Http\Resources;

use App\Models\AiConversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin AiConversation */
class AiConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title ?: __('api.assistant.new_conversation_title'),
            'status' => $this->status,
            'last_message' => $this->whenLoaded(
                'latestUserMessage',
                fn (): ?string => $this->latestUserMessage
                    ? Str::limit($this->latestUserMessage->content, (int) config('assistant.preview_length', 120), '…')
                    : null,
            ),
            'opened_at' => $this->opened_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'messages' => $this->whenLoaded('messages', fn () => AiMessageResource::collection($this->messages)->resolve()),
        ];
    }
}
