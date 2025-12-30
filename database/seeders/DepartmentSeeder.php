<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name_ar' => 'الطوارئ',
                'name_en' => 'Emergency Department',
                'code' => 'EMRG',
                'description_ar' => 'قسم الطوارئ والحالات الحرجة',
                'description_en' => 'Emergency and critical care department',
                'location' => 'Ground Floor - Wing A',
                'phone' => '+966112345678',
                'extension' => '100',
                'capacity' => 50,
                'working_hours' => json_encode([
                    'monday' => ['start' => '00:00', 'end' => '23:59'],
                    'tuesday' => ['start' => '00:00', 'end' => '23:59'],
                    'wednesday' => ['start' => '00:00', 'end' => '23:59'],
                    'thursday' => ['start' => '00:00', 'end' => '23:59'],
                    'friday' => ['start' => '00:00', 'end' => '23:59'],
                    'saturday' => ['start' => '00:00', 'end' => '23:59'],
                    'sunday' => ['start' => '00:00', 'end' => '23:59'],
                ])
            ],
            [
                'name_ar' => 'الباطنة',
                'name_en' => 'Internal Medicine',
                'code' => 'INTM',
                'description_ar' => 'قسم الطب الباطني والأمراض المزمنة',
                'description_en' => 'Internal medicine and chronic diseases',
                'location' => 'Second Floor - Wing B',
                'phone' => '+966112345679',
                'extension' => '200',
                'capacity' => 30,
                'working_hours' => json_encode([
                    'monday' => ['start' => '08:00', 'end' => '16:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '16:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '16:00'],
                    'thursday' => ['start' => '08:00', 'end' => '16:00'],
                    'friday' => ['start' => '08:00', 'end' => '12:00'],
                    'saturday' => ['start' => '08:00', 'end' => '16:00'],
                    'sunday' => ['start' => '08:00', 'end' => '16:00'],
                ])
            ],
            [
                'name_ar' => 'الجراحة العامة',
                'name_en' => 'General Surgery',
                'code' => 'SURG',
                'description_ar' => 'قسم الجراحة العامة والعمليات',
                'description_en' => 'General surgery and operations',
                'location' => 'Third Floor - Wing C',
                'phone' => '+966112345680',
                'extension' => '300',
                'capacity' => 20,
                'working_hours' => json_encode([
                    'monday' => ['start' => '07:00', 'end' => '15:00'],
                    'tuesday' => ['start' => '07:00', 'end' => '15:00'],
                    'wednesday' => ['start' => '07:00', 'end' => '15:00'],
                    'thursday' => ['start' => '07:00', 'end' => '15:00'],
                    'friday' => ['start' => '07:00', 'end' => '12:00'],
                    'saturday' => ['start' => '07:00', 'end' => '15:00'],
                    'sunday' => ['start' => '07:00', 'end' => '15:00'],
                ])
            ],
            [
                'name_ar' => 'أمراض القلب',
                'name_en' => 'Cardiology',
                'code' => 'CARD',
                'description_ar' => 'قسم أمراض القلب والأوعية الدموية',
                'description_en' => 'Cardiology and cardiovascular diseases',
                'location' => 'Second Floor - Wing A',
                'phone' => '+966112345681',
                'extension' => '250',
                'capacity' => 25,
                'working_hours' => json_encode([
                    'monday' => ['start' => '08:00', 'end' => '16:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '16:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '16:00'],
                    'thursday' => ['start' => '08:00', 'end' => '16:00'],
                    'friday' => ['start' => '08:00', 'end' => '12:00'],
                    'saturday' => ['start' => '08:00', 'end' => '16:00'],
                    'sunday' => ['start' => '08:00', 'end' => '16:00'],
                ])
            ],
            [
                'name_ar' => 'العظام',
                'name_en' => 'Orthopedics',
                'code' => 'ORTH',
                'description_ar' => 'قسم جراحة العظام والمفاصل',
                'description_en' => 'Orthopedic surgery and joints',
                'location' => 'Third Floor - Wing B',
                'phone' => '+966112345682',
                'extension' => '350',
                'capacity' => 20,
                'working_hours' => json_encode([
                    'monday' => ['start' => '08:00', 'end' => '16:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '16:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '16:00'],
                    'thursday' => ['start' => '08:00', 'end' => '16:00'],
                    'friday' => ['start' => '08:00', 'end' => '12:00'],
                    'saturday' => ['start' => '08:00', 'end' => '16:00'],
                    'sunday' => ['start' => '08:00', 'end' => '16:00'],
                ])
            ],
            [
                'name_ar' => 'المختبر',
                'name_en' => 'Laboratory',
                'code' => 'LAB',
                'description_ar' => 'قسم المختبر والتحاليل الطبية',
                'description_en' => 'Laboratory and medical tests',
                'location' => 'Ground Floor - Wing B',
                'phone' => '+966112345683',
                'extension' => '150',
                'capacity' => 15,
                'working_hours' => json_encode([
                    'monday' => ['start' => '06:00', 'end' => '22:00'],
                    'tuesday' => ['start' => '06:00', 'end' => '22:00'],
                    'wednesday' => ['start' => '06:00', 'end' => '22:00'],
                    'thursday' => ['start' => '06:00', 'end' => '22:00'],
                    'friday' => ['start' => '06:00', 'end' => '18:00'],
                    'saturday' => ['start' => '06:00', 'end' => '22:00'],
                    'sunday' => ['start' => '06:00', 'end' => '22:00'],
                ])
            ],
            [
                'name_ar' => 'الأشعة',
                'name_en' => 'Radiology',
                'code' => 'RAD',
                'description_ar' => 'قسم الأشعة والتصوير الطبي',
                'description_en' => 'Radiology and medical imaging',
                'location' => 'Ground Floor - Wing C',
                'phone' => '+966112345684',
                'extension' => '180',
                'capacity' => 10,
                'working_hours' => json_encode([
                    'monday' => ['start' => '07:00', 'end' => '20:00'],
                    'tuesday' => ['start' => '07:00', 'end' => '20:00'],
                    'wednesday' => ['start' => '07:00', 'end' => '20:00'],
                    'thursday' => ['start' => '07:00', 'end' => '20:00'],
                    'friday' => ['start' => '07:00', 'end' => '15:00'],
                    'saturday' => ['start' => '07:00', 'end' => '20:00'],
                    'sunday' => ['start' => '07:00', 'end' => '20:00'],
                ])
            ],
            [
                'name_ar' => 'الأسنان',
                'name_en' => 'Dentistry',
                'code' => 'DENT',
                'description_ar' => 'قسم طب وجراحة الأسنان',
                'description_en' => 'Dentistry and oral surgery',
                'location' => 'First Floor - Wing A',
                'phone' => '+966112345685',
                'extension' => '220',
                'capacity' => 15,
                'working_hours' => json_encode([
                    'monday' => ['start' => '08:00', 'end' => '17:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '17:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '17:00'],
                    'thursday' => ['start' => '08:00', 'end' => '17:00'],
                    'friday' => ['start' => '08:00', 'end' => '12:00'],
                    'saturday' => ['start' => '08:00', 'end' => '17:00'],
                    'sunday' => ['start' => '08:00', 'end' => '17:00'],
                ])
            ]
        ];

        foreach ($departments as $department) {
            \App\Models\Department::create($department);
        }
    }
}
