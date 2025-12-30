<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->slug(2);
        
        return [
            'name' => $name,
            'name_ar' => $name . '_ar',
            'display_name' => $this->faker->jobTitle(),
            'display_name_ar' => $this->faker->jobTitle() . ' عربي',
            'description' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence() . ' بالعربية',
            'color' => $this->faker->hexColor(),
            'level' => $this->faker->numberBetween(1, 10),
            'is_active' => true
        ];
    }
}