<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OperatingRoom;
use App\Models\Room;

class OperatingRoomSeeder extends Seeder
{
    public function run(): void
    {
        // Get surgery rooms from the existing rooms
        $surgeryRooms = Room::where('room_type', 'surgery')->get();

        $operatingRoomsData = [
            [
                'or_number' => 'OR101',
                'name' => 'General Operating Room 1',
                'name_ar' => 'غرفة العمليات العامة 1',
                'or_type' => 'general',
                'capabilities' => ['general_surgery', 'laparoscopy', 'endoscopy'],
                'equipment' => ['electrocautery', 'suction', 'monitors', 'defibrillator'],
                'monitoring_systems' => ['ecg', 'pulse_oximetry', 'blood_pressure', 'temperature'],
                'has_laminar_flow' => true,
                'has_imaging' => false,
                'has_robotic_system' => false,
                'has_cardiac_bypass' => false,
                'has_neuro_monitoring' => false,
                'temperature_min' => 18.0,
                'temperature_max' => 24.0,
                'humidity_min' => 45.0,
                'humidity_max' => 55.0,
                'status' => 'available',
                'is_active' => true,
                'is_emergency_ready' => true,
            ],
            [
                'or_number' => 'OR102',
                'name' => 'General Operating Room 2',
                'name_ar' => 'غرفة العمليات العامة 2',
                'or_type' => 'general',
                'capabilities' => ['general_surgery', 'orthopedic', 'trauma'],
                'equipment' => ['electrocautery', 'suction', 'monitors', 'orthopedic_table'],
                'monitoring_systems' => ['ecg', 'pulse_oximetry', 'blood_pressure', 'temperature'],
                'has_laminar_flow' => true,
                'has_imaging' => false,
                'has_robotic_system' => false,
                'has_cardiac_bypass' => false,
                'has_neuro_monitoring' => false,
                'temperature_min' => 18.0,
                'temperature_max' => 24.0,
                'humidity_min' => 45.0,
                'humidity_max' => 55.0,
                'status' => 'available',
                'is_active' => true,
                'is_emergency_ready' => false,
            ],
        ];

        // Create operating rooms for available surgery rooms
        foreach ($operatingRoomsData as $index => $orData) {
            $surgeryRoom = $surgeryRooms->where('room_number', $orData['or_number'])->first();
            if ($surgeryRoom) {
                $orData['room_id'] = $surgeryRoom->id;
                OperatingRoom::create($orData);
            }
        }
    }
}