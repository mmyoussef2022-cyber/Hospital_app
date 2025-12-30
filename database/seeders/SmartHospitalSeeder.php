<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Lab;
use App\Models\Radiology;
use App\Models\InsuranceCompany;
use App\Models\PatientInsurance;
use Carbon\Carbon;

class SmartHospitalSeeder extends Seeder
{
    private $departments = [];
    private $doctors = [];
    private $patients = [];
    private $insuranceCompanies = [];

    public function run()
    {
        $this->command->info('🏥 بدء تغذية قاعدة البيانات الذكية...');
        
        DB::beginTransaction();
        
        try {
            // 1. إنشاء الأقسام
            $this->createDepartments();
            
            // 2. إنشاء شركات التأمين
            $this->createInsuranceCompanies();
            
            // 3. إنشاء الأطباء (وربطهم بالمستخدمين الموجودين)
            $this->createDoctors();
            
            // 4. إنشاء المرضى
            $this->createPatients();
            
            // 5. إنشاء المواعيد
            $this->createAppointments();
            
            // 6. إنشاء السجلات الطبية
            $this->createMedicalRecords();
            
            // 7. إنشاء الوصفات الطبية
            $this->createPrescriptions();
            
            // 8. إنشاء طلبات التحاليل
            $this->createLabOrders();
            
            // 9. إنشاء طلبات الأشعة
            $this->createRadiologyOrders();
            
            DB::commit();
            
            $this->command->info('✅ تم إنشاء قاعدة بيانات متكاملة بنجاح!');
            $this->printSummary();
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('❌ خطأ في تغذية قاعدة البيانات: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createDepartments()
    {
        $this->command->info('📋 إنشاء الأقسام...');
        
        $departmentNames = [
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
            'الأسنان'
        ];

        foreach ($departmentNames as $index => $name) {
            $department = Department::firstOrCreate([
                'name_ar' => $name,
                'name_en' => 'Department ' . ($index + 1),
                'code' => 'DEPT' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'description_ar' => 'قسم ' . $name,
                'description_en' => 'Department of ' . $name,
                'is_active' => true
            ]);
            $this->departments[] = $department;
        }
    }

    private function createInsuranceCompanies()
    {
        $this->command->info('🏢 إنشاء شركات التأمين...');
        
        $companies = [
            ['name' => 'التأمين الطبي الشامل', 'coverage' => 80],
            ['name' => 'شركة الرعاية الصحية', 'coverage' => 75],
            ['name' => 'التأمين الوطني', 'coverage' => 90],
            ['name' => 'شركة الحياة للتأمين', 'coverage' => 70],
            ['name' => 'التأمين التعاوني', 'coverage' => 85]
        ];

        foreach ($companies as $index => $companyData) {
            $company = InsuranceCompany::firstOrCreate([
                'name_ar' => $companyData['name'],
                'name_en' => 'Insurance Company ' . ($index + 1),
                'code' => 'INS' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'phone' => '0501234567',
                'email' => 'info@insurance' . ($index + 1) . '.com',
                'address_ar' => 'الرياض، المملكة العربية السعودية',
                'address_en' => 'Riyadh, Saudi Arabia',
                'default_coverage_percentage' => $companyData['coverage'],
                'contract_status' => 'active',
                'is_active' => true
            ]);
            $this->insuranceCompanies[] = $company;
        }
    }

    private function createDoctors()
    {
        $this->command->info('👨‍⚕️ إنشاء ملفات الأطباء...');
        
        // أولاً: إنشاء ملف طبيب للمستخدم الحالي
        $currentUser = User::find(2); // المستخدم الحالي
        if ($currentUser && !$currentUser->doctor) {
            $doctor = Doctor::create([
                'user_id' => $currentUser->id,
                'national_id' => '1234567890',
                'license_number' => 'DOC-' . str_pad(1, 6, '0', STR_PAD_LEFT),
                'specialization' => 'طب عام',
                'degree' => 'بكالوريوس',
                'university' => 'جامعة الملك سعود',
                'experience_years' => 10,
                'consultation_fee' => 200,
                'is_available' => true,
                'working_hours' => json_encode([
                    'saturday' => ['09:00', '17:00'],
                    'sunday' => ['09:00', '17:00'],
                    'monday' => ['09:00', '17:00'],
                    'tuesday' => ['09:00', '17:00'],
                    'wednesday' => ['09:00', '17:00'],
                    'thursday' => ['09:00', '13:00']
                ]),
                'biography' => 'طبيب متخصص في الطب العام مع خبرة واسعة في التشخيص والعلاج'
            ]);
            $this->doctors[] = $doctor;
            $this->command->info("✅ تم إنشاء ملف طبيب للمستخدم: {$currentUser->name}");
        }

        // إنشاء أطباء إضافيين
        $specializations = [
            'طب الأطفال', 'أمراض القلب', 'الجراحة العامة', 'النساء والولادة',
            'طب العيون', 'الأنف والأذن والحنجرة', 'العظام', 'الأمراض الجلدية',
            'الطب النفسي', 'طب الأعصاب'
        ];

        $doctorNames = [
            'د. أحمد محمد', 'د. فاطمة علي', 'د. محمد سعد', 'د. نورا أحمد',
            'د. خالد عبدالله', 'د. سارة محمود', 'د. عبدالرحمن يوسف', 'د. مريم حسن',
            'د. طارق عبدالعزيز', 'د. هند الزهراني'
        ];

        for ($i = 0; $i < 10; $i++) {
            // إنشاء مستخدم جديد للطبيب
            $user = User::create([
                'name' => $doctorNames[$i],
                'email' => 'doctor' . ($i + 1) . '@hospital.com',
                'password' => Hash::make('password'),
                'national_id' => '223456789' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'phone' => '050123456' . $i,
                'gender' => $i % 2 == 0 ? 'male' : 'female',
                'is_active' => true,
                'department_id' => $this->departments[array_rand($this->departments)]->id
            ]);

            $doctor = Doctor::create([
                'user_id' => $user->id,
                'national_id' => '123456789' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'license_number' => 'DOC-' . str_pad($i + 2, 6, '0', STR_PAD_LEFT),
                'specialization' => $specializations[$i],
                'degree' => 'بكالوريوس',
                'university' => 'جامعة الملك سعود',
                'experience_years' => rand(3, 25),
                'consultation_fee' => rand(150, 500),
                'is_available' => true,
                'working_hours' => json_encode([
                    'saturday' => ['08:00', '16:00'],
                    'sunday' => ['08:00', '16:00'],
                    'monday' => ['08:00', '16:00'],
                    'tuesday' => ['08:00', '16:00'],
                    'wednesday' => ['08:00', '16:00']
                ]),
                'biography' => 'طبيب متخصص مع خبرة في مجال ' . $specializations[$i]
            ]);
            
            $this->doctors[] = $doctor;
        }
    }

    private function createPatients()
    {
        $this->command->info('🏥 إنشاء المرضى...');
        
        $patientNames = [
            'أحمد محمد علي', 'فاطمة عبدالله', 'محمد سعد أحمد', 'نورا حسن',
            'خالد عبدالرحمن', 'سارة محمود', 'عبدالله يوسف', 'مريم أحمد',
            'طارق عبدالعزيز', 'هند الزهراني', 'سعد محمد', 'أمل علي',
            'عبدالرحمن سالم', 'زينب حسام', 'ماجد عبدالله', 'رنا محمد'
        ];
        
        for ($i = 0; $i < 50; $i++) {
            $hasInsurance = ($i % 3 != 0); // 70% لديهم تأمين تقريباً
            $nameIndex = $i % count($patientNames);
            
            $patient = Patient::create([
                'name' => $patientNames[$nameIndex] . ' ' . ($i + 1),
                'national_id' => '1' . str_pad($i + 1, 9, '0', STR_PAD_LEFT),
                'phone' => '0501234' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email' => 'patient' . ($i + 1) . '@email.com',
                'gender' => $i % 2 == 0 ? 'male' : 'female',
                'date_of_birth' => Carbon::now()->subYears(rand(1, 80)),
                'address' => 'الرياض، المملكة العربية السعودية',
                'emergency_contact' => json_encode([
                    'name' => 'جهة الاتصال الطارئ',
                    'phone' => '0501234567',
                    'relationship' => 'أقارب'
                ]),
                'allergies' => $i % 5 == 0 ? ['البنسلين'] : null,
                'chronic_conditions' => $i % 7 == 0 ? ['السكري'] : null,
                'blood_type' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'][$i % 8],
                'is_active' => true
            ]);

            // إنشاء بوليصة تأمين إذا كان المريض مؤمن
            // تم تعطيل إنشاء التأمين مؤقتاً لتبسيط البيانات
            // if ($hasInsurance && !empty($this->insuranceCompanies)) {
            //     PatientInsurance::create([...]);
            // }
            
            $this->patients[] = $patient;
        }
    }

    private function createAppointments()
    {
        $this->command->info('📅 إنشاء المواعيد...');
        
        $appointmentTypes = ['A', 'B', 'C', 'D'];
        $statuses = ['scheduled', 'confirmed', 'completed', 'cancelled'];
        
        // مواعيد الأسبوع الماضي والحالي والقادم
        for ($i = 0; $i < 100; $i++) {
            $daysOffset = rand(-7, 14); // من أسبوع مضى إلى أسبوعين قادمين
            $appointmentDate = Carbon::now()->addDays($daysOffset);
            $doctor = $this->doctors[array_rand($this->doctors)];
            $patient = $this->patients[array_rand($this->patients)];
            
            // إنشاء وقت فريد للموعد
            $hour = rand(8, 16);
            $minute = [0, 15, 30, 45][rand(0, 3)];
            $uniqueTime = $appointmentDate->copy()->setTime($hour, $minute)->addMinutes($i); // إضافة دقائق فريدة
            
            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->user_id, // استخدام user_id من جدول الأطباء
                'appointment_date' => $uniqueTime->toDateString(),
                'appointment_time' => $uniqueTime,
                'status' => $statuses[array_rand($statuses)],
                'notes' => 'ملاحظات الموعد',
                'duration' => rand(15, 60)
            ]);
        }
    }

    private function createMedicalRecords()
    {
        $this->command->info('📋 إنشاء السجلات الطبية...');
        
        $completedAppointments = Appointment::where('status', 'completed')->get();
        
        $complaints = [
            'ألم في الصدر', 'صداع مستمر', 'حمى وإرهاق', 'ألم في البطن',
            'ضيق في التنفس', 'دوخة ودوار', 'ألم في المفاصل'
        ];
        
        $diagnoses = [
            'التهاب الجهاز التنفسي العلوي', 'ارتفاع ضغط الدم', 'التهاب المعدة',
            'الصداع النصفي', 'التهاب المفاصل', 'القلق والتوتر'
        ];
        
        foreach ($completedAppointments as $appointment) {
            MedicalRecord::create([
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id, // هذا صحيح لأن appointment.doctor_id يشير إلى users.id
                'visit_date' => $appointment->appointment_date,
                'chief_complaint' => $complaints[array_rand($complaints)],
                'diagnosis' => [$diagnoses[array_rand($diagnoses)]],
                'treatment' => 'خطة العلاج المناسبة',
                'vital_signs' => [
                    'temperature' => 37.2,
                    'blood_pressure_systolic' => 120,
                    'blood_pressure_diastolic' => 80,
                    'heart_rate' => 75,
                    'respiratory_rate' => 16
                ],
                'notes' => 'ملاحظات إضافية',
                'visit_type' => 'consultation',
                'status' => 'completed'
            ]);
        }
    }

    private function createPrescriptions()
    {
        $this->command->info('💊 إنشاء الوصفات الطبية...');
        
        $medicalRecords = MedicalRecord::all();
        $medications = [
            'باراسيتامول', 'إيبوبروفين', 'أموكسيسيلين', 'أزيثروميسين',
            'أوميبرازول', 'لوسارتان', 'ميتفورمين', 'أتورفاستاتين'
        ];
        
        $frequencies = ['مرة واحدة يومياً', 'مرتين يومياً', '3 مرات يومياً'];
        $instructions = ['بعد الأكل', 'قبل الأكل', 'مع الماء', 'عند الحاجة'];
        
        foreach ($medicalRecords->take(60) as $record) {
            // إنشاء وصفة طبية منفصلة لكل دواء
            for ($i = 0; $i < rand(1, 4); $i++) {
                Prescription::create([
                    'patient_id' => $record->patient_id,
                    'doctor_id' => $record->doctor_id,
                    'medical_record_id' => $record->id,
                    'medication_name' => $medications[array_rand($medications)],
                    'dosage' => rand(250, 1000) . ' مجم',
                    'frequency' => $frequencies[array_rand($frequencies)],
                    'duration_days' => rand(3, 14),
                    'instructions' => $instructions[array_rand($instructions)],
                    'start_date' => $record->visit_date,
                    'end_date' => Carbon::parse($record->visit_date)->addDays(rand(3, 14)),
                    'status' => 'active'
                ]);
            }
        }
    }

    private function createLabOrders()
    {
        $this->command->info('🧪 إنشاء طلبات التحاليل...');
        
        $medicalRecords = MedicalRecord::all();
        $priorities = ['routine', 'urgent', 'stat'];
        $statuses = ['ordered', 'collected', 'completed'];
        
        foreach ($medicalRecords->take(40) as $record) {
            $status = $statuses[array_rand($statuses)];
            $completedAt = $status === 'completed' ? 
                Carbon::parse($record->visit_date)->addDays(rand(1, 3)) : null;
            
            Lab::create([
                'patient_id' => $record->patient_id,
                'doctor_id' => $record->doctor_id, // هذا صحيح لأن medical_record.doctor_id يشير إلى users.id
                'medical_record_id' => $record->id,
                'test_ids' => json_encode([1, 2, 3]),
                'priority' => $priorities[array_rand($priorities)],
                'clinical_notes' => 'ملاحظات سريرية',
                'fasting_required' => rand(0, 1) == 1,
                'collection_date' => $record->visit_date,
                'order_date' => $record->visit_date,
                'completed_at' => $completedAt,
                'status' => $status,
                'results' => $status === 'completed' ? $this->generateLabResults() : null,
                'is_critical' => rand(0, 9) == 0 // 10% احتمال أن تكون حرجة
            ]);
        }
    }

    private function createRadiologyOrders()
    {
        $this->command->info('📡 إنشاء طلبات الأشعة...');
        
        $medicalRecords = MedicalRecord::all();
        $radiologyStudies = [
            'أشعة سينية على الصدر', 'أشعة مقطعية على البطن', 'رنين مغناطيسي على الدماغ',
            'أشعة سينية على العظام', 'أشعة بالموجات فوق الصوتية'
        ];
        
        $priorities = ['routine', 'urgent', 'stat'];
        $statuses = ['ordered', 'scheduled', 'completed'];
        
        foreach ($medicalRecords->take(30) as $record) {
            $status = $statuses[array_rand($statuses)];
            $completedAt = $status === 'completed' ? 
                Carbon::parse($record->visit_date)->addDays(rand(1, 5)) : null;
            
            Radiology::create([
                'patient_id' => $record->patient_id,
                'doctor_id' => $record->doctor_id, // هذا صحيح لأن medical_record.doctor_id يشير إلى users.id
                'medical_record_id' => $record->id,
                'study_type' => $radiologyStudies[array_rand($radiologyStudies)],
                'priority' => $priorities[array_rand($priorities)],
                'clinical_indication' => 'دواعي سريرية للفحص',
                'contrast_required' => rand(0, 4) == 0, // 20% احتمال
                'preparation_instructions' => 'تعليمات التحضير',
                'scheduled_date' => Carbon::parse($record->visit_date)->addDays(rand(1, 7)),
                'order_date' => $record->visit_date,
                'completed_at' => $completedAt,
                'status' => $status,
                'report' => $status === 'completed' ? $this->generateRadiologyReport() : null,
                'is_critical' => rand(0, 19) == 0 // 5% احتمال أن تكون حرجة
            ]);
        }
    }

    private function generateLabResults()
    {
        return "نتائج التحاليل:\n" .
               "- تحليل الدم الشامل: طبيعي\n" .
               "- مستوى السكر: " . rand(80, 120) . " مجم/ديسيلتر\n" .
               "- وظائف الكلى: طبيعية\n" .
               "- الكوليسترول: " . rand(150, 200) . " مجم/ديسيلتر";
    }

    private function generateRadiologyReport()
    {
        return "تقرير الأشعة:\n" .
               "الفحص يظهر بنية طبيعية للأعضاء المفحوصة.\n" .
               "لا توجد علامات على وجود التهابات أو تشوهات.\n" .
               "التوصية: المتابعة الدورية حسب الحاجة.";
    }

    private function printSummary()
    {
        $this->command->info("\n" . str_repeat('=', 50));
        $this->command->info('📊 ملخص البيانات المُنشأة:');
        $this->command->info(str_repeat('=', 50));
        
        $this->command->info('🏢 الأقسام: ' . Department::count());
        $this->command->info('🏥 شركات التأمين: ' . InsuranceCompany::count());
        $this->command->info('👨‍⚕️ الأطباء: ' . Doctor::count());
        $this->command->info('🏥 المرضى: ' . Patient::count());
        $this->command->info('📅 المواعيد: ' . Appointment::count());
        $this->command->info('📋 السجلات الطبية: ' . MedicalRecord::count());
        $this->command->info('💊 الوصفات الطبية: ' . Prescription::count());
        $this->command->info('🧪 طلبات التحاليل: ' . Lab::count());
        $this->command->info('📡 طلبات الأشعة: ' . Radiology::count());
        
        $this->command->info(str_repeat('=', 50));
        $this->command->info('✅ قاعدة البيانات جاهزة للاستخدام!');
        $this->command->info('🎯 يمكنك الآن تجربة جميع ميزات النظام');
    }
}