<?php

namespace Database\Factories;

use App\Models\Hadith;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hadith>
 */
class HadithFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'حديث عن الأعمال',
            'body' => 'إنما الأعمال بالنيات، وإنما لكل امرئ ما نوى.',
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
