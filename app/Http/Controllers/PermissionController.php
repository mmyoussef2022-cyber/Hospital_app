<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:users.view');
    }

    /**
     * عرض قائمة الصلاحيات
     */
    public function index()
    {
        $permissions = Permission::with('roles')
                                ->withCount('users')
                                ->orderBy('name')
                                ->paginate(20);

        $roles = Role::orderBy('name')->get();

        // تجميع الصلاحيات حسب الوحدة
        $groupedPermissions = $permissions->getCollection()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('permissions.index', compact('permissions', 'roles', 'groupedPermissions'));
    }

    /**
     * عرض نموذج إنشاء صلاحية جديدة
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        
        return view('permissions.create', compact('roles'));
    }

    /**
     * حفظ صلاحية جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'module' => 'required|string|max:100',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        DB::transaction(function () use ($validated) {
            $permission = Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null,
                'module' => $validated['module']
            ]);

            if (!empty($validated['roles'])) {
                $roles = Role::whereIn('id', $validated['roles'])->get();
                foreach ($roles as $role) {
                    $role->givePermissionTo($permission);
                }
            }
        });

        return redirect()->route('permissions.index')
                        ->with('success', 'تم إنشاء الصلاحية بنجاح');
    }

    /**
     * عرض تفاصيل صلاحية
     */
    public function show(Permission $permission)
    {
        $permission->load(['roles', 'users']);
        
        return view('permissions.show', compact('permission'));
    }

    /**
     * عرض نموذج تعديل صلاحية
     */
    public function edit(Permission $permission)
    {
        $permission->load('roles');
        $roles = Role::orderBy('name')->get();
        
        return view('permissions.edit', compact('permission', 'roles'));
    }

    /**
     * تحديث صلاحية
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'module' => 'required|string|max:100',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        DB::transaction(function () use ($validated, $permission) {
            $permission->update([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null,
                'module' => $validated['module']
            ]);

            // إزالة الصلاحية من جميع الأدوار أولاً
            $permission->roles()->detach();

            // إضافة الصلاحية للأدوار المحددة
            if (!empty($validated['roles'])) {
                $roles = Role::whereIn('id', $validated['roles'])->get();
                foreach ($roles as $role) {
                    $role->givePermissionTo($permission);
                }
            }
        });

        return redirect()->route('permissions.index')
                        ->with('success', 'تم تحديث الصلاحية بنجاح');
    }

    /**
     * حذف صلاحية
     */
    public function destroy(Permission $permission)
    {
        // التحقق من عدم وجود مستخدمين أو أدوار مرتبطة بهذه الصلاحية
        if ($permission->users()->count() > 0 || $permission->roles()->count() > 0) {
            return redirect()->route('permissions.index')
                            ->with('error', 'لا يمكن حذف هذه الصلاحية لأنها مرتبطة بمستخدمين أو أدوار');
        }

        $permission->delete();

        return redirect()->route('permissions.index')
                        ->with('success', 'تم حذف الصلاحية بنجاح');
    }

    /**
     * إعطاء صلاحيات متعددة لأدوار متعددة
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        DB::transaction(function () use ($validated) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $roles = Role::whereIn('id', $validated['roles'])->get();

            foreach ($roles as $role) {
                $role->syncPermissions($permissions);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الصلاحيات بنجاح'
        ]);
    }
}