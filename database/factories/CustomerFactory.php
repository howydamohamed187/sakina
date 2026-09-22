<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make(config('customers.default_password')),
            'location' => fake()->address(),
            'latitude' => fake()->latitude(22, 31),
            'longitude' => fake()->longitude(25, 35),
            'locale' => 'ar',
            'status' => 'active',
            'email_verified_at' => now(),
            'sort_order' => 0,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'email_verified_at' => null,
            'status' => 'pending',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => 'suspended',
        ]);
    }
}
