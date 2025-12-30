<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إنشاء المستخدم الرئيسي للإنتاج
        $admin = User::firstOrCreate([
            'email' => 'admin@hospital.com'
        ], [
            'name' => 'مدير النظام',
            'password' => bcrypt('admin123'),
            'email_verified_at' => now(),
        ]);

        // إنشاء الأدوار الأساسية
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        
        // تعيين الدور للمستخدم
        if (!$admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }

        // إعطاء جميع الصلاحيات للمدير الرئيسي
        $allPermissions = Permission::all();
        if ($allPermissions->count() > 0) {
            $superAdminRole->syncPermissions($allPermissions);
        }

        // إنشاء مستخدم تجريبي للاختبار
        $testUser = User::firstOrCreate([
            'email' => 'test@hospital.com'
        ], [
            'name' => 'مستخدم تجريبي',
            'password' => bcrypt('test123'),
            'email_verified_at' => now(),
        ]);

        // تعيين دور طبيب للمستخدم التجريبي
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        if (!$testUser->hasRole('doctor')) {
            $testUser->assignRole('doctor');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا نحذف البيانات في الإنتاج
    }
};