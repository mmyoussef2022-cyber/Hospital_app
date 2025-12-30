<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => User::factory(),
            'visit_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'chief_complaint' => $this->faker->sentence(),
            'diagnosis' => [
                $this->faker->words(3, true),
                $this->faker->words(2, true)
            ],
            'treatment' => $this->faker->paragraph(),
            'medications' => [
                [
                    'name' => $this->faker->word(),
                    'dosage' => $this->faker->randomElement(['250mg', '500mg', '1g']),
                    'frequency' => $this->faker->randomElement(['مرة واحد يومياً', 'مرتين يومياً', 'ثلاث مرات يومياً']),
                    'duration' => $this->faker->randomElement(['3 أيام', '7 أيام', '14 يوم'])
                ]
            ],
            'vital_signs' => [
                'blood_pressure' => $this->faker->randomElement(['120/80', '130/85', '110/70']),
                'temperature' => $this->faker->randomFloat(1, 36.0, 39.0),
                'pulse' => $this->faker->numberBetween(60, 100),
                'weight' => $this->faker->randomFloat(1, 50.0, 120.0),
                'height' => $this->faker->numberBetween(150, 200)
            ],
            'notes' => $this->faker->optional()->paragraph(),
            'attachments' => []
        ];
    }
}