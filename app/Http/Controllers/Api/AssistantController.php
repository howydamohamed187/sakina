<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SendAssistantMessageRequest;
use App\Http\Resources\AiConversationResource;
use App\Http\Resources\AiMessageResource;
use App\Models\Customer;
use App\Services\Assistant\ChatService;
use App\Services\Assistant\ConversationClosedException;
use App\Services\Providers\ProviderException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends ApiController
{
    public function __construct(private readonly ChatService $chat) {}

    public function conversations(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $paginator = $this->chat->conversations($customer, min(max($request->integer('per_page', 20), 1), 50));

        return $this->paginatedWithMeta(
            AiConversationResource::collection($paginator->getCollection())->resolve(),
            $paginator,
            __('api.assistant.conversations_ready'),
        );
    }

    public function open(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        ['conversation' => $conversation, 'created' => $created] = $this->chat->open($customer);

        return $this->success(
            (new AiConversationResource($conversation))->resolve(),
            __($created ? 'api.assistant.conversation_opened' : 'api.assistant.conversation_already_open'),
            $created ? 201 : 200,
        );
    }

    public function show(Request $request, int $conversation): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        try {
            $model = $this->chat->find($customer, $conversation)->load('messages');
        } catch (ModelNotFoundException) {
            return $this->notFound();
        }

        return $this->success((new AiConversationResource($model))->resolve(), __('api.assistant.conversation_ready'));
    }

    public function send(SendAssistantMessageRequest $request, int $conversation): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        try {
            $result = $this->chat->send($customer, $conversation, $request->validated('message'), app()->getLocale());
        } catch (ModelNotFoundException) {
            return $this->notFound();
        } catch (ConversationClosedException) {
            return $this->error(__('api.assistant.conversation_closed'), [], 409);
        } catch (ProviderException $e) {
            report($e);

            return $this->error(__('api.assistant.errors.'.$e->reason), [], $e->status());
        }

        return $this->success([
            'conversation' => (new AiConversationResource($result['conversation']))->resolve(),
            'user_message' => (new AiMessageResource($result['user_message']))->resolve(),
            'assistant_message' => (new AiMessageResource($result['assistant_message']))->resolve(),
        ], __('api.assistant.message_sent'));
    }

    public function close(Request $request, int $conversation): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        try {
            $model = $this->chat->close($customer, $conversation);
        } catch (ModelNotFoundException) {
            return $this->notFound();
        }

        return $this->success((new AiConversationResource($model))->resolve(), __('api.assistant.conversation_closed_done'));
    }

    public function destroy(Request $request, int $conversation): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        try {
            $this->chat->delete($customer, $conversation);
        } catch (ModelNotFoundException) {
            return $this->notFound();
        }

        return $this->success(null, __('api.assistant.conversation_deleted'));
    }

    private function notFound(): JsonResponse
    {
        return $this->error(__('api.assistant.conversation_not_found'), [], 404);
    }

    private function customer(Request $request): Customer|JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        return $user;
    }
}
