<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => $this->faker->phoneNumber(),
            'department_id' => Department::factory(),
            'employee_id' => 'EMP' . $this->faker->unique()->numberBetween(1000, 9999),
            'national_id' => $this->faker->unique()->numerify('##########'),
            'is_active' => true,
            'last_login_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'preferences' => [
                'language' => $this->faker->randomElement(['ar', 'en']),
                'theme' => 'light',
                'notifications' => true
            ],
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}