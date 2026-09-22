<?php

namespace Database\Factories;

use App\Models\Dhikr;
use App\Support\DhikrCategories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dhikr>
 */
class DhikrFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'سيد الاستغفار',
            'body' => 'اللهم أنت ربي لا إله إلا أنت، خلقتني وأنا عبدك.',
            'category' => DhikrCategories::MORNING,
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
