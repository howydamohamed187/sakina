<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AnswerDailyQuizRequest;
use App\Http\Resources\DailyQuizResource;
use App\Models\Customer;
use App\Services\DailyQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyQuizController extends ApiController
{
    public function __construct(private readonly DailyQuestionService $quiz) {}

    public function today(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        return $this->success($this->payload($customer), __('api.daily_question_ready'));
    }

    public function answer(AnswerDailyQuizRequest $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        $answer = $this->quiz->answer($customer, (int) $request->validated('answer_id'), field: 'answer_id');
        $result = DailyQuizResource::result($this->quiz->todayFor($customer)['question'], $answer);

        return $this->success([
            'is_correct' => $result['is_correct'],
            'message' => $result['result_message'],
            'selected_answer_id' => $result['selected_answer_id'],
            'correct_answer' => $result['correct_answer'],
            'explanation' => $result['explanation'],
        ], $result['result_message']);
    }

    public function statistics(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        return $this->success($this->quiz->statistics($customer), __('api.daily_quiz.statistics_ready'));
    }

    private function payload(Customer $customer): array
    {
        $today = $this->quiz->todayFor($customer);

        return (new DailyQuizResource(
            $today['question'],
            $today['assignment'],
            $today['answer'],
            DailyQuestionService::shuffledOptions($today['question'], $today['assignment']),
        ))->resolve();
    }
}
