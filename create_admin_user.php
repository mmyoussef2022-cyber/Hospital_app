<?php

require_once 'vendor/autoload.php';

// تحميل Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

try {
    echo "إنشاء المستخدم الإداري...\n";
    
    // إنشاء الأدوار الأساسية
    $roles = ['super_admin', 'admin', 'reception_supervisor', 'doctor', 'cashier'];
    
    foreach ($roles as $roleName) {
        Role::firstOrCreate(['name' => $roleName]);
        echo "✓ تم إنشاء/التحقق من الدور: $roleName\n";
    }
    
    // إنشاء المستخدم الإداري
    $admin = User::where('email', 'admin@hospital.com')->first();
    
    if (!$admin) {
        $admin = new User();
        $admin->name = 'مدير النظام الرئيسي';
        $admin->email = 'admin@hospital.com';
        $admin->password = Hash::make('admin123');
        $admin->email_verified_at = now();
        $admin->is_active = true;
        $admin->national_id = '1234567890';
        $admin->save();
        
        echo "✓ تم إنشاء المستخدم الإداري\n";
    } else {
        echo "✓ المستخدم الإداري موجود بالفعل\n";
    }
    
    // تعيين دور المدير
    $superAdminRole = Role::where('name', 'super_admin')->first();
    if ($superAdminRole && !$admin->hasRole('super_admin')) {
        $admin->assignRole($superAdminRole);
        echo "✓ تم تعيين دور المدير الرئيسي\n";
    }
    
    echo "\n🎉 تم إنشاء المستخدم الإداري بنجاح!\n";
    echo "\n🔐 بيانات تسجيل الدخول:\n";
    echo "البريد الإلكتروني: admin@hospital.com\n";
    echo "كلمة المرور: admin123\n";
    echo "\n🌐 رابط تسجيل الدخول: http://127.0.0.1:8000/login\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}