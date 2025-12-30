<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Department;
use Illuminate\Database\Seeder;

class AdvancedRolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->createAdvancedPermissions();
        $this->createAdvancedRoles();
        $this->assignPermissionsToRoles();
    }

    /**
     * إنشاء الصلاحيات المتقدمة
     */
    private function createAdvancedPermissions()
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
            'settings' => 'الإعدادات',
            'audit' => 'سجلات المراجعة',
            'security' => 'الأمان',
            'notifications' => 'الإشعارات',
            'workflows' => 'تدفق العمل'
        ];

        $actions = [
            'view' => 'عرض',
            'create' => 'إنشاء',
            'edit' => 'تعديل',
            'delete' => 'حذف',
            'approve' => 'موافقة',
            'reject' => 'رفض',
            'export' => 'تصدير',
            'import' => 'استيراد',
            'print' => 'طباعة',
            'manage' => 'إدارة',
            'assign' => 'تعيين',
            'revoke' => 'إلغاء',
            'delegate' => 'تفويض',
            'audit' => 'مراجعة'
        ];

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
                        'action_ar' => $actionNameAr,
                        'is_active' => true
                    ]
                );
            }
        }

        // صلاحيات خاصة للأنظمة المتقدمة
        $specialPermissions = [
            [
                'name' => 'system.full_access',
                'name_ar' => 'النظام.وصول_كامل',
                'display_name' => 'Full System Access',
                'display_name_ar' => 'وصول كامل للنظام',
                'description' => 'Complete access to all system functions',
                'description_ar' => 'وصول كامل لجميع وظائف النظام',
                'module' => 'system',
                'module_ar' => 'النظام',
                'action' => 'full_access',
                'action_ar' => 'وصول_كامل'
            ],
            [
                'name' => 'emergency.override',
                'name_ar' => 'الطوارئ.تجاوز',
                'display_name' => 'Emergency Override',
                'display_name_ar' => 'تجاوز الطوارئ',
                'description' => 'Override permissions in emergency situations',
                'description_ar' => 'تجاوز الصلاحيات في حالات الطوارئ',
                'module' => 'emergency',
                'module_ar' => 'الطوارئ',
                'action' => 'override',
                'action_ar' => 'تجاوز'
            ],
            [
                'name' => 'delegation.temporary',
                'name_ar' => 'التفويض.مؤقت',
                'display_name' => 'Temporary Delegation',
                'display_name_ar' => 'تفويض مؤقت',
                'description' => 'Delegate permissions temporarily to other users',
                'description_ar' => 'تفويض الصلاحيات مؤقتاً للمستخدمين الآخرين',
                'module' => 'delegation',
                'module_ar' => 'التفويض',
                'action' => 'temporary',
                'action_ar' => 'مؤقت'
            ]
        ];

        foreach ($specialPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }

    /**
     * إنشاء الأدوار المتقدمة
     */
    private function createAdvancedRoles()
    {
        $roles = [
            [
                'name' => 'super_admin',
                'name_ar' => 'مدير_عام',
                'display_name' => 'Super Administrator',
                'display_name_ar' => 'مدير عام',
                'description' => 'Complete system access and control',
                'description_ar' => 'صلاحية كاملة للنظام والتحكم',
                'color' => '#dc3545',
                'level' => 1
            ],
            [
                'name' => 'hospital_director',
                'name_ar' => 'مدير_المستشفى',
                'display_name' => 'Hospital Director',
                'display_name_ar' => 'مدير المستشفى',
                'description' => 'Hospital management and oversight',
                'description_ar' => 'إدارة المستشفى والإشراف العام',
                'color' => '#fd7e14',
                'level' => 2
            ],
            [
                'name' => 'department_manager',
                'name_ar' => 'مدير_قسم',
                'display_name' => 'Department Manager',
                'display_name_ar' => 'مدير قسم',
                'description' => 'Manage specific department operations',
                'description_ar' => 'إدارة عمليات قسم محدد',
                'color' => '#fd7e14',
                'level' => 3
            ],
            [
                'name' => 'senior_doctor',
                'name_ar' => 'طبيب_أول',
                'display_name' => 'Senior Doctor',
                'display_name_ar' => 'طبيب أول',
                'description' => 'Senior medical staff with extended privileges',
                'description_ar' => 'طاقم طبي أول مع صلاحيات موسعة',
                'color' => '#198754',
                'level' => 4
            ],
            [
                'name' => 'doctor',
                'name_ar' => 'طبيب',
                'display_name' => 'Doctor',
                'display_name_ar' => 'طبيب',
                'description' => 'Medical consultations and treatments',
                'description_ar' => 'الاستشارات الطبية والعلاج',
                'color' => '#198754',
                'level' => 5
            ],
            [
                'name' => 'head_nurse',
                'name_ar' => 'رئيس_تمريض',
                'display_name' => 'Head Nurse',
                'display_name_ar' => 'رئيس تمريض',
                'description' => 'Nursing supervision and coordination',
                'description_ar' => 'إشراف وتنسيق التمريض',
                'color' => '#20c997',
                'level' => 6
            ],
            [
                'name' => 'nurse',
                'name_ar' => 'ممرض',
                'display_name' => 'Nurse',
                'display_name_ar' => 'ممرض',
                'description' => 'Patient care and medical assistance',
                'description_ar' => 'رعاية المرضى والمساعدة الطبية',
                'color' => '#20c997',
                'level' => 7
            ],
            [
                'name' => 'reception_supervisor',
                'name_ar' => 'مشرف_استقبال',
                'display_name' => 'Reception Supervisor',
                'display_name_ar' => 'مشرف استقبال',
                'description' => 'Supervise reception operations',
                'description_ar' => 'إشراف على عمليات الاستقبال',
                'color' => '#0dcaf0',
                'level' => 8
            ],
            [
                'name' => 'reception_staff',
                'name_ar' => 'موظف_استقبال',
                'display_name' => 'Reception Staff',
                'display_name_ar' => 'موظف استقبال',
                'description' => 'Patient registration and appointments',
                'description_ar' => 'تسجيل المرضى والمواعيد',
                'color' => '#0dcaf0',
                'level' => 9
            ],
            [
                'name' => 'cashier_supervisor',
                'name_ar' => 'مشرف_خزينة',
                'display_name' => 'Cashier Supervisor',
                'display_name_ar' => 'مشرف خزينة',
                'description' => 'Supervise financial operations',
                'description_ar' => 'إشراف على العمليات المالية',
                'color' => '#6f42c1',
                'level' => 8
            ],
            [
                'name' => 'cashier',
                'name_ar' => 'موظف_خزينة',
                'display_name' => 'Cashier',
                'display_name_ar' => 'موظف خزينة',
                'description' => 'Financial transactions and billing',
                'description_ar' => 'المعاملات المالية والفواتير',
                'color' => '#6f42c1',
                'level' => 9
            ],
            [
                'name' => 'lab_supervisor',
                'name_ar' => 'مشرف_مختبر',
                'display_name' => 'Lab Supervisor',
                'display_name_ar' => 'مشرف مختبر',
                'description' => 'Supervise laboratory operations',
                'description_ar' => 'إشراف على عمليات المختبر',
                'color' => '#e83e8c',
                'level' => 8
            ],
            [
                'name' => 'lab_technician',
                'name_ar' => 'فني_مختبر',
                'display_name' => 'Lab Technician',
                'display_name_ar' => 'فني مختبر',
                'description' => 'Laboratory tests and analysis',
                'description_ar' => 'الفحوصات المختبرية والتحليل',
                'color' => '#e83e8c',
                'level' => 9
            ],
            [
                'name' => 'radiology_supervisor',
                'name_ar' => 'مشرف_أشعة',
                'display_name' => 'Radiology Supervisor',
                'display_name_ar' => 'مشرف أشعة',
                'description' => 'Supervise radiology operations',
                'description_ar' => 'إشراف على عمليات الأشعة',
                'color' => '#6610f2',
                'level' => 8
            ],
            [
                'name' => 'radiology_technician',
                'name_ar' => 'فني_أشعة',
                'display_name' => 'Radiology Technician',
                'display_name_ar' => 'فني أشعة',
                'description' => 'Radiology imaging and reports',
                'description_ar' => 'التصوير الإشعاعي والتقارير',
                'color' => '#6610f2',
                'level' => 9
            ],
            [
                'name' => 'pharmacy_supervisor',
                'name_ar' => 'مشرف_صيدلية',
                'display_name' => 'Pharmacy Supervisor',
                'display_name_ar' => 'مشرف صيدلية',
                'description' => 'Supervise pharmacy operations',
                'description_ar' => 'إشراف على عمليات الصيدلية',
                'color' => '#17a2b8',
                'level' => 8
            ],
            [
                'name' => 'pharmacist',
                'name_ar' => 'صيدلي',
                'display_name' => 'Pharmacist',
                'display_name_ar' => 'صيدلي',
                'description' => 'Medication dispensing and consultation',
                'description_ar' => 'صرف الأدوية والاستشارة الدوائية',
                'color' => '#17a2b8',
                'level' => 9
            ],
            [
                'name' => 'security_admin',
                'name_ar' => 'مدير_أمان',
                'display_name' => 'Security Administrator',
                'display_name_ar' => 'مدير أمان',
                'description' => 'System security and audit management',
                'description_ar' => 'إدارة أمان النظام والمراجعة',
                'color' => '#dc3545',
                'level' => 3
            ],
            [
                'name' => 'it_support',
                'name_ar' => 'دعم_تقني',
                'display_name' => 'IT Support',
                'display_name_ar' => 'دعم تقني',
                'description' => 'Technical support and system maintenance',
                'description_ar' => 'الدعم التقني وصيانة النظام',
                'color' => '#6c757d',
                'level' => 10
            ]
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }
    }

    /**
     * تعيين الصلاحيات للأدوار
     */
    private function assignPermissionsToRoles()
    {
        // المدير العام - جميع الصلاحيات
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $allPermissions = Permission::pluck('id');
            $superAdmin->permissions()->sync($allPermissions);
        }

        // مدير المستشفى - معظم الصلاحيات عدا إدارة النظام
        $hospitalDirector = Role::where('name', 'hospital_director')->first();
        if ($hospitalDirector) {
            $permissions = Permission::whereNotIn('module', ['system', 'settings', 'security'])
                                   ->pluck('id');
            $hospitalDirector->permissions()->sync($permissions);
        }

        // مدير القسم - صلاحيات القسم المحدد
        $departmentManager = Role::where('name', 'department_manager')->first();
        if ($departmentManager) {
            $permissions = Permission::whereIn('module', [
                'dashboard', 'patients', 'appointments', 'medical_records',
                'prescriptions', 'reports', 'users'
            ])->whereIn('action', ['view', 'create', 'edit', 'manage', 'approve'])
            ->pluck('id');
            $departmentManager->permissions()->sync($permissions);
        }

        // الطبيب الأول - صلاحيات طبية موسعة
        $seniorDoctor = Role::where('name', 'senior_doctor')->first();
        if ($seniorDoctor) {
            $permissions = Permission::whereIn('module', [
                'dashboard', 'patients', 'appointments', 'medical_records',
                'prescriptions', 'laboratory', 'radiology', 'surgeries'
            ])->whereIn('action', ['view', 'create', 'edit', 'approve', 'manage'])
            ->pluck('id');
            $seniorDoctor->permissions()->sync($permissions);
        }

        // الطبيب - صلاحيات طبية أساسية
        $doctor = Role::where('name', 'doctor')->first();
        if ($doctor) {
            $permissions = Permission::whereIn('module', [
                'dashboard', 'patients', 'appointments', 'medical_records',
                'prescriptions', 'laboratory', 'radiology'
            ])->whereIn('action', ['view', 'create', 'edit'])
            ->pluck('id');
            $doctor->permissions()->sync($permissions);
        }

        // موظف الاستقبال
        $receptionStaff = Role::where('name', 'reception_staff')->first();
        if ($receptionStaff) {
            $permissions = Permission::whereIn('module', [
                'dashboard', 'patients', 'appointments'
            ])->whereIn('action', ['view', 'create', 'edit'])
            ->pluck('id');
            $receptionStaff->permissions()->sync($permissions);
        }

        // موظف الخزينة
        $cashier = Role::where('name', 'cashier')->first();
        if ($cashier) {
            $permissions = Permission::whereIn('module', [
                'dashboard', 'patients', 'billing', 'cash_registers', 'insurance'
            ])->whereIn('action', ['view', 'create', 'edit', 'manage'])
            ->pluck('id');
            $cashier->permissions()->sync($permissions);
        }

        // فني المختبر
        $labTechnician = Role::where('name', 'lab_technician')->first();
        if ($labTechnician) {
            $permissions = Permission::whereIn('module', [
                'dashboard', 'patients', 'laboratory'
            ])->whereIn('action', ['view', 'create', 'edit'])
            ->pluck('id');
            $labTechnician->permissions()->sync($permissions);
        }

        // فني الأشعة
        $radiologyTechnician = Role::where('name', 'radiology_technician')->first();
        if ($radiologyTechnician) {
            $permissions = Permission::whereIn('module', [
                'dashboard', 'patients', 'radiology'
            ])->whereIn('action', ['view', 'create', 'edit'])
            ->pluck('id');
            $radiologyTechnician->permissions()->sync($permissions);
        }

        // مدير الأمان
        $securityAdmin = Role::where('name', 'security_admin')->first();
        if ($securityAdmin) {
            $permissions = Permission::whereIn('module', [
                'dashboard', 'users', 'audit', 'security', 'reports'
            ])->pluck('id');
            $securityAdmin->permissions()->sync($permissions);
        }
    }
}