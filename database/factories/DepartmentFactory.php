<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $departments = [
            'الطوارئ',
            'الباطنة',
            'الجراحة',
            'النساء والولادة',
            'الأطفال',
            'العظام',
            'القلب',
            'الأعصاب',
            'العيون',
            'الأنف والأذن والحنجرة',
            'الجلدية',
            'المختبر',
            'الأشعة',
            'الصيدلية',
            'الاستقبال'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($departments),
            'name_en' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'head_id' => null, // Will be set later if needed
            'location' => $this->faker->randomElement(['الطابق الأول', 'الطابق الثاني', 'الطابق الثالث']),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'is_active' => true,
            'working_hours' => [
                'start' => '08:00',
                'end' => '16:00',
                'days' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday']
            ]
        ];
    }
}