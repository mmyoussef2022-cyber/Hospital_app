<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RadiologyStudy;

class RadiologyStudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studies = [
            // X-Ray Studies
            [
                'code' => 'XR001',
                'name' => 'أشعة سينية على الصدر',
                'name_en' => 'Chest X-Ray',
                'description' => 'فحص الصدر بالأشعة السينية لتشخيص أمراض الرئة والقلب',
                'category' => 'x-ray',
                'body_part' => 'chest',
                'price' => 150.00,
                'duration_minutes' => 15,
                'preparation_instructions' => 'إزالة المجوهرات والملابس المعدنية',
                'contrast_instructions' => null,
                'requires_contrast' => false,
                'requires_fasting' => false,
                'is_urgent_capable' => true,
                'is_active' => true
            ],
            [
                'code' => 'XR002',
                'name' => 'أشعة سينية على العمود الفقري',
                'name_en' => 'Spine X-Ray',
                'description' => 'فحص العمود الفقري بالأشعة السينية',
                'category' => 'x-ray',
                'body_part' => 'spine',
                'price' => 200.00,
                'duration_minutes' => 20,
                'preparation_instructions' => 'إزالة المجوهرات والملابس المعدنية',
                'contrast_instructions' => null,
                'requires_contrast' => false,
                'requires_fasting' => false,
                'is_urgent_capable' => true,
                'is_active' => true
            ],
            [
                'code' => 'XR003',
                'name' => 'أشعة سينية على البطن',
                'name_en' => 'Abdominal X-Ray',
                'description' => 'فحص البطن بالأشعة السينية',
                'category' => 'x-ray',
                'body_part' => 'abdomen',
                'price' => 180.00,
                'duration_minutes' => 15,
                'preparation_instructions' => 'إزالة المجوهرات والملابس المعدنية',
                'contrast_instructions' => null,
                'requires_contrast' => false,
                'requires_fasting' => false,
                'is_urgent_capable' => true,
                'is_active' => true
            ],

            // CT Scan Studies
            [
                'code' => 'CT001',
                'name' => 'أشعة مقطعية على الرأس',
                'name_en' => 'Head CT Scan',
                'description' => 'فحص الرأس والدماغ بالأشعة المقطعية',
                'category' => 'ct',
                'body_part' => 'head',
                'price' => 800.00,
                'duration_minutes' => 30,
                'preparation_instructions' => 'إزالة المجوهرات والملابس المعدنية، إبلاغ الطبيب عن أي حساسية',
                'contrast_instructions' => 'قد تحتاج لصبغة وريدية حسب الحالة',
                'requires_contrast' => false,
                'requires_fasting' => false,
                'is_urgent_capable' => true,
                'is_active' => true
            ],
            [
                'code' => 'CT002',
                'name' => 'أشعة مقطعية على الصدر',
                'name_en' => 'Chest CT Scan',
                'description' => 'فحص الصدر والرئتين بالأشعة المقطعية',
                'category' => 'ct',
                'body_part' => 'chest',
                'price' => 900.00,
                'duration_minutes' => 45,
                'preparation_instructions' => 'إزالة المجوهرات والملابس المعدنية',
                'contrast_instructions' => 'قد تحتاج لصبغة وريدية أو فموية',
                'requires_contrast' => true,
                'requires_fasting' => false,
                'is_urgent_capable' => true,
                'is_active' => true
            ],
            [
                'code' => 'CT003',
                'name' => 'أشعة مقطعية على البطن والحوض',
                'name_en' => 'Abdomen & Pelvis CT Scan',
                'description' => 'فحص البطن والحوض بالأشعة المقطعية',
                'category' => 'ct',
                'body_part' => 'abdomen',
                'price' => 1200.00,
                'duration_minutes' => 60,
                'preparation_instructions' => 'صيام 4 ساعات قبل الفحص، شرب الصبغة الفموية',
                'contrast_instructions' => 'صبغة فموية ووريدية مطلوبة',
                'requires_contrast' => true,
                'requires_fasting' => true,
                'is_urgent_capable' => false,
                'is_active' => true
            ],

            // MRI Studies
            [
                'code' => 'MRI001',
                'name' => 'رنين مغناطيسي على الرأس',
                'name_en' => 'Head MRI',
                'description' => 'فحص الرأس والدماغ بالرنين المغناطيسي',
                'category' => 'mri',
                'body_part' => 'head',
                'price' => 1500.00,
                'duration_minutes' => 60,
                'preparation_instructions' => 'إزالة جميع المعادن، إبلاغ الطبيب عن أي زراعات معدنية',
                'contrast_instructions' => 'قد تحتاج لصبغة الجادولينيوم',
                'requires_contrast' => false,
                'requires_fasting' => false,
                'is_urgent_capable' => false,
                'is_active' => true
            ],
            [
                'code' => 'MRI002',
                'name' => 'رنين مغناطيسي على العمود الفقري',
                'name_en' => 'Spine MRI',
                'description' => 'فحص العمود الفقري بالرنين المغناطيسي',
                'category' => 'mri',
                'body_part' => 'spine',
                'price' => 1800.00,
                'duration_minutes' => 75,
                'preparation_instructions' => 'إزالة جميع المعادن، إبلاغ الطبيب عن أي زراعات معدنية',
                'contrast_instructions' => 'قد تحتاج لصبغة الجادولينيوم',
                'requires_contrast' => false,
                'requires_fasting' => false,
                'is_urgent_capable' => false,
                'is_active' => true
            ],

            // Ultrasound Studies
            [
                'code' => 'US001',
                'name' => 'موجات فوق صوتية على البطن',
                'name_en' => 'Abdominal Ultrasound',
                'description' => 'فحص أعضاء البطن بالموجات فوق الصوتية',
                'category' => 'ultrasound',
                'body_part' => 'abdomen',
                'price' => 300.00,
                'duration_minutes' => 30,
                'preparation_instructions' => 'صيام 8 ساعات قبل الفحص، شرب الماء قبل الفحص بساعة',
                'contrast_instructions' => null,
                'requires_contrast' => false,
                'requires_fasting' => true,
                'is_urgent_capable' => true,
                'is_active' => true
            ],
            [
                'code' => 'US002',
                'name' => 'موجات فوق صوتية على الحوض',
                'name_en' => 'Pelvic Ultrasound',
                'description' => 'فحص أعضاء الحوض بالموجات فوق الصوتية',
                'category' => 'ultrasound',
                'body_part' => 'pelvis',
                'price' => 350.00,
                'duration_minutes' => 30,
                'preparation_instructions' => 'امتلاء المثانة بشرب الماء قبل الفحص',
                'contrast_instructions' => null,
                'requires_contrast' => false,
                'requires_fasting' => false,
                'is_urgent_capable' => true,
                'is_active' => true
            ],
            [
                'code' => 'US003',
                'name' => 'موجات فوق صوتية على القلب',
                'name_en' => 'Echocardiogram',
                'description' => 'فحص القلب بالموجات فوق الصوتية',
                'category' => 'ultrasound',
                'body_part' => 'chest',
                'price' => 500.00,
                'duration_minutes' => 45,
                'preparation_instructions' => 'لا توجد تحضيرات خاصة',
                'contrast_instructions' => null,
                'requires_contrast' => false,
                'requires_fasting' => false,
                'is_urgent_capable' => true,
                'is_active' => true
            ],

            // Mammography
            [
                'code' => 'MAM001',
                'name' => 'تصوير الثدي الشعاعي',
                'name_en' => 'Mammography',
                'description' => 'فحص الثدي بالأشعة السينية للكشف المبكر عن السرطان',
                'category' => 'mammography',
                'body_part' => 'chest',
                'price' => 400.00,
                'duration_minutes' => 20,
                'preparation_instructions' => 'تجنب استخدام مزيل العرق أو البودرة، ارتداء ملابس منفصلة',
                'contrast_instructions' => null,
                'requires_contrast' => false,
                'requires_fasting' => false,
                'is_urgent_capable' => false,
                'is_active' => true
            ],

            // Fluoroscopy
            [
                'code' => 'FL001',
                'name' => 'تنظير الجهاز الهضمي العلوي',
                'name_en' => 'Upper GI Fluoroscopy',
                'description' => 'فحص الجهاز الهضمي العلوي بالتنظير الشعاعي',
                'category' => 'fluoroscopy',
                'body_part' => 'abdomen',
                'price' => 600.00,
                'duration_minutes' => 45,
                'preparation_instructions' => 'صيام 12 ساعة قبل الفحص',
                'contrast_instructions' => 'شرب صبغة الباريوم أثناء الفحص',
                'requires_contrast' => true,
                'requires_fasting' => true,
                'is_urgent_capable' => false,
                'is_active' => true
            ],

            // Nuclear Medicine
            [
                'code' => 'NM001',
                'name' => 'مسح العظام النووي',
                'name_en' => 'Bone Scan',
                'description' => 'فحص العظام بالطب النووي للكشف عن الأورام والالتهابات',
                'category' => 'nuclear',
                'body_part' => 'whole_body',
                'price' => 1000.00,
                'duration_minutes' => 180,
                'preparation_instructions' => 'شرب كمية كبيرة من الماء، حقن المادة المشعة قبل 3 ساعات',
                'contrast_instructions' => 'حقن مادة مشعة في الوريد',
                'requires_contrast' => true,
                'requires_fasting' => false,
                'is_urgent_capable' => false,
                'is_active' => true
            ]
        ];

        foreach ($studies as $study) {
            RadiologyStudy::create($study);
        }
    }
}