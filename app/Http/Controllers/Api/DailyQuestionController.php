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

        return $this->success(
            $this->payload($customer),
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

        $answer = $this->questions->answer($customer, (int) $data['option_id']);

        return $this->success(
            $this->payload($customer),
            $answer->is_correct
                ? __('api.daily_question_correct')
                : __('api.daily_question_wrong')
        );
    }

    private function payload(Customer $customer): array
    {
        $payload = $this->questions->todayFor($customer);

        return (new DailyQuestionResource(
            $payload['question'],
            $payload['assignment'],
            $payload['answer'],
            $this->questions->statsFor($customer),
        ))->resolve();
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
