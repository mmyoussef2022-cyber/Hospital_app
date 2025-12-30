<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'display_name',
        'display_name_ar',
        'description',
        'description_ar',
        'module',
        'module_ar',
        'action',
        'action_ar',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // العلاقات
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions');
    }

    // الخصائص المحسوبة
    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->display_name_ar : $this->attributes['display_name'];
    }

    public function getDescriptionAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->attributes['description'];
    }

    public function getModuleNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->module_ar : $this->module;
    }

    public function getActionNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->action_ar : $this->action;
    }

    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    // الدوال الثابتة
    public static function getModules(): array
    {
        return [
            'dashboard' => 'لوحة التحكم',
            'patients' => 'المرضى',
            'appointments' => 'المواعيد',
            'medical_records' => 'السجلات الطبية',
            'prescriptions' => 'الوصفات الطبية',
            'laboratory' => 'المختبر',
            'radiology' => 'الأشعة',
            'pharmacy' => 'الصيدلية',
            'billing' => 'الفواتير',
            'cash_registers' => 'الصناديق النقدية',
            'reports' => 'التقارير',
            'users' => 'المستخدمين',
            'departments' => 'الأقسام',
            'rooms' => 'الغرف والأسرة',
            'surgeries' => 'العمليات الجراحية',
            'insurance' => 'التأمين',
            'settings' => 'الإعدادات'
        ];
    }

    public static function getActions(): array
    {
        return [
            'view' => 'عرض',
            'create' => 'إنشاء',
            'edit' => 'تعديل',
            'delete' => 'حذف',
            'approve' => 'موافقة',
            'reject' => 'رفض',
            'export' => 'تصدير',
            'import' => 'استيراد',
            'print' => 'طباعة',
            'manage' => 'إدارة'
        ];
    }

    public static function createDefaultPermissions(): void
    {
        $modules = static::getModules();
        $actions = static::getActions();

        foreach ($modules as $moduleKey => $moduleNameAr) {
            foreach ($actions as $actionKey => $actionNameAr) {
                $permissionName = "{$moduleKey}.{$actionKey}";
                
                Permission::firstOrCreate(
                    ['name' => $permissionName],
                    [
                        'name_ar' => "{$moduleNameAr}.{$actionNameAr}",
                        'display_name' => ucfirst(str_replace('_', ' ', $moduleKey)) . ' - ' . ucfirst($actionKey),
                        'display_name_ar' => "{$moduleNameAr} - {$actionNameAr}",
                        'description' => "Permission to {$actionKey} {$moduleKey}",
                        'description_ar' => "صلاحية {$actionNameAr} {$moduleNameAr}",
                        'module' => $moduleKey,
                        'module_ar' => $moduleNameAr,
                        'action' => $actionKey,
                        'action_ar' => $actionNameAr
                    ]
                );
            }
        }
    }

    public static function getPermissionsByModule(): array
    {
        return static::active()
                    ->orderBy('module')
                    ->orderBy('action')
                    ->get()
                    ->groupBy('module')
                    ->toArray();
    }
}