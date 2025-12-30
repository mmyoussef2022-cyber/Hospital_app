<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\InsuranceCompany;
use App\Models\LabTest;
use App\Models\LabOrder;
use App\Models\RadiologyStudy;
use App\Models\RadiologyOrder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ComprehensiveDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('بدء إنشاء البيانات التجريبية الشاملة...');

        // Create departments if not exist
        $this->createDepartments();
        
        // Create sample doctors
        $this->createDoctors();
        
        // Create sample patients
        $this->createPatients();
        
        // Create sample appointments
        $this->createAppointments();
        
        // Create sample invoices and payments
        $this->createInvoicesAndPayments();

        $this->command->info('تم إنشاء جميع البيانات التجريبية بنجاح!');
    }

    private function createDepartments()
    {
        $departments = [
            ['name_ar' => 'الطب الباطني', 'name_en' => 'Internal Medicine', 'code' => 'INT'],
            ['name_ar' => 'الجراحة العامة', 'name_en' => 'General Surgery', 'code' => 'SUR'],
            ['name_ar' => 'طب الأطفال', 'name_en' => 'Pediatrics', 'code' => 'PED'],
            ['name_ar' => 'النساء والولادة', 'name_en' => 'Obstetrics & Gynecology', 'code' => 'OBG'],
            ['name_ar' => 'طب الأسنان', 'name_en' => 'Dentistry', 'code' => 'DEN'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept + ['is_active' => true]);
        }
    }

    private function createDoctors()
    {
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $departments = Department::all();

        $doctors = [
            ['name' => 'د. أحمد محمد علي', 'email' => 'ahmed.doctor@hospital.com', 'dept' => 'INT'],
            ['name' => 'د. فاطمة أحمد السالم', 'email' => 'fatima.doctor@hospital.com', 'dept' => 'PED'],
            ['name' => 'د. محمد عبدالله الأحمد', 'email' => 'mohammed.doctor@hospital.com', 'dept' => 'SUR'],
            ['name' => 'د. نورا سعد المطيري', 'email' => 'nora.doctor@hospital.com', 'dept' => 'OBG'],
            ['name' => 'د. خالد عبدالرحمن القحطاني', 'email' => 'khalid.doctor@hospital.com', 'dept' => 'DEN'],
        ];

        foreach ($doctors as $doctorData) {
            $department = $departments->where('code', $doctorData['dept'])->first();
            
            // Check if doctor already exists
            $existingDoctor = User::where('email', $doctorData['email'])->first();
            if ($existingDoctor) {
                continue;
            }
            
            $doctor = new User([
                'name' => $doctorData['name'],
                'email' => $doctorData['email'],
                'password' => Hash::make('password123'),
                'phone' => '+966' . rand(500000000, 599999999),
                'department_id' => $department->id,
                'is_active' => true,
                'email_verified_at' => now(),
                'employee_id' => 'DOC' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'job_title' => 'طبيب',
                'hire_date' => now()->subMonths(rand(6, 24)),
                'date_of_birth' => now()->subYears(rand(30, 50))->format('Y-m-d'),
                'gender' => rand(0, 1) ? 'male' : 'female',
                'address' => 'الرياض، المملكة العربية السعودية',
            ]);

            // Set national_id directly to avoid encryption issues
            $doctor->setRawAttributes(array_merge($doctor->getAttributes(), [
                'national_id' => '10' . rand(10000000, 99999999)
            ]));
            $doctor->save();

            $doctor->assignRole($doctorRole);
        }
    }
    private function createPatients()
    {
        $patients = [
            ['name' => 'أحمد محمد الأحمد', 'phone' => '+966501234567', 'gender' => 'male'],
            ['name' => 'فاطمة علي السالم', 'phone' => '+966501234568', 'gender' => 'female'],
            ['name' => 'محمد عبدالله القحطاني', 'phone' => '+966501234569', 'gender' => 'male'],
            ['name' => 'نورا سعد المطيري', 'phone' => '+966501234570', 'gender' => 'female'],
            ['name' => 'خالد عبدالرحمن الأحمد', 'phone' => '+966501234571', 'gender' => 'male'],
            ['name' => 'سارة محمد العتيبي', 'phone' => '+966501234572', 'gender' => 'female'],
            ['name' => 'عبدالله أحمد الشهري', 'phone' => '+966501234573', 'gender' => 'male'],
            ['name' => 'مريم علي الدوسري', 'phone' => '+966501234574', 'gender' => 'female'],
            ['name' => 'يوسف محمد الغامدي', 'phone' => '+966501234575', 'gender' => 'male'],
            ['name' => 'هند عبدالله الحربي', 'phone' => '+966501234576', 'gender' => 'female'],
        ];

        foreach ($patients as $patientData) {
            // Check if patient already exists
            $existingPatient = Patient::where('phone', $patientData['phone'])->first();
            if ($existingPatient) {
                continue;
            }
            
            $patient = new Patient([
                'name' => $patientData['name'],
                'phone' => $patientData['phone'],
                'email' => strtolower(str_replace(' ', '.', $patientData['name'])) . '@example.com',
                'date_of_birth' => now()->subYears(rand(20, 60))->format('Y-m-d'),
                'gender' => $patientData['gender'],
                'address' => 'الرياض، المملكة العربية السعودية',
                'emergency_contact' => 'جهة الاتصال الطارئة - +966' . rand(500000000, 599999999),
                'blood_type' => ['A+', 'B+', 'AB+', 'O+', 'A-', 'B-', 'AB-', 'O-'][rand(0, 7)],
                'is_active' => true,
                'nationality' => 'سعودي',
                'city' => 'الرياض',
                'country' => 'المملكة العربية السعودية',
            ]);

            // Set national_id directly to avoid encryption issues
            $patient->setRawAttributes(array_merge($patient->getAttributes(), [
                'national_id' => '20' . rand(10000000, 99999999)
            ]));
            $patient->save();
        }
    }

    private function createAppointments()
    {
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->get();
        
        $patients = Patient::all();

        if ($doctors->count() == 0 || $patients->count() == 0) {
            $this->command->warn('لا توجد أطباء أو مرضى لإنشاء المواعيد');
            return;
        }

        // Create appointments for the last 30 days and next 30 days
        for ($i = -30; $i <= 30; $i++) {
            $date = now()->addDays($i);
            
            // Create 2-5 appointments per day
            $appointmentsCount = rand(2, 5);
            
            for ($j = 0; $j < $appointmentsCount; $j++) {
                $doctor = $doctors->random();
                $patient = $patients->random();
                
                $appointmentTime = $date->copy()->setTime(rand(8, 17), [0, 15, 30, 45][rand(0, 3)]);
                
                // Check if appointment already exists for this doctor at this time
                $existingAppointment = Appointment::where('doctor_id', $doctor->id)
                    ->where('appointment_date', $appointmentTime->format('Y-m-d'))
                    ->where('appointment_time', $appointmentTime->format('H:i:s'))
                    ->first();
                
                if ($existingAppointment) {
                    continue; // Skip this appointment
                }
                
                Appointment::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_date' => $appointmentTime->format('Y-m-d'),
                    'appointment_time' => $appointmentTime->format('H:i:s'),
                    'type' => ['consultation', 'follow_up', 'emergency'][rand(0, 2)],
                    'status' => $i < 0 ? ['completed', 'cancelled'][rand(0, 1)] : 'scheduled',
                    'notes' => 'موعد تجريبي',
                ]);
            }
        }
    }

    private function createInvoicesAndPayments()
    {
        $patients = Patient::all();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->get();
        $insuranceCompanies = InsuranceCompany::all();
        $appointments = Appointment::where('status', 'completed')->get();

        if ($patients->count() == 0 || $doctors->count() == 0) {
            $this->command->warn('لا توجد مرضى أو أطباء لإنشاء الفواتير');
            return;
        }

        // Create 50 sample invoices
        for ($i = 0; $i < 50; $i++) {
            $patient = $patients->random();
            $doctor = $doctors->random();
            $appointment = $appointments->count() > 0 ? $appointments->random() : null;
            
            $invoiceDate = now()->subDays(rand(0, 90));
            $type = ['cash', 'credit', 'insurance'][rand(0, 2)];
            
            // Generate unique invoice number
            $invoiceNumber = 'INV-' . now()->format('Ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceNumber = 'INV-' . now()->format('Ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'type' => $type,
                'status' => ['draft', 'pending', 'paid', 'partially_paid', 'overdue'][rand(0, 4)],
                'patient_id' => $patient->id,
                'doctor_id' => null, // Set to null for now since foreign key points to doctors table
                'appointment_id' => $appointment ? $appointment->id : null,
                'insurance_company_id' => $type == 'insurance' && $insuranceCompanies->count() > 0 ? $insuranceCompanies->random()->id : null,
                'insurance_policy_number' => $type == 'insurance' ? 'POL-' . rand(100000, 999999) : null,
                'insurance_coverage_percentage' => $type == 'insurance' ? rand(70, 90) : 0,
                'invoice_date' => $invoiceDate->format('Y-m-d'),
                'due_date' => $type == 'credit' ? $invoiceDate->addDays(30)->format('Y-m-d') : null,
                'notes' => 'فاتورة تجريبية',
                'created_by' => 1,
            ]);

            // Add invoice items
            $itemsCount = rand(1, 4);
            for ($j = 0; $j < $itemsCount; $j++) {
                $services = [
                    ['name' => 'استشارة طبية', 'price' => rand(100, 300)],
                    ['name' => 'فحص مختبري', 'price' => rand(50, 150)],
                    ['name' => 'فحص أشعة', 'price' => rand(200, 500)],
                    ['name' => 'دواء', 'price' => rand(30, 200)],
                    ['name' => 'عملية صغرى', 'price' => rand(500, 1500)],
                ];
                
                $service = $services[rand(0, count($services) - 1)];
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'service',
                    'item_name' => $service['name'],
                    'item_description' => 'خدمة طبية تجريبية',
                    'itemable_type' => 'App\\Models\\Service', // Add required field
                    'itemable_id' => 1, // Use dummy ID
                    'unit_price' => $service['price'],
                    'quantity' => 1,
                    'tax_percentage' => 15,
                    'discount_percentage' => rand(0, 10),
                    'covered_by_insurance' => $type == 'insurance',
                    'insurance_coverage_percentage' => $type == 'insurance' ? rand(70, 90) : 0,
                ]);
            }

            // Recalculate totals
            $invoice->calculateTotals();
            $invoice->save();

            // Create payments for some invoices
            if (in_array($invoice->status, ['paid', 'partially_paid']) && rand(0, 1)) {
                $paymentAmount = $invoice->status == 'paid' ? $invoice->total_amount : rand(50, $invoice->total_amount - 50);
                
                Payment::create([
                    'payment_number' => 'PAY-' . now()->format('Ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'invoice_id' => $invoice->id,
                    'patient_id' => $invoice->patient_id,
                    'payment_method' => ['cash', 'card', 'bank_transfer'][rand(0, 2)],
                    'status' => 'completed',
                    'amount' => $paymentAmount,
                    'payment_date' => $invoiceDate->addDays(rand(0, 10)),
                    'processed_by' => 1,
                ]);

                $invoice->updatePaymentStatus();
            }
        }
    }
}