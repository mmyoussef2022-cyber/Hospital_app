<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $appointmentDate = $this->faker->dateTimeBetween('now', '+30 days');
        $appointmentTime = $this->faker->time('H:i', '18:00');
        
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => User::factory()->doctor(),
            'appointment_date' => $appointmentDate->format('Y-m-d'),
            'appointment_time' => $appointmentTime,
            'duration' => $this->faker->randomElement([30, 45, 60, 90]),
            'type' => $this->faker->randomElement(['consultation', 'follow_up', 'emergency', 'surgery']),
            'status' => $this->faker->randomElement(['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show']),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'reminder_sent_at' => $this->faker->optional(0.2)->dateTimeBetween('-7 days', 'now'),
        ];
    }

    /**
     * Indicate that the appointment is scheduled for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_date' => today()->format('Y-m-d'),
            'appointment_time' => $this->faker->time('H:i', '18:00'),
        ]);
    }

    /**
     * Indicate that the appointment is scheduled for tomorrow.
     */
    public function tomorrow(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_date' => tomorrow()->format('Y-m-d'),
            'appointment_time' => $this->faker->time('H:i', '18:00'),
        ]);
    }

    /**
     * Indicate that the appointment is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
        ]);
    }

    /**
     * Indicate that the appointment is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Indicate that the appointment is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * Indicate that the appointment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the appointment is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the appointment is a consultation.
     */
    public function consultation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'consultation',
            'duration' => 30,
        ]);
    }

    /**
     * Indicate that the appointment is a follow-up.
     */
    public function followUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'follow_up',
            'duration' => 30,
        ]);
    }

    /**
     * Indicate that the appointment is an emergency.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'emergency',
            'duration' => 60,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Indicate that the appointment is for surgery.
     */
    public function surgery(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'surgery',
            'duration' => 120,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Create appointment with specific doctor and patient.
     */
    public function forDoctorAndPatient($doctorId, $patientId): static
    {
        return $this->state(fn (array $attributes) => [
            'doctor_id' => $doctorId,
            'patient_id' => $patientId,
        ]);
    }

    /**
     * Create appointment at specific date and time.
     */
    public function at($date, $time): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_date' => Carbon::parse($date)->format('Y-m-d'),
            'appointment_time' => $time,
        ]);
    }
}
