<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Surgery;
use App\Models\Patient;
use App\Models\User;
use App\Models\SurgicalProcedure;
use App\Models\OperatingRoom;
use Carbon\Carbon;

class SurgerySeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::where('is_active', true)->take(10)->get();
        $surgeons = User::whereHas('doctor')->where('is_active', true)->take(5)->get();
        $procedures = SurgicalProcedure::active()->get();
        $operatingRooms = OperatingRoom::active()->get();

        if ($patients->isEmpty() || $surgeons->isEmpty() || $procedures->isEmpty() || $operatingRooms->isEmpty()) {
            $this->command->info('Skipping surgery seeder - missing required data (patients, surgeons, procedures, or operating rooms)');
            return;
        }

        $surgeries = [
            // Today's surgeries
            [
                'patient_id' => $patients->random()->id,
                'primary_surgeon_id' => $surgeons->random()->id,
                'surgical_procedure_id' => $procedures->where('complexity', 'minor')->random()->id,
                'operating_room_id' => $operatingRooms->random()->id,
                'scheduled_start_time' => today()->addHours(8),
                'scheduled_end_time' => today()->addHours(9),
                'priority' => 'routine',
                'status' => 'completed',
                'type' => 'outpatient',
                'actual_start_time' => today()->addHours(8)->addMinutes(5),
                'actual_end_time' => today()->addHours(8)->addMinutes(55),
                'pre_operative_notes' => 'Patient prepared for routine procedure',
                'operative_notes' => 'Procedure completed successfully without complications',
                'post_operative_notes' => 'Patient stable, discharged to recovery',
                'estimated_cost' => 3000.00,
                'actual_cost' => 2850.00,
                'estimated_duration' => 60,
                'is_emergency' => false,
                'requires_icu' => false,
                'requires_blood_bank' => false,
                'is_completed' => true,
            ],
            [
                'patient_id' => $patients->random()->id,
                'primary_surgeon_id' => $surgeons->random()->id,
                'surgical_procedure_id' => $procedures->where('complexity', 'moderate')->random()->id,
                'operating_room_id' => $operatingRooms->random()->id,
                'scheduled_start_time' => today()->addHours(10),
                'scheduled_end_time' => today()->addHours(12),
                'priority' => 'urgent',
                'status' => 'in_progress',
                'type' => 'inpatient',
                'actual_start_time' => today()->addHours(10)->addMinutes(10),
                'pre_operative_notes' => 'Patient prepared, all pre-op requirements met',
                'estimated_cost' => 8000.00,
                'estimated_duration' => 120,
                'is_emergency' => false,
                'requires_icu' => false,
                'requires_blood_bank' => true,
                'is_completed' => false,
            ],
            [
                'patient_id' => $patients->random()->id,
                'primary_surgeon_id' => $surgeons->random()->id,
                'surgical_procedure_id' => $procedures->where('complexity', 'major')->random()->id,
                'operating_room_id' => $operatingRooms->random()->id,
                'scheduled_start_time' => today()->addHours(14),
                'scheduled_end_time' => today()->addHours(17),
                'priority' => 'elective',
                'status' => 'scheduled',
                'type' => 'inpatient',
                'pre_operative_notes' => 'All pre-operative assessments completed',
                'estimated_cost' => 25000.00,
                'estimated_duration' => 180,
                'is_emergency' => false,
                'requires_icu' => true,
                'requires_blood_bank' => true,
                'is_completed' => false,
            ],
            
            // Tomorrow's surgeries
            [
                'patient_id' => $patients->random()->id,
                'primary_surgeon_id' => $surgeons->random()->id,
                'surgical_procedure_id' => $procedures->where('urgency_level', 'emergency')->random()->id,
                'operating_room_id' => $operatingRooms->random()->id,
                'scheduled_start_time' => now()->addDay()->addHours(7),
                'scheduled_end_time' => now()->addDay()->addHours(9),
                'priority' => 'emergency',
                'status' => 'scheduled',
                'type' => 'emergency',
                'pre_operative_notes' => 'Emergency case - minimal prep time',
                'estimated_cost' => 15000.00,
                'estimated_duration' => 120,
                'is_emergency' => true,
                'requires_icu' => true,
                'requires_blood_bank' => true,
                'is_completed' => false,
            ],
            [
                'patient_id' => $patients->random()->id,
                'primary_surgeon_id' => $surgeons->random()->id,
                'surgical_procedure_id' => $procedures->where('complexity', 'moderate')->random()->id,
                'operating_room_id' => $operatingRooms->random()->id,
                'scheduled_start_time' => now()->addDay()->addHours(9),
                'scheduled_end_time' => now()->addDay()->addHours(11),
                'priority' => 'routine',
                'status' => 'scheduled',
                'type' => 'inpatient',
                'pre_operative_notes' => 'Routine elective procedure',
                'estimated_cost' => 8000.00,
                'estimated_duration' => 120,
                'is_emergency' => false,
                'requires_icu' => false,
                'requires_blood_bank' => false,
                'is_completed' => false,
            ],
            
            // Past surgeries
            [
                'patient_id' => $patients->random()->id,
                'primary_surgeon_id' => $surgeons->random()->id,
                'surgical_procedure_id' => $procedures->where('complexity', 'complex')->random()->id,
                'operating_room_id' => $operatingRooms->random()->id,
                'scheduled_start_time' => now()->subDay()->addHours(8),
                'scheduled_end_time' => now()->subDay()->addHours(12),
                'priority' => 'urgent',
                'status' => 'completed',
                'type' => 'inpatient',
                'actual_start_time' => now()->subDay()->addHours(8)->addMinutes(15),
                'actual_end_time' => now()->subDay()->addHours(12)->addMinutes(30),
                'pre_operative_notes' => 'Complex case requiring specialized team',
                'operative_notes' => 'Procedure completed with minor complications managed intraoperatively',
                'post_operative_notes' => 'Patient stable, transferred to ICU for monitoring',
                'complications' => 'Minor bleeding controlled with additional sutures',
                'estimated_cost' => 60000.00,
                'actual_cost' => 65000.00,
                'estimated_duration' => 240,
                'is_emergency' => false,
                'requires_icu' => true,
                'requires_blood_bank' => true,
                'is_completed' => true,
            ],
            [
                'patient_id' => $patients->random()->id,
                'primary_surgeon_id' => $surgeons->random()->id,
                'surgical_procedure_id' => $procedures->where('is_outpatient', true)->random()->id,
                'operating_room_id' => $operatingRooms->random()->id,
                'scheduled_start_time' => Carbon::now()->subDays(2)->addHours(10),
                'scheduled_end_time' => Carbon::now()->subDays(2)->addHours(10)->addMinutes(30),
                'priority' => 'elective',
                'status' => 'completed',
                'type' => 'outpatient',
                'actual_start_time' => Carbon::now()->subDays(2)->addHours(10)->addMinutes(5),
                'actual_end_time' => Carbon::now()->subDays(2)->addHours(10)->addMinutes(25),
                'pre_operative_notes' => 'Outpatient procedure, patient in good health',
                'operative_notes' => 'Procedure completed successfully',
                'post_operative_notes' => 'Patient discharged home same day',
                'estimated_cost' => 3000.00,
                'actual_cost' => 2900.00,
                'estimated_duration' => 30,
                'is_emergency' => false,
                'requires_icu' => false,
                'requires_blood_bank' => false,
                'is_completed' => true,
            ],
            
            // Cancelled surgery
            [
                'patient_id' => $patients->random()->id,
                'primary_surgeon_id' => $surgeons->random()->id,
                'surgical_procedure_id' => $procedures->random()->id,
                'operating_room_id' => $operatingRooms->random()->id,
                'scheduled_start_time' => Carbon::now()->subDays(1)->addHours(14),
                'scheduled_end_time' => Carbon::now()->subDays(1)->addHours(16),
                'priority' => 'elective',
                'status' => 'cancelled',
                'type' => 'inpatient',
                'pre_operative_notes' => 'Patient prepared for surgery',
                'cancellation_reason' => 'Patient developed fever, surgery postponed for safety',
                'estimated_cost' => 12000.00,
                'estimated_duration' => 120,
                'is_emergency' => false,
                'requires_icu' => false,
                'requires_blood_bank' => true,
                'is_completed' => false,
            ],
        ];

        foreach ($surgeries as $surgeryData) {
            // Check for conflicts before creating
            $conflicts = Surgery::where('operating_room_id', $surgeryData['operating_room_id'])
                ->where(function($q) use ($surgeryData) {
                    $q->whereBetween('scheduled_start_time', [$surgeryData['scheduled_start_time'], $surgeryData['scheduled_end_time']])
                      ->orWhereBetween('scheduled_end_time', [$surgeryData['scheduled_start_time'], $surgeryData['scheduled_end_time']])
                      ->orWhere(function($q2) use ($surgeryData) {
                          $q2->where('scheduled_start_time', '<=', $surgeryData['scheduled_start_time'])
                             ->where('scheduled_end_time', '>=', $surgeryData['scheduled_end_time']);
                      });
                })
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->exists();

            if (!$conflicts) {
                $surgeryData['created_by'] = $surgeryData['primary_surgeon_id'];
                Surgery::create($surgeryData);
            }
        }
    }
}