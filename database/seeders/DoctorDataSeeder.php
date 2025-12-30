<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class DoctorDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create medical department
        $medicalDepartment = Department::firstOrCreate([
            'code' => 'MED'
        ], [
            'name_ar' => 'الطب العام',
            'name_en' => 'General Medicine',
            'description_ar' => 'قسم الطب العام',
            'is_active' => true
        ]);

        // Create doctor user
        $doctorExists = User::where('email', 'doctor@hospital-hms.com')->first();
        if (!$doctorExists) {
            $doctorUser = new User();
            $doctorUser->name = 'د. أحمد محمد';
            $doctorUser->email = 'doctor@hospital-hms.com';
            $doctorUser->password = Hash::make('password123');
            $doctorUser->phone = '+966501234569';
            $doctorUser->department_id = $medicalDepartment->id;
            $doctorUser->is_active = true;
            $doctorUser->email_verified_at = now();
            $doctorUser->employee_id = 'DOC001';
            $doctorUser->job_title = 'طبيب عام';
            $doctorUser->hire_date = now();
            $doctorUser->date_of_birth = '1980-05-15';
            $doctorUser->gender = 'male';
            
            // Set national_id directly without using the mutator
            $doctorUser->setRawAttributes(array_merge($doctorUser->getAttributes(), [
                'national_id' => '1234567891'
            ]));
            
            $doctorUser->address = 'الرياض، المملكة العربية السعودية';
            $doctorUser->save();

            // Assign doctor role
            $doctorUser->assignRole('doctor');
        } else {
            $doctorUser = $doctorExists;
        }

        // Create doctor profile
        $doctor = Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'doctor_number' => 'DR000001',
                'national_id' => '1234567891',
                'license_number' => 'LIC001',
                'specialization' => 'internal_medicine',
                'degree' => 'bachelor',
                'university' => 'جامعة الملك سعود',
                'experience_years' => 10,
                'languages' => ['ar', 'en'],
                'biography' => 'طبيب متخصص في الطب الباطني مع خبرة 10 سنوات',
                'working_hours' => [
                    'sunday' => ['is_working' => true, 'start' => '08:00', 'end' => '16:00'],
                    'monday' => ['is_working' => true, 'start' => '08:00', 'end' => '16:00'],
                    'tuesday' => ['is_working' => true, 'start' => '08:00', 'end' => '16:00'],
                    'wednesday' => ['is_working' => true, 'start' => '08:00', 'end' => '16:00'],
                    'thursday' => ['is_working' => true, 'start' => '08:00', 'end' => '16:00'],
                    'friday' => ['is_working' => false, 'start' => null, 'end' => null],
                    'saturday' => ['is_working' => false, 'start' => null, 'end' => null]
                ],
                'consultation_fee' => 200.00,
                'follow_up_fee' => 150.00,
                'room_number' => '101',
                'phone' => '+966501234569',
                'email' => 'doctor@hospital-hms.com',
                'is_available' => true,
                'is_active' => true,
                'rating' => 4.5,
                'total_reviews' => 0
            ]
        );

        $this->command->info('Doctor user and profile created successfully!');
        $this->command->info('Doctor Email: doctor@hospital-hms.com');
        $this->command->info('Doctor Password: password123');
    }
}