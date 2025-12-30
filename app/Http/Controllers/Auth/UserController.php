<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.edit')->only(['edit', 'update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
        $this->middleware('permission:users.manage_roles')->only(['assignRole', 'removeRole']);
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['department', 'roles']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->paginate(15);
        $departments = Department::where('is_active', true)->get();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'departments', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $roles = Role::all();
        
        return view('admin.users.create', compact('departments', 'roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'national_id' => 'required|string|size:10|unique:users,national_id',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string',
            'employee_id' => 'nullable|string|max:20|unique:users,employee_id',
            'department_id' => 'nullable|exists:departments,id',
            'job_title' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:50',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name'
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'national_id.required' => 'الرقم الوطني مطلوب',
            'national_id.size' => 'الرقم الوطني يجب أن يكون 10 أرقام',
            'national_id.unique' => 'الرقم الوطني مستخدم بالفعل',
            'employee_id.unique' => 'رقم الموظف مستخدم بالفعل'
        ]);

        // Encrypt sensitive data
        $validated['password'] = Hash::make($validated['password']);
        $validated['national_id'] = encrypt($validated['national_id']);

        $user = User::create($validated);

        // Assign roles
        if ($request->filled('roles')) {
            $user->assignRole($request->roles);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['department', 'roles', 'permissions']);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $departments = Department::where('is_active', true)->get();
        $roles = Role::all();
        
        return view('admin.users.edit', compact('user', 'departments', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'national_id' => ['required', 'string', 'size:10', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string',
            'employee_id' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'department_id' => 'nullable|exists:departments,id',
            'job_title' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:50',
            'hire_date' => 'nullable|date',
            'employment_status' => 'in:active,inactive,suspended,terminated',
            'salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name'
        ]);

        // Handle password update
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Encrypt national ID if changed
        if ($validated['national_id'] !== decrypt($user->national_id)) {
            $validated['national_id'] = encrypt($validated['national_id']);
        } else {
            unset($validated['national_id']);
        }

        $user->update($validated);

        // Update roles
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting super admin
        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'لا يمكن حذف مدير النظام الرئيسي');
        }

        // Soft delete by deactivating
        $user->update(['is_active' => false, 'employment_status' => 'terminated']);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم إلغاء تفعيل المستخدم بنجاح');
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->assignRole($request->role);

        return back()->with('success', 'تم تعيين الدور بنجاح');
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->removeRole($request->role);

        return back()->with('success', 'تم إزالة الدور بنجاح');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';
        
        return back()->with('success', "{$status} المستخدم بنجاح");
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user)
    {
        $newPassword = 'password123'; // In production, generate a random password
        $user->update(['password' => Hash::make($newPassword)]);

        // In production, send the new password via email or SMS
        
        return back()->with('success', 'تم إعادة تعيين كلمة المرور بنجاح');
    }
}
