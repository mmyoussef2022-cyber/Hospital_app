<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:users.view');
    }

    /**
     * عرض قائمة الأدوار
     */
    public function index()
    {
        $roles = Role::with('permissions')
                    ->withCount('users')
                    ->orderBy('name')
                    ->paginate(15);

        $permissions = Permission::orderBy('name')->get();

        return view('roles.index', compact('roles', 'permissions'));
    }

    /**
     * عرض نموذج إنشاء دور جديد
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        
        return view('roles.create', compact('permissions'));
    }

    /**
     * حفظ دور جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null
            ]);

            if (!empty($validated['permissions'])) {
                $permissions = Permission::whereIn('id', $validated['permissions'])->get();
                $role->syncPermissions($permissions);
            }
        });

        return redirect()->route('roles.index')
                        ->with('success', 'تم إنشاء الدور بنجاح');
    }

    /**
     * عرض تفاصيل دور
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        
        return view('roles.show', compact('role'));
    }

    /**
     * عرض نموذج تعديل دور
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::orderBy('name')->get();
        
        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * تحديث دور
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::transaction(function () use ($validated, $role) {
            $role->update([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null
            ]);

            if (isset($validated['permissions'])) {
                $permissions = Permission::whereIn('id', $validated['permissions'])->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }
        });

        return redirect()->route('roles.index')
                        ->with('success', 'تم تحديث الدور بنجاح');
    }

    /**
     * حذف دور
     */
    public function destroy(Role $role)
    {
        // التحقق من عدم وجود مستخدمين مرتبطين بهذا الدور
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                            ->with('error', 'لا يمكن حذف هذا الدور لأنه مرتبط بمستخدمين');
        }

        $role->delete();

        return redirect()->route('roles.index')
                        ->with('success', 'تم حذف الدور بنجاح');
    }

    /**
     * إعطاء صلاحيات للدور
     */
    public function assignPermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $permissions = Permission::whereIn('id', $validated['permissions'])->get();
        $role->syncPermissions($permissions);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث صلاحيات الدور بنجاح'
        ]);
    }

    /**
     * إزالة صلاحية من الدور
     */
    public function revokePermission(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission);

        return response()->json([
            'success' => true,
            'message' => 'تم إزالة الصلاحية من الدور بنجاح'
        ]);
    }
}