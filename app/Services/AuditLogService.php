<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserRole;
use App\Models\PermissionLog;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

class AuditLogService
{
    /**
     * تسجيل إنشاء مستخدم جديد
     */
    public function logUserCreation(User $user, User $createdBy): void
    {
        $this->createLog([
            'user_id' => $user->id,
            'action' => 'user_created',
            'permission_type' => 'user',
            'permission_name' => 'user.create',
            'new_data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'department_id' => $user->department_id,
                'job_title' => $user->job_title,
                'specialization' => $user->specialization
            ],
            'granted_by' => $createdBy->id,
            'reason' => 'إنشاء مستخدم جديد في النظام'
        ]);
    }

    /**
     * تسجيل تحديث بيانات المستخدم
     */
    public function logUserUpdate(User $user, array $oldData, User $updatedBy): void
    {
        $changes = [];
        $newData = $user->toArray();

        // تحديد التغييرات
        foreach ($newData as $key => $value) {
            if (isset($oldData[$key]) && $oldData[$key] !== $value) {
                $changes[$key] = [
                    'old' => $oldData[$key],
                    'new' => $value
                ];
            }
        }

        if (!empty($changes)) {
            $this->createLog([
                'user_id' => $user->id,
                'action' => 'user_updated',
                'permission_type' => 'user',
                'permission_name' => 'user.edit',
                'old_data' => $changes,
                'new_data' => $newData,
                'granted_by' => $updatedBy->id,
                'reason' => 'تحديث بيانات المستخدم'
            ]);
        }
    }

    /**
     * تسجيل تعيين دور للمستخدم
     */
    public function logRoleAssignment(
        User $user, 
        Role $role, 
        ?int $departmentId, 
        ?Carbon $expiresAt, 
        string $reason
    ): void {
        $this->createLog([
            'user_id' => $user->id,
            'action' => 'role_assigned',
            'permission_type' => 'role',
            'permission_name' => $role->name,
            'new_data' => [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'role_display_name' => $role->display_name,
                'department_id' => $departmentId,
                'expires_at' => $expiresAt?->toDateTimeString(),
                'assigned_at' => now()->toDateTimeString()
            ],
            'granted_by' => auth()->id(),
            'reason' => $reason
        ]);
    }

    /**
     * تسجيل إلغاء دور من المستخدم
     */
    public function logRoleRevocation(User $user, Role $role, ?int $departmentId, string $reason): void
    {
        $this->createLog([
            'user_id' => $user->id,
            'action' => 'role_revoked',
            'permission_type' => 'role',
            'permission_name' => $role->name,
            'old_data' => [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'role_display_name' => $role->display_name,
                'department_id' => $departmentId,
                'revoked_at' => now()->toDateTimeString()
            ],
            'granted_by' => auth()->id(),
            'reason' => $reason
        ]);
    }

    /**
     * تسجيل انتهاء صلاحية دور
     */
    public function logRoleExpiration(UserRole $userRole): void
    {
        $this->createLog([
            'user_id' => $userRole->user_id,
            'action' => 'role_expired',
            'permission_type' => 'role',
            'permission_name' => $userRole->role->name,
            'old_data' => [
                'role_id' => $userRole->role_id,
                'role_name' => $userRole->role->name,
                'department_id' => $userRole->department_id,
                'expires_at' => $userRole->expires_at->toDateTimeString(),
                'expired_at' => now()->toDateTimeString()
            ],
            'reason' => 'انتهاء صلاحية الدور تلقائياً'
        ]);
    }

    /**
     * تسجيل تفويض الصلاحيات
     */
    public function logPermissionDelegation(
        User $fromUser, 
        User $toUser, 
        array $permissionIds, 
        Carbon $expiresAt, 
        string $reason
    ): void {
        $permissions = Permission::whereIn('id', $permissionIds)->get();

        $this->createLog([
            'user_id' => $toUser->id,
            'action' => 'permissions_delegated',
            'permission_type' => 'delegation',
            'permission_name' => 'delegated_permissions',
            'new_data' => [
                'from_user_id' => $fromUser->id,
                'from_user_name' => $fromUser->name,
                'to_user_id' => $toUser->id,
                'to_user_name' => $toUser->name,
                'permissions' => $permissions->pluck('name')->toArray(),
                'expires_at' => $expiresAt->toDateTimeString(),
                'delegated_at' => now()->toDateTimeString()
            ],
            'granted_by' => $fromUser->id,
            'reason' => $reason
        ]);
    }

    /**
     * تسجيل محاولة وصول غير مصرح بها
     */
    public function logUnauthorizedAccess(User $user, string $permission, string $route): void
    {
        $this->createLog([
            'user_id' => $user->id,
            'action' => 'unauthorized_access',
            'permission_type' => 'security',
            'permission_name' => $permission,
            'new_data' => [
                'route' => $route,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'attempted_at' => now()->toDateTimeString(),
                'user_roles' => $user->activeRoles()->pluck('name')->toArray()
            ],
            'reason' => 'محاولة وصول غير مصرح بها'
        ]);
    }

    /**
     * تسجيل تسجيل دخول المستخدم
     */
    public function logUserLogin(User $user): void
    {
        $this->createLog([
            'user_id' => $user->id,
            'action' => 'user_login',
            'permission_type' => 'authentication',
            'permission_name' => 'login',
            'new_data' => [
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'login_at' => now()->toDateTimeString()
            ],
            'reason' => 'تسجيل دخول المستخدم'
        ]);
    }

    /**
     * تسجيل تسجيل خروج المستخدم
     */
    public function logUserLogout(User $user): void
    {
        $this->createLog([
            'user_id' => $user->id,
            'action' => 'user_logout',
            'permission_type' => 'authentication',
            'permission_name' => 'logout',
            'new_data' => [
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'logout_at' => now()->toDateTimeString()
            ],
            'reason' => 'تسجيل خروج المستخدم'
        ]);
    }

    /**
     * الحصول على سجل الصلاحيات للمستخدم
     */
    public function getUserPermissionLogs(User $user, int $limit = 50): Collection
    {
        return PermissionLog::where('user_id', $user->id)
                           ->with('grantedBy')
                           ->orderBy('created_at', 'desc')
                           ->limit($limit)
                           ->get();
    }

    /**
     * الحصول على محاولات الوصول غير المصرح بها الأخيرة
     */
    public function getRecentUnauthorizedAttempts(User $user, int $minutes = 60): int
    {
        return PermissionLog::where('user_id', $user->id)
                           ->where('action', 'unauthorized_access')
                           ->where('created_at', '>=', now()->subMinutes($minutes))
                           ->count();
    }

    /**
     * إرسال تنبيه أمني
     */
    public function sendSecurityAlert(User $user, int $attemptCount): void
    {
        // الحصول على المديرين والمسؤولين عن الأمان
        $admins = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['super_admin', 'security_admin']);
        })->get();

        foreach ($admins as $admin) {
            $admin->notify(new SecurityAlertNotification($user, $attemptCount));
        }

        // تسجيل إرسال التنبيه
        $this->createLog([
            'user_id' => $user->id,
            'action' => 'security_alert_sent',
            'permission_type' => 'security',
            'permission_name' => 'security_alert',
            'new_data' => [
                'attempt_count' => $attemptCount,
                'alert_sent_at' => now()->toDateTimeString(),
                'notified_admins' => $admins->pluck('id')->toArray()
            ],
            'reason' => "إرسال تنبيه أمني بسبب {$attemptCount} محاولات وصول غير مصرح بها"
        ]);
    }

    /**
     * الحصول على إحصائيات الأمان
     */
    public function getSecurityStatistics(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_logs' => PermissionLog::where('created_at', '>=', $startDate)->count(),
            'unauthorized_attempts' => PermissionLog::where('action', 'unauthorized_access')
                                                   ->where('created_at', '>=', $startDate)
                                                   ->count(),
            'role_assignments' => PermissionLog::where('action', 'role_assigned')
                                              ->where('created_at', '>=', $startDate)
                                              ->count(),
            'role_revocations' => PermissionLog::where('action', 'role_revoked')
                                              ->where('created_at', '>=', $startDate)
                                              ->count(),
            'user_logins' => PermissionLog::where('action', 'user_login')
                                         ->where('created_at', '>=', $startDate)
                                         ->count(),
            'security_alerts' => PermissionLog::where('action', 'security_alert_sent')
                                             ->where('created_at', '>=', $startDate)
                                             ->count(),
            'top_unauthorized_users' => PermissionLog::where('action', 'unauthorized_access')
                                                    ->where('created_at', '>=', $startDate)
                                                    ->select('user_id', DB::raw('COUNT(*) as attempts'))
                                                    ->groupBy('user_id')
                                                    ->orderBy('attempts', 'desc')
                                                    ->limit(10)
                                                    ->with('user:id,name,email')
                                                    ->get(),
            'daily_activity' => PermissionLog::where('created_at', '>=', $startDate)
                                            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                                            ->groupBy(DB::raw('DATE(created_at)'))
                                            ->orderBy('date')
                                            ->get()
        ];
    }

    /**
     * تنظيف السجلات القديمة
     */
    public function cleanupOldLogs(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return PermissionLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * تصدير سجلات المراجعة
     */
    public function exportAuditLogs(array $filters = []): Collection
    {
        $query = PermissionLog::with(['user', 'grantedBy']);

        // تطبيق الفلاتر
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * إنشاء سجل جديد
     */
    private function createLog(array $data): void
    {
        PermissionLog::create(array_merge($data, [
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]));
    }
}