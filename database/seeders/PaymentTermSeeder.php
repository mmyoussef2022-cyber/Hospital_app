<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentTerm;

class PaymentTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentTerms = [
            [
                'name' => 'Cash on Delivery',
                'name_ar' => 'نقدي عند التسليم',
                'description' => 'Payment due immediately upon service delivery',
                'description_ar' => 'الدفع مستحق فور تقديم الخدمة',
                'days' => 0,
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => 'Net 15',
                'name_ar' => 'صافي 15 يوم',
                'description' => 'Payment due within 15 days of invoice date',
                'description_ar' => 'الدفع مستحق خلال 15 يوم من تاريخ الفاتورة',
                'days' => 15,
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => 'Net 30',
                'name_ar' => 'صافي 30 يوم',
                'description' => 'Payment due within 30 days of invoice date',
                'description_ar' => 'الدفع مستحق خلال 30 يوم من تاريخ الفاتورة',
                'days' => 30,
                'is_active' => true,
                'is_default' => true
            ],
            [
                'name' => '2/10 Net 30',
                'name_ar' => '2% خصم خلال 10 أيام أو صافي 30',
                'description' => '2% discount if paid within 10 days, otherwise net 30 days',
                'description_ar' => 'خصم 2% إذا تم الدفع خلال 10 أيام، وإلا صافي 30 يوم',
                'days' => 30,
                'discount_percentage' => 2.00,
                'discount_days' => 10,
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => '1/15 Net 45',
                'name_ar' => '1% خصم خلال 15 يوم أو صافي 45',
                'description' => '1% discount if paid within 15 days, otherwise net 45 days',
                'description_ar' => 'خصم 1% إذا تم الدفع خلال 15 يوم، وإلا صافي 45 يوم',
                'days' => 45,
                'discount_percentage' => 1.00,
                'discount_days' => 15,
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => 'Net 60 with Late Fee',
                'name_ar' => 'صافي 60 يوم مع رسوم تأخير',
                'description' => 'Payment due within 60 days, 1.5% monthly late fee after 5 days overdue',
                'description_ar' => 'الدفع مستحق خلال 60 يوم، رسوم تأخير 1.5% شهرياً بعد 5 أيام من التأخير',
                'days' => 60,
                'late_fee_percentage' => 1.50,
                'late_fee_days' => 5,
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => 'Extended Terms - Net 90',
                'name_ar' => 'شروط ممتدة - صافي 90 يوم',
                'description' => 'Extended payment terms for special cases - 90 days',
                'description_ar' => 'شروط دفع ممتدة للحالات الخاصة - 90 يوم',
                'days' => 90,
                'late_fee_percentage' => 2.00,
                'late_fee_days' => 10,
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => 'Quick Pay Discount',
                'name_ar' => 'خصم الدفع السريع',
                'description' => '3% discount if paid within 5 days, otherwise net 20 days',
                'description_ar' => 'خصم 3% إذا تم الدفع خلال 5 أيام، وإلا صافي 20 يوم',
                'days' => 20,
                'discount_percentage' => 3.00,
                'discount_days' => 5,
                'is_active' => true,
                'is_default' => false
            ]
        ];

        foreach ($paymentTerms as $term) {
            PaymentTerm::create($term);
        }
    }
}
