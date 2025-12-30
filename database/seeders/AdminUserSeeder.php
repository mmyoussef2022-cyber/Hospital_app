<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الأدوار الأساسية إذا لم تكن موجودة
        $roles = [
            'super_admin' => 'مدير النظام الرئيسي',
            'admin' => 'مدير النظام',
            'reception_supervisor' => 'مشرف الاستقبال',
            'doctor' => 'طبيب',
            'cashier' => 'أمين الصندوق'
        ];

        foreach ($roles as $roleName => $roleDisplayName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // إنشاء المستخدم الإداري الرئيسي
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'name' => 'مدير النظام الرئيسي',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'is_active' => true,
                'national_id' => '1234567890'
            ]
        );

        // تعيين دور المدير الرئيسي
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole && !$adminUser->hasRole('super_admin')) {
            $adminUser->assignRole($superAdminRole);
        }

        // إنشاء مستخدم استقبال تجريبي
        $receptionUser = User::firstOrCreate(
            ['email' => 'reception@hospital.com'],
            [
                'name' => 'موظف الاستقبال',
                'password' => Hash::make('reception123'),
                'email_verified_at' => now(),
                'is_active' => true,
                'national_id' => '0987654321'
            ]
        );

        // تعيين دور الاستقبال
        $receptionRole = Role::where('name', 'reception_supervisor')->first();
        if ($receptionRole && !$receptionUser->hasRole('reception_supervisor')) {
            $receptionUser->assignRole($receptionRole);
        }

        // إنشاء مستخدم طبيب تجريبي
        $doctorUser = User::firstOrCreate(
            ['email' => 'doctor@hospital.com'],
            [
                'name' => 'د. أحمد محمد',
                'password' => Hash::make('doctor123'),
                'email_verified_at' => now(),
                'is_active' => true,
                'national_id' => '1122334455'
            ]
        );

        // تعيين دور الطبيب
        $doctorRole = Role::where('name', 'doctor')->first();
        if ($doctorRole && !$doctorUser->hasRole('doctor')) {
            $doctorUser->assignRole($doctorRole);
        }

        // إنشاء مستخدم خزينة تجريبي
        $cashierUser = User::firstOrCreate(
            ['email' => 'cashier@hospital.com'],
            [
                'name' => 'أمين الصندوق',
                'password' => Hash::make('cashier123'),
                'email_verified_at' => now(),
                'is_active' => true,
                'national_id' => '5566778899'
            ]
        );

        // تعيين دور الخزينة
        $cashierRole = Role::where('name', 'cashier')->first();
        if ($cashierRole && !$cashierUser->hasRole('cashier')) {
            $cashierUser->assignRole($cashierRole);
        }

        $this->command->info('تم إنشاء المستخدمين التجريبيين بنجاح!');
        $this->command->info('');
        $this->command->info('🔐 بيانات تسجيل الدخول:');
        $this->command->info('');
        $this->command->info('👑 مدير النظام الرئيسي:');
        $this->command->info('   البريد الإلكتروني: admin@hospital.com');
        $this->command->info('   كلمة المرور: admin123');
        $this->command->info('');
        $this->command->info('🏥 موظف الاستقبال:');
        $this->command->info('   البريد الإلكتروني: reception@hospital.com');
        $this->command->info('   كلمة المرور: reception123');
        $this->command->info('');
        $this->command->info('👨‍⚕️ الطبيب:');
        $this->command->info('   البريد الإلكتروني: doctor@hospital.com');
        $this->command->info('   كلمة المرور: doctor123');
        $this->command->info('');
        $this->command->info('💰 أمين الصندوق:');
        $this->command->info('   البريد الإلكتروني: cashier@hospital.com');
        $this->command->info('   كلمة المرور: cashier123');
    }
}