<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'mobile' => fake()->unique()->numerify('9#########'),
            'role_id' => fn () => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => static::$password ??= 'Password123',
            'must_change_password' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function role(string $name): static
    {
        return $this->state(fn () => ['role_id' => Role::where('name', $name)->value('id')]);
    }

    public function admin(): static
    {
        return $this->role(Role::ADMIN);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
