<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some doctors and patients
        $doctors = User::role('doctor')->take(3)->get();
        $patients = Patient::take(10)->get();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            $this->command->info('No doctors or patients found. Please run UserSeeder and PatientSeeder first.');
            return;
        }

        // Create appointments for today
        foreach ($doctors as $doctor) {
            // Morning appointments
            Appointment::factory()
                ->forDoctorAndPatient($doctor->id, $patients->random()->id)
                ->at(today(), '09:00')
                ->scheduled()
                ->consultation()
                ->create();

            Appointment::factory()
                ->forDoctorAndPatient($doctor->id, $patients->random()->id)
                ->at(today(), '10:30')
                ->confirmed()
                ->followUp()
                ->create();

            // Afternoon appointments
            Appointment::factory()
                ->forDoctorAndPatient($doctor->id, $patients->random()->id)
                ->at(today(), '14:00')
                ->inProgress()
                ->consultation()
                ->create();

            Appointment::factory()
                ->forDoctorAndPatient($doctor->id, $patients->random()->id)
                ->at(today(), '15:30')
                ->completed()
                ->consultation()
                ->create();
        }

        // Create appointments for tomorrow
        foreach ($doctors as $doctor) {
            Appointment::factory()
                ->forDoctorAndPatient($doctor->id, $patients->random()->id)
                ->at(tomorrow(), '09:30')
                ->scheduled()
                ->consultation()
                ->create();

            Appointment::factory()
                ->forDoctorAndPatient($doctor->id, $patients->random()->id)
                ->at(tomorrow(), '11:00')
                ->scheduled()
                ->followUp()
                ->create();
        }

        // Create some appointments for next week
        $nextWeek = today()->addWeek();
        foreach ($doctors as $doctor) {
            for ($i = 0; $i < 5; $i++) {
                $date = $nextWeek->copy()->addDays($i);
                $times = ['09:00', '10:30', '14:00', '15:30', '16:30'];
                
                foreach ($times as $time) {
                    if (rand(1, 3) === 1) { // 33% chance to create appointment
                        Appointment::factory()
                            ->forDoctorAndPatient($doctor->id, $patients->random()->id)
                            ->at($date, $time)
                            ->scheduled()
                            ->create();
                    }
                }
            }
        }

        // Create some past appointments
        $lastWeek = today()->subWeek();
        foreach ($doctors as $doctor) {
            for ($i = 0; $i < 5; $i++) {
                $date = $lastWeek->copy()->addDays($i);
                
                Appointment::factory()
                    ->forDoctorAndPatient($doctor->id, $patients->random()->id)
                    ->at($date, '10:00')
                    ->completed()
                    ->create();

                Appointment::factory()
                    ->forDoctorAndPatient($doctor->id, $patients->random()->id)
                    ->at($date, '15:00')
                    ->completed()
                    ->create();
            }
        }

        $this->command->info('Created sample appointments successfully!');
    }
}
