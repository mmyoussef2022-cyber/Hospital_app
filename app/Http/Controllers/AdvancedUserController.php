<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\UserRole;
use App\Services\AdvancedPermissionService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdvancedUserController extends Controller
{
    protected $permissionService;
    protected $auditService;

    public function __construct(AdvancedPermissionService $permissionService, AuditLogService $auditService)
    {
        $this->permissionService = $permissionService;
        $this->auditService = $auditService;
        
        // تطبيق middleware للصلاحيات
        $this->middleware('auth');
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.edit')->only(['edit', 'update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
        $this->middleware('permission:users.manage')->only(['manageRoles', 'assignRole', 'revokeRole']);
    }

    /**
     * عرض قائمة المستخدمين مع الصلاحيات المتقدمة
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'department', 'userRoles.role', 'userRoles.department']);

        // فلترة حسب القسم
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // فلترة حسب الدور
        if ($request->filled('role_id')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('role_id', $request->role_id);
            });
        }

        // فلترة حسب حالة النشاط
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // البحث في الاسم أو البريد الإلكتروني
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);
        $departments = Department::active()->get();
        $roles = Role::all();

        return view('advanced-users.index', compact('users', 'departments', 'roles'));
    }

    /**
     * عرض تفاصيل مستخدم مع صلاحياته المتقدمة
     */
    public function show(User $user)
    {
        $user->load([
            'roles.permissions',
            'department',
            'userRoles' => function($query) {
                $query->with(['role', 'department'])->orderBy('assigned_at', 'desc');
            }
        ]);

        // الحصول على جميع الصلاحيات الفعالة
        $activePermissions = $this->permissionService->getUserActivePermissions($user);
        
        // الحصول على سجل الصلاحيات
        $permissionLogs = $this->auditService->getUserPermissionLogs($user, 20);

        // الحصول على الأدوار المتاحة للتعيين
        $availableRoles = $this->permissionService->getAvailableRolesForUser($user);

        return view('advanced-users.show', compact(
            'user', 
            'activePermissions', 
            'permissionLogs', 
            'availableRoles'
        ));
    }

    /**
     * إنشاء مستخدم جديد مع صلاحيات متقدمة
     */
    public function create()
    {
        $departments = Department::active()->get();
        $roles = Role::all();
        $specializations = $this->getSpecializations();

        return view('advanced-users.create', compact('departments', 'roles', 'specializations'));
    }

    /**
     * حفظ مستخدم جديد مع الصلاحيات
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'national_id' => 'required|string|unique:users',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'employee_id' => 'required|string|unique:users',
            'department_id' => 'required|exists:departments,id',
            'job_title' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'hire_date' => 'required|date',
            'employment_status' => 'required|in:active,inactive,terminated',
            'salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|array',
            'qualifications' => 'nullable|array',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'role_departments' => 'nullable|array',
            'role_departments.*' => 'nullable|exists:departments,id',
            'role_expires_at' => 'nullable|array',
            'role_expires_at.*' => 'nullable|date|after:today'
        ]);

        DB::transaction(function () use ($validated, $request) {
            // إنشاء المستخدم
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'national_id' => $validated['national_id'],
                'phone' => $validated['phone'],
                'mobile' => $validated['mobile'],
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'address' => $validated['address'],
                'employee_id' => $validated['employee_id'],
                'department_id' => $validated['department_id'],
                'job_title' => $validated['job_title'],
                'specialization' => $validated['specialization'],
                'license_number' => $validated['license_number'],
                'hire_date' => $validated['hire_date'],
                'employment_status' => $validated['employment_status'],
                'salary' => $validated['salary'],
                'emergency_contact' => $validated['emergency_contact'] ?? [],
                'qualifications' => $validated['qualifications'] ?? [],
                'is_active' => true
            ]);

            // تعيين الأدوار مع الصلاحيات المتقدمة
            foreach ($validated['roles'] as $index => $roleId) {
                $departmentId = $validated['role_departments'][$index] ?? null;
                $expiresAt = $validated['role_expires_at'][$index] ?? null;

                $this->permissionService->assignRoleToUser(
                    $user,
                    $roleId,
                    $departmentId,
                    $expiresAt ? \Carbon\Carbon::parse($expiresAt) : null,
                    'تعيين أولي عند إنشاء المستخدم'
                );
            }

            // تسجيل العملية في سجل المراجعة
            $this->auditService->logUserCreation($user, auth()->user());
        });

        return redirect()->route('advanced-users.index')
                        ->with('success', 'تم إنشاء المستخدم بنجاح مع الصلاحيات المحددة');
    }

    /**
     * تعديل صلاحيات المستخدم
     */
    public function edit(User $user)
    {
        $user->load(['roles', 'department', 'userRoles.role', 'userRoles.department']);
        
        $departments = Department::active()->get();
        $roles = Role::all();
        $specializations = $this->getSpecializations();

        return view('advanced-users.edit', compact('user', 'departments', 'roles', 'specializations'));
    }

    /**
     * تحديث المستخدم والصلاحيات
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'national_id' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'employee_id' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'department_id' => 'required|exists:departments,id',
            'job_title' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'hire_date' => 'required|date',
            'employment_status' => 'required|in:active,inactive,terminated',
            'salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|array',
            'qualifications' => 'nullable|array',
            'is_active' => 'required|boolean',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'role_departments' => 'nullable|array',
            'role_departments.*' => 'nullable|exists:departments,id',
            'role_expires_at' => 'nullable|array',
            'role_expires_at.*' => 'nullable|date|after:today'
        ]);

        DB::transaction(function () use ($validated, $request, $user) {
            // حفظ البيانات القديمة للمراجعة
            $oldData = $user->toArray();

            // تحديث بيانات المستخدم
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'national_id' => $validated['national_id'],
                'phone' => $validated['phone'],
                'mobile' => $validated['mobile'],
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'address' => $validated['address'],
                'employee_id' => $validated['employee_id'],
                'department_id' => $validated['department_id'],
                'job_title' => $validated['job_title'],
                'specialization' => $validated['specialization'],
                'license_number' => $validated['license_number'],
                'hire_date' => $validated['hire_date'],
                'employment_status' => $validated['employment_status'],
                'salary' => $validated['salary'],
                'emergency_contact' => $validated['emergency_contact'] ?? [],
                'qualifications' => $validated['qualifications'] ?? [],
                'is_active' => $validated['is_active']
            ];

            // تحديث كلمة المرور إذا تم إدخالها
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            // تحديث الأدوار
            $this->permissionService->syncUserRoles(
                $user,
                $validated['roles'],
                $validated['role_departments'] ?? [],
                $validated['role_expires_at'] ?? []
            );

            // تسجيل العملية في سجل المراجعة
            $this->auditService->logUserUpdate($user, $oldData, auth()->user());
        });

        return redirect()->route('advanced-users.show', $user)
                        ->with('success', 'تم تحديث المستخدم والصلاحيات بنجاح');
    }

    /**
     * إدارة أدوار المستخدم
     */
    public function manageRoles(User $user)
    {
        $user->load(['userRoles.role', 'userRoles.department']);
        
        $availableRoles = Role::all();
        $departments = Department::active()->get();

        return view('advanced-users.manage-roles', compact('user', 'availableRoles', 'departments'));
    }

    /**
     * تعيين دور جديد للمستخدم
     */
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'expires_at' => 'nullable|date|after:today',
            'reason' => 'required|string|max:500'
        ]);

        $this->permissionService->assignRoleToUser(
            $user,
            $validated['role_id'],
            $validated['department_id'],
            $validated['expires_at'] ? \Carbon\Carbon::parse($validated['expires_at']) : null,
            $validated['reason']
        );

        return redirect()->back()->with('success', 'تم تعيين الدور بنجاح');
    }

    /**
     * إلغاء دور من المستخدم
     */
    public function revokeRole(Request $request, User $user, UserRole $userRole)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $this->permissionService->revokeRoleFromUser(
            $user,
            $userRole,
            $validated['reason']
        );

        return redirect()->back()->with('success', 'تم إلغاء الدور بنجاح');
    }

    /**
     * تفويض صلاحيات مؤقتة
     */
    public function delegatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'delegate_to' => 'required|exists:users,id',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
            'expires_at' => 'required|date|after:now',
            'reason' => 'required|string|max:500'
        ]);

        $delegateToUser = User::findOrFail($validated['delegate_to']);

        $this->permissionService->delegatePermissions(
            $user,
            $delegateToUser,
            $validated['permissions'],
            \Carbon\Carbon::parse($validated['expires_at']),
            $validated['reason']
        );

        return redirect()->back()->with('success', 'تم تفويض الصلاحيات بنجاح');
    }

    /**
     * الحصول على التخصصات المتاحة
     */
    private function getSpecializations(): array
    {
        return [
            'internal_medicine' => 'طب باطني',
            'surgery' => 'جراحة عامة',
            'pediatrics' => 'طب أطفال',
            'obstetrics_gynecology' => 'نساء وولادة',
            'orthopedics' => 'عظام',
            'cardiology' => 'قلب',
            'neurology' => 'مخ وأعصاب',
            'dermatology' => 'جلدية',
            'ophthalmology' => 'عيون',
            'ent' => 'أنف وأذن وحنجرة',
            'urology' => 'مسالك بولية',
            'psychiatry' => 'طب نفسي',
            'radiology' => 'أشعة',
            'laboratory' => 'مختبر',
            'pharmacy' => 'صيدلة',
            'nursing' => 'تمريض',
            'administration' => 'إدارة',
            'reception' => 'استقبال',
            'cashier' => 'خزينة'
        ];
    }

    /**
     * تفعيل دور المستخدم
     */
    public function activateRole(User $user, UserRole $userRole)
    {
        if ($userRole->user_id !== $user->id) {
            return redirect()->back()->with('error', 'غير مصرح لك بهذا الإجراء.');
        }

        $userRole->update(['is_active' => true]);

        return redirect()->back()->with('success', 'تم تفعيل الدور بنجاح.');
    }

    /**
     * إلغاء تفعيل دور المستخدم
     */
    public function deactivateRole(User $user, UserRole $userRole)
    {
        if ($userRole->user_id !== $user->id) {
            return redirect()->back()->with('error', 'غير مصرح لك بهذا الإجراء.');
        }

        $userRole->update(['is_active' => false]);

        return redirect()->back()->with('success', 'تم إلغاء تفعيل الدور بنجاح.');
    }

    /**
     * حذف دور المستخدم
     */
    public function removeRole(User $user, UserRole $userRole)
    {
        if ($userRole->user_id !== $user->id) {
            return redirect()->back()->with('error', 'غير مصرح لك بهذا الإجراء.');
        }

        $userRole->delete();

        return redirect()->back()->with('success', 'تم حذف الدور بنجاح.');
    }
}