<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use Spatie\Permission\Models\Role;

class MedicalRecordsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create doctor role if not exists
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);

        // Get first user and assign doctor role
        $user = User::first();
        if ($user && !$user->hasRole('doctor')) {
            $user->assignRole('doctor');
            $this->command->info('Assigned doctor role to user: ' . $user->name);
        }

        // Get patients and doctors
        $patients = Patient::take(3)->get();
        $doctors = User::role('doctor')->get();

        if ($patients->count() > 0 && $doctors->count() > 0) {
            foreach ($patients as $patient) {
                $doctor = $doctors->random();
                
                // Create medical record
                $record = MedicalRecord::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'visit_date' => now()->subDays(rand(1, 30)),
                    'chief_complaint' => 'Patient complains of headache and fever',
                    'chief_complaint_ar' => 'يشكو المريض من صداع وحمى',
                    'diagnosis' => ['Viral infection', 'Headache'],
                    'diagnosis_ar' => ['عدوى فيروسية', 'صداع'],
                    'treatment' => 'Rest and medication',
                    'treatment_ar' => 'راحة وأدوية',
                    'visit_type' => ['consultation', 'follow_up', 'emergency'][rand(0, 2)],
                    'is_emergency' => rand(0, 1) == 1,
                    'notes' => 'Patient responded well to treatment',
                    'notes_ar' => 'استجاب المريض جيداً للعلاج'
                ]);

                $this->command->info('Created medical record ID: ' . $record->id . ' for patient: ' . $patient->name);

                // Create prescription for this record
                $prescription = Prescription::create([
                    'medical_record_id' => $record->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'medication_name' => 'Paracetamol 500mg',
                    'medication_name_ar' => 'باراسيتامول 500 مجم',
                    'dosage' => '500mg',
                    'frequency' => 'Three times daily',
                    'frequency_ar' => 'ثلاث مرات يومياً',
                    'duration_days' => 7,
                    'instructions' => 'Take after meals',
                    'instructions_ar' => 'يؤخذ بعد الوجبات',
                    'start_date' => now(),
                    'end_date' => now()->addDays(7),
                    'status' => 'active'
                ]);

                $this->command->info('Created prescription ID: ' . $prescription->id);
            }
        }
    }
}