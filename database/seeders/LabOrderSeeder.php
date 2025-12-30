<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class LabOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get available data
        $patients = Patient::all();
        $doctors = User::whereHas('appointments')->get();
        $labTests = LabTest::active()->get();

        if ($patients->isEmpty() || $doctors->isEmpty() || $labTests->isEmpty()) {
            $this->command->warn('لا توجد بيانات كافية لإنشاء طلبات المختبر. تأكد من وجود مرضى وأطباء وفحوصات مختبر.');
            return;
        }

        // Create sample orders for today
        $orders = [
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'lab_test_id' => $labTests->where('code', 'CBC')->first()->id ?? $labTests->first()->id,
                'priority' => 'urgent',
                'clinical_notes' => 'فحص عاجل - مريض يعاني من أعراض فقر الدم',
                'status' => 'ordered',
                'ordered_at' => now()->subHours(2)
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'lab_test_id' => $labTests->where('code', 'FBS')->first()->id ?? $labTests->first()->id,
                'priority' => 'routine',
                'clinical_notes' => 'فحص السكر الصيامي - متابعة دورية',
                'status' => 'collected',
                'ordered_at' => now()->subHours(3),
                'collected_at' => now()->subHours(1)
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'lab_test_id' => $labTests->where('code', 'LIPID')->first()->id ?? $labTests->first()->id,
                'priority' => 'routine',
                'clinical_notes' => 'فحص الدهون - تقييم صحة القلب',
                'status' => 'processing',
                'ordered_at' => now()->subHours(4),
                'collected_at' => now()->subHours(2)
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'lab_test_id' => $labTests->where('code', 'URINE')->first()->id ?? $labTests->first()->id,
                'priority' => 'stat',
                'clinical_notes' => 'فحص البول العاجل - اشتباه التهاب المسالك البولية',
                'status' => 'completed',
                'ordered_at' => now()->subHours(5),
                'collected_at' => now()->subHours(3),
                'completed_at' => now()->subHour()
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'lab_test_id' => $labTests->where('code', 'LIVER')->first()->id ?? $labTests->first()->id,
                'priority' => 'routine',
                'clinical_notes' => 'فحص وظائف الكبد - متابعة العلاج',
                'status' => 'completed',
                'ordered_at' => now()->subHours(6),
                'collected_at' => now()->subHours(4),
                'completed_at' => now()->subHours(2)
            ]
        ];

        foreach ($orders as $orderData) {
            $labTest = LabTest::find($orderData['lab_test_id']);
            $orderData['total_amount'] = $labTest->price;
            
            LabOrder::create($orderData);
        }

        $this->command->info('تم إنشاء ' . count($orders) . ' طلب مختبر لليوم');
    }
}