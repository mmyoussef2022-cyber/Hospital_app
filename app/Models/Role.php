<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'display_name',
        'display_name_ar',
        'description',
        'description_ar',
        'color',
        'level',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer'
    ];

    // العلاقات
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
                   ->withPivot(['department_id', 'assigned_at', 'expires_at', 'is_active'])
                   ->withTimestamps();
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    // الخصائص المحسوبة
    public function getDisplayNameAttribute(): string
    {
        $displayName = app()->getLocale() === 'ar' 
            ? ($this->display_name_ar ?? $this->attributes['display_name'] ?? $this->name)
            : ($this->attributes['display_name'] ?? $this->name);
        
        return $displayName ?? $this->name ?? 'غير محدد';
    }

    public function getDescriptionAttribute(): string
    {
        $description = app()->getLocale() === 'ar' 
            ? ($this->description_ar ?? $this->attributes['description'] ?? '')
            : ($this->attributes['description'] ?? '');
        
        return $description ?? '';
    }

    public function getNameAttribute(): string
    {
        $name = app()->getLocale() === 'ar' ? $this->name_ar : $this->attributes['name'];
        return $name ?? $this->attributes['name'] ?? '';
    }

    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', '>=', $level);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->whereHas('users', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    // الدوال المساعدة
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    public function grantPermission(Permission $permission): void
    {
        if (!$this->hasPermission($permission->name)) {
            $this->permissions()->attach($permission->id);
        }
    }

    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    public function getPermissionsByModule(): array
    {
        return $this->permissions()
                   ->select('module', 'module_ar', 'name', 'name_ar', 'action', 'action_ar')
                   ->get()
                   ->groupBy('module')
                   ->toArray();
    }

    public function canAccessModule(string $module): bool
    {
        return $this->permissions()->where('module', $module)->exists();
    }

    public function canPerformAction(string $module, string $action): bool
    {
        return $this->permissions()
                   ->where('module', $module)
                   ->where('action', $action)
                   ->exists();
    }

    // الدوال الثابتة
    public static function getSuperAdminRole(): ?Role
    {
        return static::where('name', 'super_admin')->first();
    }

    public static function getDefaultRoles(): array
    {
        return [
            'super_admin' => 'مدير عام',
            'department_manager' => 'مدير قسم',
            'doctor' => 'طبيب',
            'nurse' => 'ممرض',
            'reception_staff' => 'موظف استقبال',
            'cashier' => 'موظف خزينة',
            'lab_technician' => 'موظف مختبر',
            'radiology_technician' => 'موظف أشعة',
            'pharmacy_staff' => 'موظف صيدلية'
        ];
    }

    public static function createDefaultRoles(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'name_ar' => 'مدير_عام',
                'display_name' => 'Super Administrator',
                'display_name_ar' => 'مدير عام',
                'description' => 'Full system access and control',
                'description_ar' => 'صلاحية كاملة للنظام',
                'color' => '#dc3545',
                'level' => 1
            ],
            [
                'name' => 'department_manager',
                'name_ar' => 'مدير_قسم',
                'display_name' => 'Department Manager',
                'display_name_ar' => 'مدير قسم',
                'description' => 'Manage department operations',
                'description_ar' => 'إدارة عمليات القسم',
                'color' => '#fd7e14',
                'level' => 2
            ],
            [
                'name' => 'doctor',
                'name_ar' => 'طبيب',
                'display_name' => 'Doctor',
                'display_name_ar' => 'طبيب',
                'description' => 'Medical consultations and treatments',
                'description_ar' => 'الاستشارات الطبية والعلاج',
                'color' => '#198754',
                'level' => 3
            ],
            [
                'name' => 'reception_staff',
                'name_ar' => 'موظف_استقبال',
                'display_name' => 'Reception Staff',
                'display_name_ar' => 'موظف استقبال',
                'description' => 'Patient registration and appointments',
                'description_ar' => 'تسجيل المرضى والمواعيد',
                'color' => '#0dcaf0',
                'level' => 4
            ],
            [
                'name' => 'cashier',
                'name_ar' => 'موظف_خزينة',
                'display_name' => 'Cashier',
                'display_name_ar' => 'موظف خزينة',
                'description' => 'Financial transactions and billing',
                'description_ar' => 'المعاملات المالية والفواتير',
                'color' => '#6f42c1',
                'level' => 4
            ]
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }
    }
}