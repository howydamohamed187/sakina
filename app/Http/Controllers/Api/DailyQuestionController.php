<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DailyQuestionResource;
use App\Models\Customer;
use App\Services\DailyQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyQuestionController extends ApiController
{
    public function __construct(private readonly DailyQuestionService $questions) {}

    public function show(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $payload = $this->questions->todayFor($customer);

        return $this->success(
            (new DailyQuestionResource($payload['question'], $payload['assignment'], $payload['answer']))->resolve(),
            __('api.daily_question_ready')
        );
    }

    public function answer(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $data = $request->validate([
            'option_id' => ['required', 'integer'],
        ]);

        $this->questions->answer($customer, (int) $data['option_id']);
        $payload = $this->questions->todayFor($customer);

        return $this->success(
            (new DailyQuestionResource($payload['question'], $payload['assignment'], $payload['answer']))->resolve(),
            $payload['answer']?->is_correct
                ? __('api.daily_question_correct')
                : __('api.daily_question_wrong')
        );
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
