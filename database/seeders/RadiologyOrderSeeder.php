<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RadiologyOrder;
use App\Models\RadiologyStudy;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class RadiologyOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get available data
        $patients = Patient::all();
        $doctors = User::whereHas('appointments')->get();
        $studies = RadiologyStudy::active()->get();

        if ($patients->isEmpty() || $doctors->isEmpty() || $studies->isEmpty()) {
            $this->command->warn('لا توجد بيانات كافية لإنشاء طلبات الأشعة. تأكد من وجود مرضى وأطباء وفحوصات أشعة.');
            return;
        }

        // Create sample orders for today
        $orders = [
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'radiology_study_id' => $studies->where('code', 'XR001')->first()->id, // Chest X-Ray
                'priority' => 'urgent',
                'clinical_indication' => 'ضيق في التنفس وألم في الصدر',
                'clinical_history' => 'مريض يعاني من أعراض تنفسية حادة',
                'special_instructions' => 'فحص عاجل - مريض في الطوارئ',
                'status' => 'ordered',
                'ordered_at' => now()->subHours(2)
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'radiology_study_id' => $studies->where('code', 'CT001')->first()->id, // Head CT
                'priority' => 'stat',
                'clinical_indication' => 'صداع شديد مفاجئ',
                'clinical_history' => 'مريض يعاني من صداع شديد مع غثيان',
                'special_instructions' => 'استبعاد النزيف الدماغي',
                'status' => 'scheduled',
                'ordered_at' => now()->subHours(1),
                'scheduled_at' => now()->addHour()
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'radiology_study_id' => $studies->where('code', 'US001')->first()->id, // Abdominal US
                'priority' => 'routine',
                'clinical_indication' => 'ألم في البطن',
                'clinical_history' => 'مريض يعاني من ألم في الجانب الأيمن من البطن',
                'special_instructions' => 'فحص المرارة والكبد',
                'status' => 'in_progress',
                'ordered_at' => now()->subHours(3),
                'scheduled_at' => now()->subMinutes(30),
                'started_at' => now()->subMinutes(15)
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'radiology_study_id' => $studies->where('code', 'XR002')->first()->id, // Spine X-Ray
                'priority' => 'routine',
                'clinical_indication' => 'ألم في أسفل الظهر',
                'clinical_history' => 'مريض يعاني من ألم مزمن في أسفل الظهر',
                'special_instructions' => 'فحص الفقرات القطنية',
                'status' => 'completed',
                'ordered_at' => now()->subHours(4),
                'scheduled_at' => now()->subHours(2),
                'started_at' => now()->subHours(1.5),
                'completed_at' => now()->subHour()
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'radiology_study_id' => $studies->where('code', 'US003')->first()->id, // Echo
                'priority' => 'urgent',
                'clinical_indication' => 'ألم في الصدر وضيق تنفس',
                'clinical_history' => 'مريض يعاني من أعراض قلبية',
                'special_instructions' => 'تقييم وظائف القلب',
                'status' => 'reported',
                'ordered_at' => now()->subHours(5),
                'scheduled_at' => now()->subHours(3),
                'started_at' => now()->subHours(2.5),
                'completed_at' => now()->subHours(2),
                'reported_at' => now()->subMinutes(30),
                'has_urgent_findings' => true
            ]
        ];

        foreach ($orders as $orderData) {
            $study = RadiologyStudy::find($orderData['radiology_study_id']);
            $orderData['total_amount'] = $study->price;
            
            RadiologyOrder::create($orderData);
        }

        $this->command->info('تم إنشاء ' . count($orders) . ' طلب أشعة لليوم');
    }
}