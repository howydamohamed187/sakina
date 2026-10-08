<?php

namespace Database\Factories;

use App\Models\RuqyahStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RuqyahStep>
 */
class RuqyahStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'سورة الفاتحة',
            'instruction' => 'قراءة سورة الفاتحة 7 مرات',
            'content' => 'بِسْمِ اللَّهِ الرَّحْمَـٰنِ الرَّحِيمِ',
            'repeat_count' => 7,
            'status' => 'active',
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => 'suspended',
        ]);
    }
}
