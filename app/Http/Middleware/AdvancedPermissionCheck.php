<?php

namespace App\Http\Middleware;

use App\Services\AdvancedPermissionService;
use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvancedPermissionCheck
{
    protected $permissionService;
    protected $auditService;

    public function __construct(AdvancedPermissionService $permissionService, AuditLogService $auditService)
    {
        $this->permissionService = $permissionService;
        $this->auditService = $auditService;
    }

    /**
     * Handle an incoming request with advanced permission checking.
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $departmentParam = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $route = $request->route()->getName() ?? $request->path();

        // الحصول على معرف القسم من المعاملات إذا تم تحديده
        $departmentId = null;
        if ($departmentParam && $request->has($departmentParam)) {
            $departmentId = $request->get($departmentParam);
        }

        // التحقق من الصلاحية المتقدمة
        if (!$this->permissionService->checkUserPermission($user, $permission, $departmentId)) {
            // تسجيل محاولة الوصول غير المصرح بها
            $this->auditService->logUnauthorizedAccess($user, $permission, $route);
            
            // التحقق من محاولات الوصول غير المصرح بها
            $this->permissionService->checkUnauthorizedAccess($user, $permission, $route);
            
            // إرجاع خطأ 403
            abort(403, 'ليس لديك صلاحية للوصول إلى هذه الصفحة أو القسم المحدد');
        }

        // تسجيل الوصول الناجح للعمليات الحساسة
        if ($this->isSensitiveOperation($permission)) {
            $this->auditService->createLog([
                'user_id' => $user->id,
                'action' => 'sensitive_access',
                'permission_type' => 'access',
                'permission_name' => $permission,
                'new_data' => [
                    'route' => $route,
                    'department_id' => $departmentId,
                    'accessed_at' => now()->toDateTimeString()
                ],
                'reason' => 'وصول ناجح لعملية حساسة'
            ]);
        }

        return $next($request);
    }

    /**
     * تحديد ما إذا كانت العملية حساسة تتطلب تسجيل إضافي
     */
    private function isSensitiveOperation(string $permission): bool
    {
        $sensitiveOperations = [
            'users.create',
            'users.edit',
            'users.delete',
            'users.manage',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            'financial.manage',
            'reports.export',
            'settings.manage'
        ];

        return in_array($permission, $sensitiveOperations);
    }
}