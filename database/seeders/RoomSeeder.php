<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Bed;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ICU Rooms
        $this->createRoom('ICU101', 'icu', 'العناية المركزة', 1, 'الجناح الشرقي', 1, 1500, 'غرفة عناية مركزة مجهزة بأحدث المعدات', ['oxygen', 'suction', 'monitor', 'ventilator'], ['ac', 'bathroom']);
        $this->createRoom('ICU102', 'icu', 'العناية المركزة', 1, 'الجناح الشرقي', 1, 1500, 'غرفة عناية مركزة مجهزة بأحدث المعدات', ['oxygen', 'suction', 'monitor', 'ventilator'], ['ac', 'bathroom']);
        $this->createRoom('ICU103', 'icu', 'العناية المركزة', 1, 'الجناح الشرقي', 2, 1800, 'غرفة عناية مركزة مزدوجة', ['oxygen', 'suction', 'monitor', 'ventilator', 'defibrillator'], ['ac', 'bathroom']);

        // Private Rooms
        $this->createRoom('PVT201', 'private', 'الجراحة العامة', 2, 'الجناح الغربي', 1, 800, 'غرفة خاصة مع جميع المرافق', ['oxygen', 'suction'], ['tv', 'wifi', 'ac', 'bathroom', 'fridge']);
        $this->createRoom('PVT202', 'private', 'الجراحة العامة', 2, 'الجناح الغربي', 1, 800, 'غرفة خاصة مع جميع المرافق', ['oxygen', 'suction'], ['tv', 'wifi', 'ac', 'bathroom', 'fridge']);
        $this->createRoom('PVT203', 'private', 'النساء والولادة', 2, 'الجناح الغربي', 1, 900, 'غرفة خاصة للولادة', ['oxygen', 'suction', 'monitor'], ['tv', 'wifi', 'ac', 'bathroom', 'fridge', 'balcony']);

        // Semi-Private Rooms
        $this->createRoom('SP301', 'semi_private', 'الباطنة', 3, 'الجناح الشمالي', 2, 500, 'غرفة شبه خاصة', ['oxygen'], ['tv', 'wifi', 'ac', 'bathroom']);
        $this->createRoom('SP302', 'semi_private', 'الباطنة', 3, 'الجناح الشمالي', 2, 500, 'غرفة شبه خاصة', ['oxygen'], ['tv', 'wifi', 'ac', 'bathroom']);
        $this->createRoom('SP303', 'semi_private', 'الأطفال', 3, 'الجناح الشمالي', 2, 450, 'غرفة شبه خاصة للأطفال', ['oxygen'], ['tv', 'wifi', 'ac', 'bathroom']);

        // Ward Rooms
        $this->createRoom('W401', 'ward', 'الباطنة', 4, 'الجناح الجنوبي', 4, 200, 'جناح عام', ['oxygen'], ['ac']);
        $this->createRoom('W402', 'ward', 'الباطنة', 4, 'الجناح الجنوبي', 4, 200, 'جناح عام', ['oxygen'], ['ac']);
        $this->createRoom('W403', 'ward', 'الجراحة العامة', 4, 'الجناح الجنوبي', 6, 200, 'جناح عام كبير', ['oxygen'], ['ac']);

        // Emergency Rooms
        $this->createRoom('ER001', 'emergency', 'الطوارئ', 1, null, 1, 300, 'غرفة طوارئ', ['oxygen', 'suction', 'monitor', 'defibrillator'], ['ac']);
        $this->createRoom('ER002', 'emergency', 'الطوارئ', 1, null, 1, 300, 'غرفة طوارئ', ['oxygen', 'suction', 'monitor', 'defibrillator'], ['ac']);
        $this->createRoom('ER003', 'emergency', 'الطوارئ', 1, null, 2, 300, 'غرفة طوارئ مزدوجة', ['oxygen', 'suction', 'monitor', 'defibrillator'], ['ac']);

        // Surgery Rooms
        $this->createRoom('OR101', 'surgery', 'الجراحة العامة', 1, 'جناح العمليات', 1, 1000, 'غرفة عمليات', ['oxygen', 'suction', 'monitor', 'defibrillator'], ['ac']);
        $this->createRoom('OR102', 'surgery', 'جراحة القلب', 1, 'جناح العمليات', 1, 1200, 'غرفة عمليات القلب', ['oxygen', 'suction', 'monitor', 'defibrillator', 'ventilator'], ['ac']);
    }

    private function createRoom($roomNumber, $roomType, $department, $floor, $wing, $capacity, $dailyRate, $description, $equipment = [], $amenities = [])
    {
        $room = Room::create([
            'room_number' => $roomNumber,
            'room_type' => $roomType,
            'department' => $department,
            'floor' => $floor,
            'wing' => $wing,
            'capacity' => $capacity,
            'daily_rate' => $dailyRate,
            'description' => $description,
            'equipment' => $equipment,
            'amenities' => $amenities,
            'status' => 'available',
            'is_active' => true
        ]);

        // Create beds for the room
        for ($i = 1; $i <= $capacity; $i++) {
            $bedType = $this->getBedTypeForRoom($roomType);
            $features = $this->getFeaturesForBedType($bedType);

            Bed::create([
                'room_id' => $room->id,
                'bed_number' => $i,
                'bed_type' => $bedType,
                'features' => $features,
                'status' => 'available',
                'is_active' => true
            ]);
        }
    }

    private function getBedTypeForRoom($roomType): string
    {
        return match($roomType) {
            'icu' => 'icu',
            'emergency' => 'standard',
            'surgery' => 'standard',
            default => 'standard'
        };
    }

    private function getFeaturesForBedType($bedType): array
    {
        return match($bedType) {
            'icu' => ['adjustable', 'electric', 'side_rails', 'trendelenburg'],
            'bariatric' => ['adjustable', 'electric', 'weighing_scale'],
            'pediatric' => ['side_rails'],
            default => ['adjustable', 'side_rails']
        };
    }
}