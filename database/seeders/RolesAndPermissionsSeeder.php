<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // إنشاء الصلاحيات الافتراضية
        $this->createPermissions();
        
        // إنشاء الأدوار الافتراضية
        $this->createRoles();
        
        // ربط الأدوار بالصلاحيات
        $this->assignPermissionsToRoles();
        
        // إنشاء المستخدم الإداري الافتراضي
        $this->createAdminUser();
    }

    private function createPermissions()
    {
        $modules = [
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

        $actions = [
            'view' => 'عرض',
            'create' => 'إنشاء',
            'edit' => 'تعديل',
            'delete' => 'حذف',
            'approve' => 'موافقة',
            'reject' => 'رفض',
            'export' => 'تصدير',
            'print' => 'طباعة',
            'manage' => 'إدارة'
        ];

        foreach ($modules as $moduleKey => $moduleNameAr) {
            foreach ($actions as $actionKey => $actionNameAr) {
                Permission::firstOrCreate([
                    'name' => "{$moduleKey}.{$actionKey}",
                    'name_ar' => "{$moduleNameAr}.{$actionNameAr}",
                    'display_name' => ucfirst(str_replace('_', ' ', $moduleKey)) . ' - ' . ucfirst($actionKey),
                    'display_name_ar' => "{$moduleNameAr} - {$actionNameAr}",
                    'description' => "Permission to {$actionKey} {$moduleKey}",
                    'description_ar' => "صلاحية {$actionNameAr} {$moduleNameAr}",
                    'module' => $moduleKey,
                    'module_ar' => $moduleNameAr,
                    'action' => $actionKey,
                    'action_ar' => $actionNameAr
                ]);
            }
        }
    }

    private function createRoles()
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
                'name' => 'nurse',
                'name_ar' => 'ممرض',
                'display_name' => 'Nurse',
                'display_name_ar' => 'ممرض',
                'description' => 'Patient care and medical assistance',
                'description_ar' => 'رعاية المرضى والمساعدة الطبية',
                'color' => '#20c997',
                'level' => 4
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
            ],
            [
                'name' => 'lab_technician',
                'name_ar' => 'موظف_مختبر',
                'display_name' => 'Lab Technician',
                'display_name_ar' => 'موظف مختبر',
                'description' => 'Laboratory tests and results',
                'description_ar' => 'التحاليل المخبرية والنتائج',
                'color' => '#6610f2',
                'level' => 4
            ],
            [
                'name' => 'radiology_technician',
                'name_ar' => 'موظف_أشعة',
                'display_name' => 'Radiology Technician',
                'display_name_ar' => 'موظف أشعة',
                'description' => 'Radiology imaging and reports',
                'description_ar' => 'التصوير الإشعاعي والتقارير',
                'color' => '#d63384',
                'level' => 4
            ],
            [
                'name' => 'pharmacy_staff',
                'name_ar' => 'موظف_صيدلية',
                'display_name' => 'Pharmacy Staff',
                'display_name_ar' => 'موظف صيدلية',
                'description' => 'Medication dispensing and management',
                'description_ar' => 'صرف الأدوية وإدارتها',
                'color' => '#ffc107',
                'level' => 4
            ]
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }
    }

    private function assignPermissionsToRoles()
    {
        // صلاحيات المدير العام - جميع الصلاحيات
        $superAdmin = Role::where('name', 'super_admin')->first();
        $allPermissions = Permission::all();
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));

        // صلاحيات مدير القسم
        $departmentManager = Role::where('name', 'department_manager')->first();
        $managerPermissions = Permission::whereIn('module', [
            'dashboard', 'patients', 'appointments', 'medical_records', 
            'reports', 'users', 'departments'
        ])->get();
        $departmentManager->permissions()->sync($managerPermissions->pluck('id'));

        // صلاحيات الطبيب
        $doctor = Role::where('name', 'doctor')->first();
        $doctorPermissions = Permission::whereIn('module', [
            'dashboard', 'patients', 'appointments', 'medical_records', 
            'prescriptions', 'laboratory', 'radiology'
        ])->whereIn('action', ['view', 'create', 'edit', 'print'])->get();
        $doctor->permissions()->sync($doctorPermissions->pluck('id'));

        // صلاحيات موظف الاستقبال
        $reception = Role::where('name', 'reception_staff')->first();
        $receptionPermissions = Permission::whereIn('module', [
            'dashboard', 'patients', 'appointments'
        ])->whereIn('action', ['view', 'create', 'edit'])->get();
        $reception->permissions()->sync($receptionPermissions->pluck('id'));

        // صلاحيات موظف الخزينة
        $cashier = Role::where('name', 'cashier')->first();
        $cashierPermissions = Permission::whereIn('module', [
            'dashboard', 'billing', 'cash_registers', 'reports'
        ])->whereIn('action', ['view', 'create', 'edit', 'print'])->get();
        $cashier->permissions()->sync($cashierPermissions->pluck('id'));

        // صلاحيات موظف المختبر
        $labTech = Role::where('name', 'lab_technician')->first();
        $labPermissions = Permission::whereIn('module', [
            'dashboard', 'laboratory', 'patients'
        ])->whereIn('action', ['view', 'create', 'edit', 'print'])->get();
        $labTech->permissions()->sync($labPermissions->pluck('id'));

        // صلاحيات موظف الأشعة
        $radioTech = Role::where('name', 'radiology_technician')->first();
        $radioPermissions = Permission::whereIn('module', [
            'dashboard', 'radiology', 'patients'
        ])->whereIn('action', ['view', 'create', 'edit', 'print'])->get();
        $radioTech->permissions()->sync($radioPermissions->pluck('id'));
    }

    private function createAdminUser()
    {
        $admin = User::firstOrCreate([
            'email' => 'admin@hospital-hms.com'
        ], [
            'name' => 'مدير النظام',
            'password' => bcrypt('password123'),
            'employee_id' => 'EMP001',
            'job_title' => 'مدير عام',
            'is_active' => true,
            'email_verified_at' => now()
        ]);

        // تعيين دور المدير العام
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole && !$admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }
    }
}