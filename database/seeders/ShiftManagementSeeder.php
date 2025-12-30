<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CashRegister;
use App\Models\Shift;
use App\Models\ShiftTransaction;
use App\Models\ShiftReport;
use App\Models\ShiftHandover;
use App\Models\StaffProductivity;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;

class ShiftManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating shift management test data...');

        // Get departments and users
        $departments = Department::where('is_active', true)->get();
        $users = User::where('is_active', true)->get();

        if ($departments->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No departments or users found. Please run department and user seeders first.');
            return;
        }

        // Create cash registers for each department
        $this->command->info('Creating cash registers...');
        $cashRegisters = [];
        
        foreach ($departments->take(5) as $index => $department) {
            $register = CashRegister::create([
                'register_name' => "صندوق {$department->name_ar}",
                'department_id' => $department->id,
                'location' => "استقبال {$department->name_ar}",
                'opening_balance' => rand(1000, 5000),
                'current_balance' => rand(1000, 5000),
                'expected_balance' => rand(1000, 5000),
                'status' => 'active',
                'is_active' => true
            ]);
            
            $cashRegisters[] = $register;
        }

        $this->command->info('Created ' . count($cashRegisters) . ' cash registers');

        // Create shifts for the past week and upcoming week
        $this->command->info('Creating shifts...');
        $shifts = [];
        $shiftTypes = ['morning', 'afternoon', 'evening', 'night'];
        $shiftTimes = [
            'morning' => ['06:00', '14:00'],
            'afternoon' => ['14:00', '22:00'],
            'evening' => ['18:00', '02:00'],
            'night' => ['22:00', '06:00']
        ];

        for ($i = -7; $i <= 7; $i++) {
            $date = now()->addDays($i);
            
            foreach ($departments->take(3) as $department) {
                foreach ($shiftTypes as $type) {
                    if (rand(1, 100) <= 70) { // 70% chance of having a shift
                        $user = $users->whereNotNull('department_id')->random();
                        if (!$user) {
                            $user = $users->random();
                        }
                        $register = $cashRegisters[array_rand($cashRegisters)];
                        
                        $status = 'scheduled';
                        if ($i < 0) { // Past shifts
                            $status = collect(['completed', 'completed', 'completed', 'cancelled', 'no_show'])
                                    ->random();
                        } elseif ($i == 0 && in_array($type, ['morning', 'afternoon'])) { // Today's early shifts
                            $status = collect(['active', 'completed'])->random();
                        }

                        $shift = Shift::create([
                            'user_id' => $user->id,
                            'department_id' => $department->id,
                            'cash_register_id' => $register->id,
                            'supervisor_id' => $users->random()->id,
                            'shift_type' => $type,
                            'shift_date' => $date->format('Y-m-d'),
                            'scheduled_start' => $shiftTimes[$type][0],
                            'scheduled_end' => $shiftTimes[$type][1],
                            'status' => $status,
                            'opening_cash_balance' => rand(500, 2000),
                            'expected_cash_balance' => rand(1000, 5000),
                            'total_revenue' => $status == 'completed' ? rand(2000, 10000) : 0,
                            'total_transactions' => $status == 'completed' ? rand(20, 100) : 0,
                            'patients_served' => $status == 'completed' ? rand(15, 80) : 0
                        ]);

                        if ($status == 'active') {
                            $shift->actual_start = $date->copy()->setTimeFromTimeString($shiftTimes[$type][0]);
                            $shift->save();
                        } elseif ($status == 'completed') {
                            $shift->actual_start = $date->copy()->setTimeFromTimeString($shiftTimes[$type][0]);
                            $shift->actual_end = $date->copy()->setTimeFromTimeString($shiftTimes[$type][1]);
                            $shift->closing_cash_balance = $shift->expected_cash_balance + rand(-100, 200);
                            $shift->cash_difference = $shift->closing_cash_balance - $shift->expected_cash_balance;
                            $shift->cash_verified = true;
                            $shift->cash_verified_at = $shift->actual_end;
                            $shift->save();
                        }

                        $shifts[] = $shift;
                    }
                }
            }
        }

        $this->command->info('Created ' . count($shifts) . ' shifts');

        // Create transactions for completed shifts
        $this->command->info('Creating shift transactions...');
        $transactionCount = 0;
        
        foreach ($shifts as $shift) {
            if ($shift->status == 'completed') {
                $numTransactions = rand(10, 50);
                
                for ($j = 0; $j < $numTransactions; $j++) {
                    $amount = rand(50, 500);
                    $transactionType = collect(['payment', 'payment', 'payment', 'refund'])->random();
                    $paymentMethod = collect(['cash', 'cash', 'card', 'insurance'])->random();
                    
                    ShiftTransaction::create([
                        'shift_id' => $shift->id,
                        'cash_register_id' => $shift->cash_register_id,
                        'patient_id' => rand(1, 10), // Assuming we have patients
                        'transaction_type' => $transactionType,
                        'payment_method' => $paymentMethod,
                        'amount' => $amount,
                        'received_amount' => $paymentMethod == 'cash' ? $amount + rand(0, 50) : null,
                        'change_amount' => $paymentMethod == 'cash' ? rand(0, 50) : 0,
                        'status' => 'completed',
                        'transaction_date' => $shift->actual_start->addMinutes(rand(0, 480)),
                        'processed_by' => $shift->user_id,
                        'description' => 'معاملة تجريبية - ' . $transactionType
                    ]);
                    
                    $transactionCount++;
                }
            }
        }

        $this->command->info('Created ' . $transactionCount . ' transactions');

        // Create shift reports for completed shifts
        $this->command->info('Creating shift reports...');
        $reportCount = 0;
        
        foreach ($shifts as $shift) {
            if ($shift->status == 'completed') {
                $report = ShiftReport::create([
                    'shift_id' => $shift->id,
                    'user_id' => $shift->user_id,
                    'department_id' => $shift->department_id,
                    'report_date' => $shift->shift_date,
                    'shift_start' => $shift->scheduled_start,
                    'shift_end' => $shift->scheduled_end,
                    'opening_balance' => $shift->opening_cash_balance,
                    'closing_balance' => $shift->closing_cash_balance,
                    'expected_balance' => $shift->expected_cash_balance,
                    'cash_difference' => $shift->cash_difference,
                    'total_revenue' => $shift->total_revenue,
                    'cash_payments' => $shift->total_revenue * 0.7,
                    'card_payments' => $shift->total_revenue * 0.2,
                    'insurance_payments' => $shift->total_revenue * 0.1,
                    'total_transactions' => $shift->total_transactions,
                    'cash_transactions' => $shift->total_transactions * 0.7,
                    'card_transactions' => $shift->total_transactions * 0.2,
                    'insurance_transactions' => $shift->total_transactions * 0.1,
                    'patients_served' => $shift->patients_served,
                    'appointments_handled' => rand(10, 30),
                    'new_registrations' => rand(2, 8),
                    'average_transaction_amount' => $shift->total_transactions > 0 ? 
                                                  $shift->total_revenue / $shift->total_transactions : 0,
                    'largest_transaction' => rand(200, 1000),
                    'smallest_transaction' => rand(20, 100),
                    'status' => collect(['completed', 'reviewed', 'approved'])->random()
                ]);

                if ($report->status == 'reviewed') {
                    $report->reviewed_by = $users->random()->id;
                    $report->reviewed_at = now()->subHours(rand(1, 24));
                } elseif ($report->status == 'approved') {
                    $report->reviewed_by = $users->random()->id;
                    $report->reviewed_at = now()->subHours(rand(25, 48));
                    $report->approved_by = $users->random()->id;
                    $report->approved_at = now()->subHours(rand(1, 24));
                }

                $report->save();
                $reportCount++;
            }
        }

        $this->command->info('Created ' . $reportCount . ' shift reports');

        // Create some handovers
        $this->command->info('Creating shift handovers...');
        $handoverCount = 0;
        
        /*
        $completedShifts = collect($shifts)->where('status', 'completed');
        foreach ($completedShifts->take(10) as $shift) {
            $nextShift = collect($shifts)->where('shift_date', $shift->shift_date)
                                       ->where('department_id', $shift->department_id)
                                       ->where('id', '!=', $shift->id)
                                       ->first();

            if ($nextShift) {
                ShiftHandover::create([
                    'from_shift_id' => $shift->id,
                    'to_shift_id' => $nextShift->id,
                    'from_user_id' => $shift->user_id,
                    'to_user_id' => $nextShift->user_id,
                    'department_id' => $shift->department_id,
                    'cash_register_id' => $shift->cash_register_id,
                    'handover_date' => $shift->actual_end ?? now(),
                    'cash_balance_handed_over' => $shift->closing_cash_balance,
                    'cash_balance_received' => $shift->closing_cash_balance + rand(-10, 10),
                    'cash_difference' => rand(-10, 10),
                    'cash_balance_verified' => true,
                    'register_keys_handed_over' => true,
                    'pending_transactions_reviewed' => true,
                    'system_access_transferred' => true,
                    'status' => collect(['completed', 'completed', 'pending'])->random(),
                    'outstanding_tasks' => 'مهام تجريبية للتسليم',
                    'important_notes' => 'ملاحظات مهمة للوردية القادمة'
                ]);
                
                $handoverCount++;
            }
        }
        */

        $this->command->info('Created ' . $handoverCount . ' handovers');

        // Create staff productivity records
        $this->command->info('Creating staff productivity records...');
        $productivityCount = 0;
        
        $completedShifts = collect($shifts)->where('status', 'completed');
        foreach ($completedShifts->take(20) as $shift) {
            StaffProductivity::create([
                'user_id' => $shift->user_id,
                'shift_id' => $shift->id,
                'department_id' => $shift->department_id,
                'productivity_date' => $shift->shift_date,
                'shift_start' => $shift->scheduled_start,
                'shift_end' => $shift->scheduled_end,
                'total_working_minutes' => 480, // 8 hours
                'break_minutes' => 60,
                'productive_minutes' => 420,
                'appointments_handled' => rand(15, 40),
                'patients_registered' => rand(5, 15),
                'patients_checked_in' => $shift->patients_served,
                'services_provided' => rand(20, 60),
                'prescriptions_issued' => rand(10, 30),
                'lab_orders_processed' => rand(5, 20),
                'radiology_orders_processed' => rand(2, 10),
                'invoices_generated' => rand(15, 35),
                'payments_processed' => $shift->total_transactions,
                'revenue_generated' => $shift->total_revenue,
                'collections_made' => $shift->total_revenue,
                'phone_calls_handled' => rand(20, 50),
                'emails_processed' => rand(5, 15),
                'documents_processed' => rand(10, 25),
                'efficiency_score' => rand(70, 95),
                'quality_score' => rand(75, 98),
                'customer_satisfaction_score' => rand(80, 95),
                'errors_made' => rand(0, 3),
                'corrections_needed' => rand(0, 2),
                'overtime_minutes' => rand(0, 60),
                'performance_rating' => collect(['excellent', 'good', 'good', 'satisfactory'])->random(),
                'evaluated_by' => $users->random()->id,
                'evaluated_at' => now()->subHours(rand(1, 48))
            ]);
            
            $productivityCount++;
        }

        $this->command->info('Created ' . $productivityCount . ' productivity records');

        $this->command->info('Shift management test data created successfully!');
        $this->command->info('Summary:');
        $this->command->info('- Cash Registers: ' . count($cashRegisters));
        $this->command->info('- Shifts: ' . count($shifts));
        $this->command->info('- Transactions: ' . $transactionCount);
        $this->command->info('- Reports: ' . $reportCount);
        $this->command->info('- Handovers: ' . $handoverCount);
        $this->command->info('- Productivity Records: ' . $productivityCount);
    }
}