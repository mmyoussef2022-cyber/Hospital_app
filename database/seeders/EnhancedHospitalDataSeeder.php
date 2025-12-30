<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\InsuranceCompany;
use App\Models\InsurancePolicy;
use App\Models\PatientInsurance;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EnhancedHospitalDataSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('🏥 بدء إنشاء البيانات التجريبية الشاملة للمستشفى...');

        // إنشاء الأقسام الطبية
        $this->createDepartments();
        
        // إنشاء الأطباء في مختلف التخصصات
        $this->createDoctors();
        
        // إنشاء المرضى والأسر
        $this->createPatientsAndFamilies();
        
        // ربط المرضى بالتأمين
        $this->assignInsuranceToPatients();
        
        // إنشاء المواعيد
        $this->createAppointments();
        
        // إنشاء الفواتير والمدفوعات
        $this->createInvoicesAndPayments();

        $this->command->info('✅ تم إنشاء البيانات التجريبية الشاملة بنجاح!');
    }

    private function createDepartments(): void
    {
        $this->command->info('📋 إنشاء الأقسام الطبية...');

        $departments = [
            ['name_ar' => 'الطب الباطني', 'name_en' => 'Internal Medicine', 'code' => 'INT'],
            ['name_ar' => 'الجراحة العامة', 'name_en' => 'General Surgery', 'code' => 'SUR'],
            ['name_ar' => 'طب الأطفال', 'name_en' => 'Pediatrics', 'code' => 'PED'],
            ['name_ar' => 'النساء والولادة', 'name_en' => 'Obstetrics & Gynecology', 'code' => 'OBG'],
            ['name_ar' => 'طب الأسنان', 'name_en' => 'Dentistry', 'code' => 'DEN'],
            ['name_ar' => 'العظام', 'name_en' => 'Orthopedics', 'code' => 'ORT'],
            ['name_ar' => 'القلب والأوعية الدموية', 'name_en' => 'Cardiology', 'code' => 'CAR'],
            ['name_ar' => 'الأمراض الجلدية', 'name_en' => 'Dermatology', 'code' => 'DER'],
            ['name_ar' => 'العيون', 'name_en' => 'Ophthalmology', 'code' => 'OPH'],
            ['name_ar' => 'الأنف والأذن والحنجرة', 'name_en' => 'ENT', 'code' => 'ENT'],
            ['name_ar' => 'الطب النفسي', 'name_en' => 'Psychiatry', 'code' => 'PSY'],
            ['name_ar' => 'الأشعة', 'name_en' => 'Radiology', 'code' => 'RAD'],
            ['name_ar' => 'المختبر', 'name_en' => 'Laboratory', 'code' => 'LAB'],
            ['name_ar' => 'الطوارئ', 'name_en' => 'Emergency', 'code' => 'EMR'],
            ['name_ar' => 'التخدير', 'name_en' => 'Anesthesia', 'code' => 'ANE']
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        $this->command->info('✅ تم إنشاء ' . count($departments) . ' قسم طبي');
    }

    private function createDoctors(): void
    {
        $this->command->info('👨‍⚕️ إنشاء الأطباء في مختلف التخصصات...');

        $departments = Department::all();
        
        $doctors = [
            // الطب الباطني
            [
                'department' => 'الطب الباطني',
                'doctors' => [
                    ['name' => 'د. محمد أحمد السالم', 'email' => 'mohammed.salem@hospital.com', 'specialization' => 'أمراض الجهاز الهضمي'],
                    ['name' => 'د. فاطمة علي الأحمد', 'email' => 'fatima.ahmed@hospital.com', 'specialization' => 'أمراض الكلى'],
                    ['name' => 'د. عبدالله محمد القحطاني', 'email' => 'abdullah.qhtani@hospital.com', 'specialization' => 'أمراض الغدد الصماء']
                ]
            ],
            // الجراحة العامة
            [
                'department' => 'الجراحة العامة',
                'doctors' => [
                    ['name' => 'د. خالد عبدالرحمن المطيري', 'email' => 'khalid.mutairi@hospital.com', 'specialization' => 'جراحة الجهاز الهضمي'],
                    ['name' => 'د. نورا سعد الدوسري', 'email' => 'nora.dosari@hospital.com', 'specialization' => 'جراحة الثدي'],
                    ['name' => 'د. أحمد يوسف الشهري', 'email' => 'ahmed.shehri@hospital.com', 'specialization' => 'جراحة المناظير']
                ]
            ],
            // طب الأطفال
            [
                'department' => 'طب الأطفال',
                'doctors' => [
                    ['name' => 'د. سارة محمد العتيبي', 'email' => 'sara.otaibi@hospital.com', 'specialization' => 'طب الأطفال حديثي الولادة'],
                    ['name' => 'د. عمر عبدالله الحربي', 'email' => 'omar.harbi@hospital.com', 'specialization' => 'أمراض الأطفال المعدية'],
                    ['name' => 'د. هند علي الغامدي', 'email' => 'hind.ghamdi@hospital.com', 'specialization' => 'طب الأطفال التطوري']
                ]
            ],
            // النساء والولادة
            [
                'department' => 'النساء والولادة',
                'doctors' => [
                    ['name' => 'د. مريم أحمد الزهراني', 'email' => 'mariam.zahrani@hospital.com', 'specialization' => 'طب النساء والتوليد'],
                    ['name' => 'د. ليلى محمد السبيعي', 'email' => 'layla.subai@hospital.com', 'specialization' => 'جراحة النساء'],
                    ['name' => 'د. رنا عبدالله الشمري', 'email' => 'rana.shamri@hospital.com', 'specialization' => 'طب الأجنة']
                ]
            ],
            // طب الأسنان
            [
                'department' => 'طب الأسنان',
                'doctors' => [
                    ['name' => 'د. يوسف محمد الفيصل', 'email' => 'youssef.faisal@hospital.com', 'specialization' => 'جراحة الفم والأسنان'],
                    ['name' => 'د. أمل سعد القرني', 'email' => 'amal.qarni@hospital.com', 'specialization' => 'تقويم الأسنان'],
                    ['name' => 'د. ماجد عبدالرحمن البقمي', 'email' => 'majed.baqmi@hospital.com', 'specialization' => 'طب أسنان الأطفال']
                ]
            ],
            // العظام
            [
                'department' => 'العظام',
                'doctors' => [
                    ['name' => 'د. طارق أحمد الراشد', 'email' => 'tariq.rashed@hospital.com', 'specialization' => 'جراحة العمود الفقري'],
                    ['name' => 'د. منى علي الحكمي', 'email' => 'mona.hakmi@hospital.com', 'specialization' => 'طب الروماتيزم'],
                    ['name' => 'د. سلطان محمد العنزي', 'email' => 'sultan.anzi@hospital.com', 'specialization' => 'جراحة المفاصل']
                ]
            ],
            // القلب والأوعية الدموية
            [
                'department' => 'القلب والأوعية الدموية',
                'doctors' => [
                    ['name' => 'د. فهد عبدالله الدوسري', 'email' => 'fahd.dosari@hospital.com', 'specialization' => 'قسطرة القلب'],
                    ['name' => 'د. نوال سعد المالكي', 'email' => 'nawal.malki@hospital.com', 'specialization' => 'أمراض القلب'],
                    ['name' => 'د. بدر محمد الشهراني', 'email' => 'badr.shahrani@hospital.com', 'specialization' => 'جراحة القلب']
                ]
            ],
            // الأمراض الجلدية
            [
                'department' => 'الأمراض الجلدية',
                'doctors' => [
                    ['name' => 'د. ريم أحمد الخالدي', 'email' => 'reem.khalidi@hospital.com', 'specialization' => 'الأمراض الجلدية التجميلية'],
                    ['name' => 'د. وليد عبدالرحمن الجهني', 'email' => 'waleed.johani@hospital.com', 'specialization' => 'أمراض الجلد المناعية']
                ]
            ],
            // العيون
            [
                'department' => 'العيون',
                'doctors' => [
                    ['name' => 'د. عبدالعزيز محمد الفهد', 'email' => 'abdulaziz.fahd@hospital.com', 'specialization' => 'جراحة الشبكية'],
                    ['name' => 'د. هيفاء علي الحارثي', 'email' => 'haifa.harthi@hospital.com', 'specialization' => 'طب عيون الأطفال']
                ]
            ],
            // الأنف والأذن والحنجرة
            [
                'department' => 'الأنف والأذن والحنجرة',
                'doctors' => [
                    ['name' => 'د. سامي أحمد الغامدي', 'email' => 'sami.ghamdi@hospital.com', 'specialization' => 'جراحة الأنف والجيوب الأنفية'],
                    ['name' => 'د. دانا محمد الشريف', 'email' => 'dana.shareef@hospital.com', 'specialization' => 'أمراض السمع والتوازن']
                ]
            ]
        ];

        $doctorCount = 0;
        foreach ($doctors as $deptData) {
            $department = $departments->where('name_ar', $deptData['department'])->first();
            
            if (!$department) {
                $this->command->warn("القسم '{$deptData['department']}' غير موجود");
                continue;
            }

            foreach ($deptData['doctors'] as $doctorData) {
                // إنشاء المستخدم
                $user = User::create([
                    'name' => $doctorData['name'],
                    'email' => $doctorData['email'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'job_title' => 'طبيب',
                    'phone' => '+966' . rand(500000000, 599999999),
                    'national_id' => $this->generateNationalId(),
                    'is_active' => true
                ]);

                // تعيين دور الطبيب
                $user->assignRole('doctor');

                // إنشاء ملف الطبيب
                Doctor::create([
                    'user_id' => $user->id,
                    'doctor_number' => 'DR' . str_pad(Doctor::count() + 1, 6, '0', STR_PAD_LEFT),
                    'national_id' => $this->generateNationalId(),
                    'license_number' => 'LIC-' . strtoupper(Str::random(8)),
                    'specialization' => $doctorData['specialization'],
                    'degree' => 'بكالوريوس الطب والجراحة',
                    'university' => 'جامعة الملك سعود',
                    'experience_years' => rand(5, 20),
                    'languages' => ['العربية', 'الإنجليزية'],
                    'biography' => 'طبيب متخصص في ' . $doctorData['specialization'] . ' مع خبرة واسعة في التشخيص والعلاج',
                    'working_hours' => [
                        'sunday' => ['start' => '08:00', 'end' => '16:00'],
                        'monday' => ['start' => '08:00', 'end' => '16:00'],
                        'tuesday' => ['start' => '08:00', 'end' => '16:00'],
                        'wednesday' => ['start' => '08:00', 'end' => '16:00'],
                        'thursday' => ['start' => '08:00', 'end' => '16:00']
                    ],
                    'consultation_fee' => rand(200, 500),
                    'follow_up_fee' => rand(100, 300),
                    'room_number' => 'R' . rand(100, 999),
                    'phone' => '+966' . rand(500000000, 599999999),
                    'email' => $doctorData['email'],
                    'is_available' => true,
                    'is_active' => true
                ]);

                $doctorCount++;
                $this->command->info("✅ تم إنشاء الطبيب: {$doctorData['name']} - {$doctorData['specialization']}");
            }
        }

        $this->command->info("✅ تم إنشاء {$doctorCount} طبيب في مختلف التخصصات");
    }

    private function createPatientsAndFamilies(): void
    {
        $this->command->info('👥 إنشاء المرضى والأسر...');

        $families = [
            // العائلة الأولى - آل أحمد
            [
                'family_code' => 'FAM-001',
                'members' => [
                    [
                        'first_name' => 'محمد',
                        'last_name' => 'أحمد السالم',
                        'gender' => 'male',
                        'date_of_birth' => '1980-05-15',
                        'phone' => '+966501234567',
                        'email' => 'mohammed.ahmed@email.com',
                        'is_family_head' => true,
                        'relation' => 'رب الأسرة'
                    ],
                    [
                        'first_name' => 'فاطمة',
                        'last_name' => 'محمد الأحمد',
                        'gender' => 'female',
                        'date_of_birth' => '1985-08-22',
                        'phone' => '+966501234568',
                        'email' => 'fatima.ahmed@email.com',
                        'is_family_head' => false,
                        'relation' => 'الزوجة'
                    ],
                    [
                        'first_name' => 'أحمد',
                        'last_name' => 'محمد السالم',
                        'gender' => 'male',
                        'date_of_birth' => '2010-03-10',
                        'phone' => null,
                        'email' => null,
                        'is_family_head' => false,
                        'relation' => 'الابن'
                    ],
                    [
                        'first_name' => 'سارة',
                        'last_name' => 'محمد السالم',
                        'gender' => 'female',
                        'date_of_birth' => '2012-11-05',
                        'phone' => null,
                        'email' => null,
                        'is_family_head' => false,
                        'relation' => 'الابنة'
                    ]
                ]
            ],
            // العائلة الثانية - آل القحطاني
            [
                'family_code' => 'FAM-002',
                'members' => [
                    [
                        'first_name' => 'عبدالله',
                        'last_name' => 'سعد القحطاني',
                        'gender' => 'male',
                        'date_of_birth' => '1975-12-20',
                        'phone' => '+966502345678',
                        'email' => 'abdullah.qhtani@email.com',
                        'is_family_head' => true,
                        'relation' => 'رب الأسرة'
                    ],
                    [
                        'first_name' => 'نورا',
                        'last_name' => 'عبدالله القحطاني',
                        'gender' => 'female',
                        'date_of_birth' => '1982-07-18',
                        'phone' => '+966502345679',
                        'email' => 'nora.qhtani@email.com',
                        'is_family_head' => false,
                        'relation' => 'الزوجة'
                    ],
                    [
                        'first_name' => 'خالد',
                        'last_name' => 'عبدالله القحطاني',
                        'gender' => 'male',
                        'date_of_birth' => '2008-01-25',
                        'phone' => null,
                        'email' => null,
                        'is_family_head' => false,
                        'relation' => 'الابن'
                    ]
                ]
            ],
            // العائلة الثالثة - آل المطيري
            [
                'family_code' => 'FAM-003',
                'members' => [
                    [
                        'first_name' => 'سعد',
                        'last_name' => 'محمد المطيري',
                        'gender' => 'male',
                        'date_of_birth' => '1988-09-12',
                        'phone' => '+966503456789',
                        'email' => 'saad.mutairi@email.com',
                        'is_family_head' => true,
                        'relation' => 'رب الأسرة'
                    ],
                    [
                        'first_name' => 'هند',
                        'last_name' => 'سعد المطيري',
                        'gender' => 'female',
                        'date_of_birth' => '1992-04-08',
                        'phone' => '+966503456790',
                        'email' => 'hind.mutairi@email.com',
                        'is_family_head' => false,
                        'relation' => 'الزوجة'
                    ],
                    [
                        'first_name' => 'ريان',
                        'last_name' => 'سعد المطيري',
                        'gender' => 'male',
                        'date_of_birth' => '2018-06-15',
                        'phone' => null,
                        'email' => null,
                        'is_family_head' => false,
                        'relation' => 'الابن'
                    ]
                ]
            ],
            // العائلة الرابعة - آل الدوسري
            [
                'family_code' => 'FAM-004',
                'members' => [
                    [
                        'first_name' => 'علي',
                        'last_name' => 'أحمد الدوسري',
                        'gender' => 'male',
                        'date_of_birth' => '1970-11-30',
                        'phone' => '+966504567890',
                        'email' => 'ali.dosari@email.com',
                        'is_family_head' => true,
                        'relation' => 'رب الأسرة'
                    ],
                    [
                        'first_name' => 'مريم',
                        'last_name' => 'علي الدوسري',
                        'gender' => 'female',
                        'date_of_birth' => '1978-02-14',
                        'phone' => '+966504567891',
                        'email' => 'mariam.dosari@email.com',
                        'is_family_head' => false,
                        'relation' => 'الزوجة'
                    ],
                    [
                        'first_name' => 'يوسف',
                        'last_name' => 'علي الدوسري',
                        'gender' => 'male',
                        'date_of_birth' => '2005-10-20',
                        'phone' => null,
                        'email' => null,
                        'is_family_head' => false,
                        'relation' => 'الابن'
                    ],
                    [
                        'first_name' => 'لينا',
                        'last_name' => 'علي الدوسري',
                        'gender' => 'female',
                        'date_of_birth' => '2007-12-08',
                        'phone' => null,
                        'email' => null,
                        'is_family_head' => false,
                        'relation' => 'الابنة'
                    ],
                    [
                        'first_name' => 'زياد',
                        'last_name' => 'علي الدوسري',
                        'gender' => 'male',
                        'date_of_birth' => '2015-03-22',
                        'phone' => null,
                        'email' => null,
                        'is_family_head' => false,
                        'relation' => 'الابن'
                    ]
                ]
            ],
            // العائلة الخامسة - آل الغامدي
            [
                'family_code' => 'FAM-005',
                'members' => [
                    [
                        'first_name' => 'محمد',
                        'last_name' => 'عبدالرحمن الغامدي',
                        'gender' => 'male',
                        'date_of_birth' => '1983-06-25',
                        'phone' => '+966505678901',
                        'email' => 'mohammed.ghamdi@email.com',
                        'is_family_head' => true,
                        'relation' => 'رب الأسرة'
                    ],
                    [
                        'first_name' => 'أمل',
                        'last_name' => 'محمد الغامدي',
                        'gender' => 'female',
                        'date_of_birth' => '1987-09-17',
                        'phone' => '+966505678902',
                        'email' => 'amal.ghamdi@email.com',
                        'is_family_head' => false,
                        'relation' => 'الزوجة'
                    ],
                    [
                        'first_name' => 'عبدالرحمن',
                        'last_name' => 'محمد الغامدي',
                        'gender' => 'male',
                        'date_of_birth' => '2013-04-12',
                        'phone' => null,
                        'email' => null,
                        'is_family_head' => false,
                        'relation' => 'الابن'
                    ]
                ]
            ]
        ];

        // إضافة مرضى فرديين (غير عائليين)
        $individualPatients = [
            [
                'first_name' => 'خالد',
                'last_name' => 'سالم الحربي',
                'gender' => 'male',
                'date_of_birth' => '1995-03-15',
                'phone' => '+966506789012',
                'email' => 'khalid.harbi@email.com',
                'family_code' => null
            ],
            [
                'first_name' => 'ريم',
                'last_name' => 'أحمد الشهري',
                'gender' => 'female',
                'date_of_birth' => '1990-08-28',
                'phone' => '+966507890123',
                'email' => 'reem.shehri@email.com',
                'family_code' => null
            ],
            [
                'first_name' => 'عبدالعزيز',
                'last_name' => 'محمد العنزي',
                'gender' => 'male',
                'date_of_birth' => '1965-12-10',
                'phone' => '+966508901234',
                'email' => 'abdulaziz.anzi@email.com',
                'family_code' => null
            ],
            [
                'first_name' => 'سلمى',
                'last_name' => 'عبدالله الزهراني',
                'gender' => 'female',
                'date_of_birth' => '1998-07-05',
                'phone' => '+966509012345',
                'email' => 'salma.zahrani@email.com',
                'family_code' => null
            ],
            [
                'first_name' => 'فهد',
                'last_name' => 'سعد البقمي',
                'gender' => 'male',
                'date_of_birth' => '1972-11-18',
                'phone' => '+966500123456',
                'email' => 'fahd.baqmi@email.com',
                'family_code' => null
            ]
        ];

        $patientCount = 0;

        // إنشاء العائلات
        foreach ($families as $family) {
            foreach ($family['members'] as $member) {
                $patient = Patient::create([
                    'name' => $member['first_name'] . ' ' . $member['last_name'],
                    'name_en' => $member['first_name'] . ' ' . $member['last_name'],
                    'gender' => $member['gender'],
                    'date_of_birth' => $member['date_of_birth'],
                    'phone' => $member['phone'],
                    'email' => $member['email'],
                    'national_id' => $this->generateNationalId(),
                    'family_code' => $family['family_code'],
                    'family_relation' => $member['is_family_head'] ? 'self' : 'child',
                    'address' => 'الرياض، المملكة العربية السعودية',
                    'city' => 'الرياض',
                    'country' => 'المملكة العربية السعودية',
                    'nationality' => 'سعودي',
                    'emergency_contact' => [
                        'name' => $member['is_family_head'] ? 'جهة الاتصال الطارئة' : $family['members'][0]['first_name'] . ' ' . $family['members'][0]['last_name'],
                        'phone' => $member['is_family_head'] ? '+966500000000' : $family['members'][0]['phone']
                    ],
                    'blood_type' => $this->getRandomBloodType(),
                    'allergies' => [$this->getRandomAllergies()],
                    'medical_notes' => $this->getRandomMedicalHistory($member['gender'], $member['date_of_birth']),
                    'is_active' => true
                ]);

                $patientCount++;
                $this->command->info("✅ تم إنشاء المريض: {$member['first_name']} {$member['last_name']} - {$member['relation']} ({$family['family_code']})");
            }
        }

        // إنشاء المرضى الفرديين
        foreach ($individualPatients as $patientData) {
            $patient = Patient::create([
                'name' => $patientData['first_name'] . ' ' . $patientData['last_name'],
                'name_en' => $patientData['first_name'] . ' ' . $patientData['last_name'],
                'gender' => $patientData['gender'],
                'date_of_birth' => $patientData['date_of_birth'],
                'phone' => $patientData['phone'],
                'email' => $patientData['email'],
                'national_id' => $this->generateNationalId(),
                'family_code' => null,
                'family_relation' => 'self',
                'address' => 'الرياض، المملكة العربية السعودية',
                'city' => 'الرياض',
                'country' => 'المملكة العربية السعودية',
                'nationality' => 'سعودي',
                'emergency_contact' => [
                    'name' => 'جهة الاتصال الطارئة',
                    'phone' => '+966500000000'
                ],
                'blood_type' => $this->getRandomBloodType(),
                'allergies' => [$this->getRandomAllergies()],
                'medical_notes' => $this->getRandomMedicalHistory($patientData['gender'], $patientData['date_of_birth']),
                'is_active' => true
            ]);

            $patientCount++;
            $this->command->info("✅ تم إنشاء المريض: {$patientData['first_name']} {$patientData['last_name']} - مريض فردي");
        }

        $this->command->info("✅ تم إنشاء {$patientCount} مريض في " . count($families) . " عائلات + " . count($individualPatients) . " مريض فردي");
    }

    private function assignInsuranceToPatients(): void
    {
        $this->command->info('🏥 ربط المرضى بالتأمين...');

        $patients = Patient::all();
        $policies = \App\Models\InsurancePolicy::active()->get();

        if ($policies->isEmpty()) {
            $this->command->warn('لا توجد بوالص تأمين نشطة');
            return;
        }

        $assignedCount = 0;

        foreach ($patients as $patient) {
            // 70% من المرضى لديهم تأمين
            if (rand(1, 100) <= 70) {
                $policy = $policies->random();
                
                // تحديد نوع العضوية حسب العائلة
                $membershipType = 'self';
                $policyHolderName = null;
                $relation = 'self';

                if ($patient->family_code && $patient->family_relation !== 'self') {
                    $familyHead = Patient::where('family_code', $patient->family_code)
                                        ->where('family_relation', 'self')
                                        ->first();
                    
                    if ($familyHead) {
                        $policyHolderName = $familyHead->full_name;
                        $relation = $this->getRelationFromPatientData($patient);
                    }
                }

                PatientInsurance::create([
                    'patient_id' => $patient->id,
                    'insurance_company_id' => $policy->insurance_company_id,
                    'insurance_policy_id' => $policy->id,
                    'member_id' => 'MEM-' . strtoupper(Str::random(8)),
                    'policy_holder_name' => $policyHolderName,
                    'policy_holder_relation' => $relation,
                    'card_number' => 'CARD-' . rand(100000000, 999999999),
                    'coverage_start_date' => '2024-01-01',
                    'coverage_end_date' => '2025-12-31',
                    'status' => 'active',
                    'annual_limit_used' => 0,
                    'annual_limit_remaining' => $policy->max_coverage_per_year,
                    'is_primary' => true,
                    'priority_order' => 1
                ]);

                $assignedCount++;
                $this->command->info("✅ تم ربط {$patient->full_name} ببوليصة {$policy->policy_name}");
            }
        }

        $this->command->info("✅ تم ربط {$assignedCount} مريض بالتأمين");
    }

    private function createAppointments(): void
    {
        $this->command->info('📅 إنشاء المواعيد...');

        $doctors = Doctor::all();
        $patients = Patient::all();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            $this->command->warn('لا توجد أطباء أو مرضى لإنشاء المواعيد');
            return;
        }

        $appointmentCount = 0;
        $statuses = ['scheduled', 'completed', 'cancelled', 'no_show'];
        $types = ['consultation', 'follow_up', 'emergency', 'surgery'];

        // إنشاء مواعيد للأسبوعين الماضيين والأسبوعين القادمين
        for ($i = -14; $i <= 14; $i++) {
            $date = Carbon::now()->addDays($i);
            
            // تخطي أيام الجمعة والسبت
            if ($date->isFriday() || $date->isSaturday()) {
                continue;
            }

            // إنشاء 5-15 موعد يومياً
            $dailyAppointments = rand(5, 15);
            
            for ($j = 0; $j < $dailyAppointments; $j++) {
                $doctor = $doctors->random();
                $patient = $patients->random();
                
                // تحديد وقت الموعد
                $hour = rand(8, 16);
                $minute = rand(0, 3) * 15; // 0, 15, 30, 45
                $appointmentTime = $date->copy()->setTime($hour, $minute);

                // تحديد الحالة حسب التاريخ
                if ($date->isPast()) {
                    $status = collect(['completed', 'cancelled', 'no_show'])->random();
                } else {
                    $status = 'scheduled';
                }

                $appointment = Appointment::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_date' => $appointmentTime->toDateString(),
                    'appointment_time' => $appointmentTime->toTimeString(),
                    'type' => collect($types)->random(),
                    'status' => $status,
                    'duration' => rand(15, 60),
                    'notes' => $this->getRandomAppointmentNotes(),
                    'created_at' => $appointmentTime->subDays(rand(1, 7)),
                    'updated_at' => $appointmentTime->subDays(rand(0, 3))
                ]);

                $appointmentCount++;
            }
        }

        $this->command->info("✅ تم إنشاء {$appointmentCount} موعد");
    }

    private function createInvoicesAndPayments(): void
    {
        $this->command->info('💰 إنشاء الفواتير والمدفوعات...');

        $completedAppointments = Appointment::where('status', 'completed')->get();
        $insuranceCompanies = InsuranceCompany::all();

        if ($completedAppointments->isEmpty()) {
            $this->command->warn('لا توجد مواعيد مكتملة لإنشاء الفواتير');
            return;
        }

        $invoiceCount = 0;
        $paymentCount = 0;

        foreach ($completedAppointments as $appointment) {
            // 80% من المواعيد المكتملة لها فواتير
            if (rand(1, 100) <= 80) {
                $patient = $appointment->patient;
                $doctor = $appointment->doctor;
                
                // تحديد نوع الفاتورة
                $patientInsurance = $patient->patientInsurances()->active()->first();
                $invoiceType = $patientInsurance ? 'insurance' : 'cash';
                
                $consultationFee = $doctor->consultation_fee ?: rand(200, 500);
                $additionalServices = rand(0, 300); // خدمات إضافية
                $totalAmount = $consultationFee + $additionalServices;
                
                $invoice = Invoice::create([
                    'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($invoiceCount + 1, 6, '0', STR_PAD_LEFT),
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'insurance_company_id' => $patientInsurance ? $patientInsurance->insurance_company_id : null,
                    'invoice_date' => $appointment->appointment_date,
                    'due_date' => Carbon::parse($appointment->appointment_date)->addDays(30),
                    'type' => $invoiceType,
                    'status' => 'draft',
                    'subtotal' => $totalAmount,
                    'tax_amount' => $totalAmount * 0.15, // ضريبة 15%
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount * 1.15,
                    'paid_amount' => 0,
                    'remaining_amount' => $totalAmount * 1.15,
                    'notes' => 'فاتورة استشارة طبية - ' . $appointment->type,
                    'created_at' => $appointment->appointment_date,
                    'updated_at' => $appointment->appointment_date
                ]);

                // إضافة عناصر الفاتورة
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'itemable_type' => 'consultation',
                    'itemable_id' => $appointment->id,
                    'description' => 'استشارة طبية - ' . $doctor->specialization,
                    'quantity' => 1,
                    'unit_price' => $consultationFee,
                    'total_amount' => $consultationFee,
                    'tax_rate' => 15.00,
                    'tax_amount' => $consultationFee * 0.15
                ]);

                if ($additionalServices > 0) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'itemable_type' => 'service',
                        'itemable_id' => null,
                        'description' => 'خدمات طبية إضافية',
                        'quantity' => 1,
                        'unit_price' => $additionalServices,
                        'total_amount' => $additionalServices,
                        'tax_rate' => 15.00,
                        'tax_amount' => $additionalServices * 0.15
                    ]);
                }

                // تحديث حالة الفاتورة
                $invoice->status = 'pending';
                $invoice->save();

                $invoiceCount++;

                // إنشاء مدفوعات لـ 70% من الفواتير
                if (rand(1, 100) <= 70) {
                    $paymentMethods = ['cash', 'card', 'bank_transfer'];
                    $paymentAmount = $invoice->total_amount;
                    
                    // للفواتير التأمينية، المريض يدفع جزء والتأمين يدفع الباقي
                    if ($invoiceType === 'insurance' && $patientInsurance) {
                        $coverage = $patientInsurance->calculateCoverage($invoice->total_amount);
                        $paymentAmount = $coverage['patient_responsibility'];
                    }

                    $payment = Payment::create([
                        'invoice_id' => $invoice->id,
                        'patient_id' => $patient->id,
                        'insurance_company_id' => ($invoiceType === 'insurance') ? $patientInsurance->insurance_company_id : null,
                        'amount' => $paymentAmount,
                        'payment_method' => collect($paymentMethods)->random(),
                        'payment_date' => Carbon::parse($appointment->appointment_date)->addDays(rand(0, 5)),
                        'status' => 'completed',
                        'reference_number' => 'PAY-' . strtoupper(Str::random(10)),
                        'notes' => 'دفعة مقابل ' . $invoice->invoice_number,
                        'created_at' => Carbon::parse($appointment->appointment_date)->addDays(rand(0, 5))
                    ]);

                    // تحديث الفاتورة
                    $invoice->paid_amount = $paymentAmount;
                    $invoice->remaining_amount = $invoice->total_amount - $paymentAmount;
                    $invoice->status = ($invoice->remaining_amount <= 0) ? 'paid' : 'partially_paid';
                    $invoice->save();

                    $paymentCount++;
                }
            }
        }

        $this->command->info("✅ تم إنشاء {$invoiceCount} فاتورة و {$paymentCount} دفعة");
    }

    // Helper methods
    private function generateNationalId(): string
    {
        return '1' . rand(100000000, 999999999);
    }

    private function getRandomBloodType(): string
    {
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        return collect($bloodTypes)->random();
    }

    private function getRandomAllergies(): ?string
    {
        $allergies = [
            null,
            'حساسية من البنسلين',
            'حساسية من الأسبرين',
            'حساسية من المكسرات',
            'حساسية من اللاكتوز',
            'حساسية من الغبار',
            'حساسية موسمية'
        ];
        return collect($allergies)->random();
    }

    private function getRandomMedicalHistory(string $gender, string $dateOfBirth): ?string
    {
        $age = Carbon::parse($dateOfBirth)->age;
        $histories = [];

        if ($age > 40) {
            $histories[] = 'ارتفاع ضغط الدم';
            $histories[] = 'السكري النوع الثاني';
            $histories[] = 'ارتفاع الكوليسترول';
        }

        if ($gender === 'female' && $age > 20) {
            $histories[] = 'فقر الدم';
            $histories[] = 'نقص فيتامين د';
        }

        if ($age < 18) {
            $histories[] = 'تطعيمات كاملة';
            $histories[] = 'نمو طبيعي';
        }

        $commonHistories = [
            'لا يوجد تاريخ مرضي مهم',
            'حساسية موسمية',
            'صداع نصفي',
            'آلام الظهر',
            'التهاب المفاصل'
        ];

        $histories = array_merge($histories, $commonHistories);
        
        return rand(1, 100) <= 30 ? collect($histories)->random() : null;
    }

    private function getRelationFromPatientData($patient): string
    {
        // تحديد العلاقة بناءً على العمر والجنس
        $age = Carbon::parse($patient->date_of_birth)->age;
        
        if ($age < 18) {
            return 'child';
        } elseif ($patient->gender === 'female' && $age >= 18) {
            return 'spouse';
        } else {
            return 'other';
        }
    }

    private function getRandomAppointmentNotes(): ?string
    {
        $notes = [
            null,
            'مراجعة دورية',
            'متابعة العلاج',
            'فحص شامل',
            'استشارة أولية',
            'تجديد الوصفة الطبية',
            'فحص ما بعد العملية'
        ];
        return collect($notes)->random();
    }

    private function getRandomChiefComplaint(): ?string
    {
        $complaints = [
            'صداع',
            'ألم في البطن',
            'حمى',
            'سعال',
            'ألم في الصدر',
            'دوخة',
            'ألم في الظهر',
            'التهاب الحلق',
            'طفح جلدي',
            'صعوبة في التنفس',
            'غثيان',
            'إرهاق عام',
            'ألم في المفاصل'
        ];
        return collect($complaints)->random();
    }
}