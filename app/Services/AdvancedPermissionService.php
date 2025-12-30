<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserRole;
use App\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdvancedPermissionService
{
    protected $auditService;

    public function __construct(AuditLogService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * الحصول على جميع الصلاحيات الفعالة للمستخدم
     */
    public function getUserActivePermissions(User $user): Collection
    {
        $cacheKey = "user_permissions_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            return $user->activeRoles()
                       ->with('permissions')
                       ->get()
                       ->pluck('permissions')
                       ->flatten()
                       ->unique('id')
                       ->values();
        });
    }

    /**
     * التحقق من صلاحية المستخدم مع مراعاة القسم والتخصص
     */
    public function checkUserPermission(User $user, string $permission, ?int $departmentId = null): bool
    {
        // المدير العام له صلاحية كاملة
        if ($user->isSuperAdmin()) {
            return true;
        }

        // التحقق من الصلاحية الأساسية
        if (!$user->hasPermission($permission)) {
            return false;
        }

        // إذا لم يتم تحديد قسم، فالصلاحية صالحة
        if (!$departmentId) {
            return true;
        }

        // التحقق من صلاحية القسم
        return $this->checkDepartmentPermission($user, $permission, $departmentId);
    }

    /**
     * التحقق من صلاحية القسم
     */
    public function checkDepartmentPermission(User $user, string $permission, int $departmentId): bool
    {
        // التحقق من وجود دور نشط في القسم المحدد
        $hasRoleInDepartment = $user->userRoles()
                                   ->active()
                                   ->where(function($query) use ($departmentId) {
                                       $query->where('department_id', $departmentId)
                                             ->orWhereNull('department_id'); // الأدوار العامة
                                   })
                                   ->whereHas('role.permissions', function($query) use ($permission) {
                                       $query->where('name', $permission);
                                   })
                                   ->exists();

        return $hasRoleInDepartment;
    }

    /**
     * تعيين دور للمستخدم مع إعدادات متقدمة
     */
    public function assignRoleToUser(
        User $user, 
        int $roleId, 
        ?int $departmentId = null, 
        ?Carbon $expiresAt = null, 
        string $reason = ''
    ): UserRole {
        $role = Role::findOrFail($roleId);

        // التحقق من عدم وجود نفس الدور في نفس القسم
        $existingRole = $user->userRoles()
                            ->where('role_id', $roleId)
                            ->where('department_id', $departmentId)
                            ->active()
                            ->first();

        if ($existingRole) {
            throw new \Exception('المستخدم لديه هذا الدور بالفعل في هذا القسم');
        }

        DB::transaction(function () use ($user, $role, $departmentId, $expiresAt, $reason) {
            // إنشاء تعيين الدور
            $userRole = UserRole::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'department_id' => $departmentId,
                'assigned_at' => now(),
                'expires_at' => $expiresAt,
                'is_active' => true
            ]);

            // تسجيل العملية
            $this->auditService->logRoleAssignment($user, $role, $departmentId, $expiresAt, $reason);

            // مسح cache الصلاحيات
            $this->clearUserPermissionsCache($user);

            return $userRole;
        });
    }

    /**
     * إلغاء دور من المستخدم
     */
    public function revokeRoleFromUser(User $user, UserRole $userRole, string $reason = ''): void
    {
        DB::transaction(function () use ($user, $userRole, $reason) {
            $role = $userRole->role;
            $departmentId = $userRole->department_id;

            // إلغاء تفعيل الدور
            $userRole->update(['is_active' => false]);

            // تسجيل العملية
            $this->auditService->logRoleRevocation($user, $role, $departmentId, $reason);

            // مسح cache الصلاحيات
            $this->clearUserPermissionsCache($user);
        });
    }

    /**
     * مزامنة أدوار المستخدم
     */
    public function syncUserRoles(User $user, array $roleIds, array $departmentIds = [], array $expiresAt = []): void
    {
        DB::transaction(function () use ($user, $roleIds, $departmentIds, $expiresAt) {
            // إلغاء تفعيل جميع الأدوار الحالية
            $user->userRoles()->update(['is_active' => false]);

            // تعيين الأدوار الجديدة
            foreach ($roleIds as $index => $roleId) {
                $departmentId = $departmentIds[$index] ?? null;
                $expires = $expiresAt[$index] ?? null;

                $this->assignRoleToUser(
                    $user,
                    $roleId,
                    $departmentId,
                    $expires ? Carbon::parse($expires) : null,
                    'مزامنة الأدوار'
                );
            }

            // مسح cache الصلاحيات
            $this->clearUserPermissionsCache($user);
        });
    }

    /**
     * تفويض صلاحيات مؤقتة
     */
    public function delegatePermissions(
        User $fromUser, 
        User $toUser, 
        array $permissionIds, 
        Carbon $expiresAt, 
        string $reason
    ): void {
        DB::transaction(function () use ($fromUser, $toUser, $permissionIds, $expiresAt, $reason) {
            // التحقق من أن المستخدم المفوض لديه الصلاحيات
            $userPermissions = $this->getUserActivePermissions($fromUser)->pluck('id')->toArray();
            $invalidPermissions = array_diff($permissionIds, $userPermissions);

            if (!empty($invalidPermissions)) {
                throw new \Exception('لا يمكن تفويض صلاحيات لا يملكها المستخدم');
            }

            // إنشاء دور مؤقت للتفويض
            $delegatedRole = Role::create([
                'name' => "delegated_role_{$fromUser->id}_{$toUser->id}_" . time(),
                'name_ar' => "دور_مفوض_{$fromUser->id}_{$toUser->id}_" . time(),
                'display_name' => "Delegated Role from {$fromUser->name}",
                'display_name_ar' => "دور مفوض من {$fromUser->name}",
                'description' => "Temporary delegated permissions",
                'description_ar' => "صلاحيات مفوضة مؤقتة",
                'color' => '#ffc107',
                'level' => 99, // مستوى منخفض للأدوار المفوضة
                'is_active' => true
            ]);

            // ربط الصلاحيات بالدور المؤقت
            $delegatedRole->permissions()->attach($permissionIds);

            // تعيين الدور المؤقت للمستخدم المفوض إليه
            $this->assignRoleToUser($toUser, $delegatedRole->id, null, $expiresAt, $reason);

            // تسجيل عملية التفويض
            $this->auditService->logPermissionDelegation($fromUser, $toUser, $permissionIds, $expiresAt, $reason);
        });
    }

    /**
     * الحصول على الأدوار المتاحة للتعيين للمستخدم
     */
    public function getAvailableRolesForUser(User $user): Collection
    {
        $currentRoleIds = $user->activeRoles()->pluck('roles.id')->toArray();
        
        return Role::active()
                   ->whereNotIn('id', $currentRoleIds)
                   ->orderBy('level')
                   ->get();
    }

    /**
     * الحصول على الصلاحيات حسب المودول
     */
    public function getPermissionsByModule(): array
    {
        return Permission::active()
                        ->orderBy('module')
                        ->orderBy('action')
                        ->get()
                        ->groupBy('module')
                        ->toArray();
    }

    /**
     * إنشاء صلاحيات ديناميكية حسب التخصص
     */
    public function createDynamicPermissions(string $specialization, array $modules): void
    {
        $actions = ['view', 'create', 'edit', 'delete', 'manage'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}.{$specialization}";
                
                Permission::firstOrCreate(
                    ['name' => $permissionName],
                    [
                        'name_ar' => "{$module}.{$action}.{$specialization}",
                        'display_name' => ucfirst($module) . ' - ' . ucfirst($action) . ' (' . ucfirst($specialization) . ')',
                        'display_name_ar' => "{$module} - {$action} ({$specialization})",
                        'description' => "Dynamic permission for {$specialization} to {$action} {$module}",
                        'description_ar' => "صلاحية ديناميكية لـ {$specialization} لـ {$action} {$module}",
                        'module' => $module,
                        'module_ar' => $module,
                        'action' => $action,
                        'action_ar' => $action,
                        'is_active' => true
                    ]
                );
            }
        }
    }

    /**
     * التحقق من انتهاء صلاحية الأدوار وإلغاء تفعيلها
     */
    public function checkAndExpireRoles(): int
    {
        $expiredRoles = UserRole::active()
                               ->whereNotNull('expires_at')
                               ->where('expires_at', '<=', now())
                               ->get();

        $count = 0;
        foreach ($expiredRoles as $userRole) {
            $userRole->update(['is_active' => false]);
            
            // تسجيل انتهاء الصلاحية
            $this->auditService->logRoleExpiration($userRole);
            
            // مسح cache الصلاحيات
            $this->clearUserPermissionsCache($userRole->user);
            
            $count++;
        }

        return $count;
    }

    /**
     * الحصول على إحصائيات الصلاحيات
     */
    public function getPermissionStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_roles' => Role::count(),
            'active_roles' => Role::active()->count(),
            'total_permissions' => Permission::count(),
            'active_permissions' => Permission::active()->count(),
            'expired_roles' => UserRole::expired()->count(),
            'temporary_roles' => UserRole::active()->whereNotNull('expires_at')->count(),
            'users_by_department' => User::join('departments', 'users.department_id', '=', 'departments.id')
                                        ->selectRaw('departments.name_ar as department, COUNT(*) as count')
                                        ->groupBy('departments.id', 'departments.name_ar')
                                        ->get()
                                        ->toArray()
        ];
    }

    /**
     * مسح cache الصلاحيات للمستخدم
     */
    public function clearUserPermissionsCache(User $user): void
    {
        Cache::forget("user_permissions_{$user->id}");
    }

    /**
     * مسح جميع cache الصلاحيات
     */
    public function clearAllPermissionsCache(): void
    {
        $users = User::pluck('id');
        foreach ($users as $userId) {
            Cache::forget("user_permissions_{$userId}");
        }
    }

    /**
     * التحقق من محاولات الوصول غير المصرح بها
     */
    public function checkUnauthorizedAccess(User $user, string $permission, string $route): void
    {
        if (!$this->checkUserPermission($user, $permission)) {
            // تسجيل محاولة الوصول غير المصرح بها
            $this->auditService->logUnauthorizedAccess($user, $permission, $route);
            
            // إرسال تنبيه أمني إذا تكررت المحاولات
            $recentAttempts = $this->auditService->getRecentUnauthorizedAttempts($user, 60); // آخر ساعة
            
            if ($recentAttempts >= 5) {
                $this->auditService->sendSecurityAlert($user, $recentAttempts);
            }
        }
    }
}