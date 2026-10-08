<?php

namespace App\Services\Assistant;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Customer;
use App\Services\Providers\ProviderException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Customer conversations with the Smart Assistant. Every lookup is scoped to the
 * customer, so a conversation of another customer behaves exactly like a missing one.
 */
class ChatService
{
    private const QUESTION_PREFIXES = [
        'ما هي', 'ما هو', 'ماهي', 'ماهو', 'ما', 'ماذا', 'لماذا', 'كيف', 'هل', 'متى', 'أين', 'اين',
        'من هو', 'من هي', 'كم', 'اشرح لي', 'اشرح', 'وضح لي', 'وضح', 'أريد أن أعرف', 'اريد ان اعرف', 'عرفني على', 'حدثني عن',
        'what is', 'what are', 'who is', 'how do i', 'how to', 'how', 'why', 'when', 'where', 'can you', 'tell me about', 'explain',
    ];

    public function __construct(private readonly AiAssistantService $assistant) {}

    /**
     * Returns the customer's open conversation, or opens a new one when none is open.
     *
     * @return array{conversation: AiConversation, created: bool}
     */
    public function open(Customer $customer): array
    {
        return DB::transaction(function () use ($customer): array {
            Customer::query()->whereKey($customer->getKey())->lockForUpdate()->value('id');

            $existing = $customer->aiConversations()->open()->latest('id')->first();

            if ($existing) {
                return ['conversation' => $existing, 'created' => false];
            }

            $conversation = $customer->aiConversations()->create([
                'status' => AiConversation::STATUS_OPEN,
                'opened_at' => now(),
            ]);

            return ['conversation' => $conversation->refresh(), 'created' => true];
        });
    }

    /**
     * Sends the customer's message in an open conversation and stores it with the AI reply.
     * The context sent to the AI is built here from the conversation's stored messages;
     * the app only sends the new message. The AI is called before anything is written,
     * so a provider failure never leaves an unanswered message behind.
     *
     * @return array{conversation: AiConversation, user_message: AiMessage, assistant_message: AiMessage}
     *
     * @throws ModelNotFoundException when the conversation is not the customer's
     * @throws ConversationClosedException
     * @throws ProviderException
     */
    public function send(Customer $customer, int $conversationId, string $message, ?string $locale = null): array
    {
        $message = trim($message);
        $conversation = $this->find($customer, $conversationId);

        if (! $conversation->isOpen()) {
            throw new ConversationClosedException;
        }

        $history = $conversation->messages()
            ->reorder()
            ->latest('id')
            ->limit(max(0, (int) config('assistant.history_limit', 20)))
            ->get(['id', 'role', 'content'])
            ->reverse()
            ->values();

        $reply = $this->assistant->reply($history, $message, $locale);

        return DB::transaction(function () use ($conversation, $message, $reply): array {
            $conversation = AiConversation::query()->lockForUpdate()->findOrFail($conversation->id);

            if (! $conversation->isOpen()) {
                throw new ConversationClosedException;
            }

            $userMessage = $conversation->messages()->create(['role' => AiMessage::ROLE_USER, 'content' => $message]);
            $assistantMessage = $conversation->messages()->create(['role' => AiMessage::ROLE_ASSISTANT, 'content' => $reply]);

            $conversation->forceFill([
                'title' => $conversation->title ?: self::titleFrom($message),
                'last_message_at' => $assistantMessage->created_at,
            ])->save();

            return [
                'conversation' => $conversation,
                'user_message' => $userMessage,
                'assistant_message' => $assistantMessage,
            ];
        });
    }

    /**
     * Closes the conversation; closing an already closed one is a no-op.
     *
     * @throws ModelNotFoundException
     */
    public function close(Customer $customer, int $conversationId): AiConversation
    {
        $conversation = $this->find($customer, $conversationId);

        if ($conversation->isOpen()) {
            $conversation->forceFill([
                'status' => AiConversation::STATUS_CLOSED,
                'closed_at' => now(),
            ])->save();
        }

        return $conversation;
    }

    /**
     * Summaries only (no message bodies besides the last question preview), newest first.
     */
    public function conversations(Customer $customer, int $perPage): LengthAwarePaginator
    {
        return $customer->aiConversations()
            ->with(['latestUserMessage' => fn ($query) => $query->select('ai_messages.id', 'ai_messages.ai_conversation_id', 'ai_messages.content')])
            ->orderByRaw('COALESCE(last_message_at, opened_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate($perPage, ['id', 'customer_id', 'title', 'status', 'opened_at', 'closed_at', 'last_message_at', 'created_at']);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function find(Customer $customer, int $conversationId): AiConversation
    {
        return $customer->aiConversations()->findOrFail($conversationId);
    }

    /**
     * Deletes the conversation and all of its messages.
     *
     * @throws ModelNotFoundException
     */
    public function delete(Customer $customer, int $conversationId): void
    {
        $conversation = $this->find($customer, $conversationId);

        DB::transaction(function () use ($conversation): void {
            AiMessage::query()->where('ai_conversation_id', $conversation->id)->delete();
            $conversation->delete();
        });
    }

    /**
     * Short readable title from the first question, without an extra AI request:
     * "ما هي أركان الإسلام وما معنى الشهادة؟" becomes "أركان الإسلام".
     */
    public static function titleFrom(string $message): string
    {
        $maxLength = (int) config('assistant.title_max_length', 60);
        $text = trim((string) preg_replace('/\s+/u', ' ', $message));

        $firstClause = trim((string) (preg_split('/[؟?!.,،؛;:\n]|\s(?:وما|وماذا|وكيف|وهل|ولماذا|ومتى|وأين|and|or)\s/u', $text)[0] ?? ''));

        $title = $firstClause;

        foreach (self::QUESTION_PREFIXES as $prefix) {
            if (preg_match('/^'.preg_quote($prefix, '/').'\s+(.+)$/iu', $title, $matches)) {
                $title = trim($matches[1]);

                break;
            }
        }

        $title = trim(implode(' ', array_slice(explode(' ', $title), 0, 6)));

        if (mb_strlen($title) < 2) {
            $title = $text;
        }

        return Str::limit($title, $maxLength, '…') ?: Str::limit($text, $maxLength, '…');
    }
}
