<?php

namespace Database\Factories;

use App\Models\Dua;
use App\Support\DuaCategories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dua>
 */
class DuaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'دعاء الكرب',
            'body' => 'لا إله إلا الله العظيم الحليم، لا إله إلا الله رب العرش العظيم.',
            'category' => DuaCategories::DISTRESS,
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
