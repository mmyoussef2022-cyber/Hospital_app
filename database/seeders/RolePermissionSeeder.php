<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // System Administration
            'system.manage',
            'system.configure',
            'system.backup',
            'system.reports',
            
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.manage_roles',
            
            // Department Management
            'departments.view',
            'departments.create',
            'departments.edit',
            'departments.delete',
            
            // Patient Management
            'patients.view',
            'patients.create',
            'patients.edit',
            'patients.delete',
            'patients.view_medical_records',
            'patients.edit_medical_records',
            
            // Appointment Management
            'appointments.view',
            'appointments.create',
            'appointments.edit',
            'appointments.delete',
            'appointments.schedule',
            
            // Medical Records
            'medical_records.view',
            'medical_records.create',
            'medical_records.edit',
            'medical_records.delete',
            'medical_records.prescribe',
            
            // Laboratory
            'laboratory.view',
            'laboratory.create',
            'laboratory.edit',
            'laboratory.delete',
            'laboratory.results',
            
            // Radiology
            'radiology.view',
            'radiology.create',
            'radiology.edit',
            'radiology.delete',
            'radiology.results',
            
            // Financial Management
            'billing.view',
            'billing.create',
            'billing.edit',
            'billing.delete',
            'billing.payments',
            'billing.reports',
            
            // Room Management
            'rooms.view',
            'rooms.create',
            'rooms.edit',
            'rooms.delete',
            'rooms.assign',
            
            // Surgery Management
            'surgery.view',
            'surgery.create',
            'surgery.edit',
            'surgery.delete',
            'surgery.schedule',
            
            // Notifications
            'notifications.send',
            'notifications.manage',
            
            // Reports
            'reports.view',
            'reports.generate',
            'reports.export',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission]);
        }

        // Create roles
        $superAdmin = \Spatie\Permission\Models\Role::create(['name' => 'Super Admin']);
        $hospitalAdmin = \Spatie\Permission\Models\Role::create(['name' => 'Hospital Admin']);
        $doctor = \Spatie\Permission\Models\Role::create(['name' => 'Doctor']);
        $nurse = \Spatie\Permission\Models\Role::create(['name' => 'Nurse']);
        $receptionist = \Spatie\Permission\Models\Role::create(['name' => 'Receptionist']);
        $labTechnician = \Spatie\Permission\Models\Role::create(['name' => 'Lab Technician']);
        $radiologyTechnician = \Spatie\Permission\Models\Role::create(['name' => 'Radiology Technician']);
        $accountant = \Spatie\Permission\Models\Role::create(['name' => 'Accountant']);
        $pharmacist = \Spatie\Permission\Models\Role::create(['name' => 'Pharmacist']);

        // Assign permissions to roles
        
        // Super Admin - All permissions
        $superAdmin->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        // Hospital Admin - Most permissions except system management
        $hospitalAdmin->givePermissionTo([
            'users.view', 'users.create', 'users.edit', 'users.manage_roles',
            'departments.view', 'departments.create', 'departments.edit',
            'patients.view', 'patients.create', 'patients.edit',
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.schedule',
            'rooms.view', 'rooms.create', 'rooms.edit', 'rooms.assign',
            'surgery.view', 'surgery.create', 'surgery.edit', 'surgery.schedule',
            'billing.view', 'billing.create', 'billing.edit', 'billing.payments', 'billing.reports',
            'reports.view', 'reports.generate', 'reports.export',
            'notifications.send', 'notifications.manage'
        ]);

        // Doctor - Medical focused permissions
        $doctor->givePermissionTo([
            'patients.view', 'patients.create', 'patients.edit',
            'patients.view_medical_records', 'patients.edit_medical_records',
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.schedule',
            'medical_records.view', 'medical_records.create', 'medical_records.edit', 'medical_records.prescribe',
            'laboratory.view', 'laboratory.create', 'laboratory.results',
            'radiology.view', 'radiology.create', 'radiology.results',
            'surgery.view', 'surgery.create', 'surgery.edit', 'surgery.schedule',
            'rooms.view', 'rooms.assign',
            'reports.view'
        ]);

        // Nurse - Patient care permissions
        $nurse->givePermissionTo([
            'patients.view', 'patients.edit',
            'patients.view_medical_records', 'patients.edit_medical_records',
            'appointments.view',
            'medical_records.view', 'medical_records.create', 'medical_records.edit',
            'laboratory.view', 'laboratory.results',
            'radiology.view', 'radiology.results',
            'rooms.view', 'rooms.assign'
        ]);

        // Receptionist - Front desk permissions
        $receptionist->givePermissionTo([
            'patients.view', 'patients.create', 'patients.edit',
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.schedule',
            'billing.view', 'billing.create', 'billing.payments',
            'rooms.view'
        ]);

        // Lab Technician - Laboratory permissions
        $labTechnician->givePermissionTo([
            'patients.view',
            'laboratory.view', 'laboratory.create', 'laboratory.edit', 'laboratory.results'
        ]);

        // Radiology Technician - Radiology permissions
        $radiologyTechnician->givePermissionTo([
            'patients.view',
            'radiology.view', 'radiology.create', 'radiology.edit', 'radiology.results'
        ]);

        // Accountant - Financial permissions
        $accountant->givePermissionTo([
            'patients.view',
            'billing.view', 'billing.create', 'billing.edit', 'billing.payments', 'billing.reports',
            'reports.view', 'reports.generate', 'reports.export'
        ]);

        // Pharmacist - Pharmacy permissions
        $pharmacist->givePermissionTo([
            'patients.view',
            'medical_records.view',
            'billing.view', 'billing.create'
        ]);
    }
}
