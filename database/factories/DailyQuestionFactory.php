<?php

namespace Database\Factories;

use App\Models\DailyQuestion;
use App\Support\QuestionCategories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyQuestion>
 */
class DailyQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'body' => 'من أول الأنبياء؟',
            'category' => QuestionCategories::RELIGIOUS,
            'status' => 'active',
            'sort_order' => 0,
        ];
    }

    public function withOptions(?array $options = null): static
    {
        return $this->afterCreating(function (DailyQuestion $question) use ($options): void {
            if ($question->options()->exists()) {
                return;
            }

            $rows = $options ?? [
                ['body' => 'آدم عليه السلام', 'is_correct' => true],
                ['body' => 'نوح عليه السلام', 'is_correct' => false],
                ['body' => 'إبراهيم عليه السلام', 'is_correct' => false],
                ['body' => 'موسى عليه السلام', 'is_correct' => false],
            ];

            foreach ($rows as $index => $row) {
                $question->options()->create([
                    'body' => $row['body'],
                    'is_correct' => (bool) ($row['is_correct'] ?? false),
                    'sort_order' => $index + 1,
                ]);
            }
        });
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => 'suspended',
        ]);
    }
}
