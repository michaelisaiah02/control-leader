<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'employeeID' => fake()->randomNumber(5, true),
            'password' => static::$password ??= Hash::make('12345'),
        ];
    }

    /**
     * Indicate that the model's password should be plain text.
     */
    public function plainPassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => $attributes['password'],
        ]);
    }

    /**
     * Indicate that the model's password should be hashed.
     */
    public function hashedPassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => Hash::make($attributes['password']),
        ]);
    }
}
