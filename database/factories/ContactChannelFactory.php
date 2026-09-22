<?php

namespace Database\Factories;

use App\Models\ContactChannel;
use App\Models\ContactType;
use App\Support\ContactTypes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactChannel>
 */
class ContactChannelFactory extends Factory
{
    public function definition(): array
    {
        $type = ContactType::query()->firstOrCreate(
            ['kind' => ContactTypes::LINK],
            ['name' => ContactTypes::label(ContactTypes::LINK), 'status' => 'active', 'sort_order' => 0],
        );

        return [
            'name' => fake()->company(),
            'contact_type_id' => $type->id,
            'type' => $type->kind,
            'value' => fake()->url(),
            'status' => 'active',
            'sort_order' => 0,
        ];
    }

    public function phone(): static
    {
        return $this->state(fn (): array => [
            'type' => ContactTypes::PHONE,
            'value' => '+201012345678',
        ]);
    }

    public function account(): static
    {
        return $this->state(fn (): array => [
            'type' => ContactTypes::ACCOUNT,
            'value' => fake()->numerify('##########'),
        ]);
    }
}
