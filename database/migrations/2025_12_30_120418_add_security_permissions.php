<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create security permissions
        $securityPermissions = [
            'security.view' => 'عرض لوحة تحكم الأمان',
            'security.manage' => 'إدارة إعدادات الأمان',
            'security.export' => 'تصدير سجلات الأمان',
            'security.logs' => 'عرض سجلات الأمان',
            'security.backup' => 'إنشاء النسخ الاحتياطية',
            'security.cleanup' => 'تنظيف السجلات القديمة',
            'security.block_ip' => 'حظر عناوين IP',
            'security.health_check' => 'فحص صحة النظام',
        ];

        foreach ($securityPermissions as $name => $displayName) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName, 'guard_name' => 'web']
            );
        }

        // Give all security permissions to admin and super-admin roles
        $adminRoles = ['admin', 'super-admin'];
        foreach ($adminRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach (array_keys($securityPermissions) as $permission) {
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        }

        // Give limited security permissions to other roles
        $limitedRoles = [
            'doctor' => ['security.view'],
            'nurse' => ['security.view'],
            'reception' => ['security.view'],
            'cashier' => ['security.view'],
            'lab-technician' => ['security.view'],
            'radiology-technician' => ['security.view'],
        ];

        foreach ($limitedRoles as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($permissions as $permission) {
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove security permissions
        $securityPermissions = [
            'security.view',
            'security.manage',
            'security.export',
            'security.logs',
            'security.backup',
            'security.cleanup',
            'security.block_ip',
            'security.health_check',
        ];

        Permission::whereIn('name', $securityPermissions)->delete();
    }
};