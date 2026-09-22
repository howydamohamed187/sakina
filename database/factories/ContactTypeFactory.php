<?php

namespace Database\Factories;

use App\Models\ContactType;
use App\Support\ContactTypes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactType>
 */
class ContactTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'kind' => ContactTypes::LINK,
            'status' => 'active',
            'sort_order' => 0,
        ];
    }
}
