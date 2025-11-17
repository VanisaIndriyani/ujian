<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            'email' => fake()->unique()->safeEmail(),
            'nip' => null,
            'nisn' => fake()->unique()->numerify('20########'),
            'role' => 'murid',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => 'admin',
            'nisn' => null,
            'nip' => null,
            'classroom' => null,
            'email' => 'admin@example.com',
        ]);
    }

    public function guru(): static
    {
        return $this->state(fn () => [
            'role' => 'guru',
            'nip' => fake()->unique()->numerify('1975####'),
            'nisn' => null,
            'classroom' => null,
            'email' => fake()->unique()->safeEmail(),
        ]);
    }

    public function murid(): static
    {
        return $this->state(fn () => [
            'role' => 'murid',
            'nip' => null,
            'nisn' => fake()->unique()->numerify('20########'),
            'classroom' => fake()->randomElement(['Radiologi 2025 A', 'Radiologi 2025 B']),
            'email' => fake()->unique()->safeEmail(),
        ]);
    }
}
