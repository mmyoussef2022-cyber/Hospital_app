<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class SimpleAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
            'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.delete',
            'certificates.view', 'certificates.create', 'certificates.edit', 'certificates.delete', 'certificates.verify',
            'services.view', 'services.create', 'services.edit', 'services.delete',
            'reviews.view', 'reviews.create', 'reviews.edit', 'reviews.delete', 'reviews.moderate',
            'reports.view', 'analytics.view', 'system.admin', 'system.settings'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);

        // Assign all permissions to super-admin
        $superAdminRole->syncPermissions(Permission::all());

        // Get first department
        $department = Department::first();
        if (!$department) {
            $department = Department::create([
                'name_ar' => 'الإدارة',
                'name_en' => 'Administration',
                'code' => 'ADMIN',
                'description_ar' => 'قسم الإدارة العامة',
                'is_active' => true
            ]);
        }

        // Create admin user using direct SQL to avoid encryption issues
        $adminExists = User::where('email', 'admin@hospital-hms.com')->first();
        if (!$adminExists) {
            $adminUser = new User();
            $adminUser->name = 'مدير النظام';
            $adminUser->email = 'admin@hospital-hms.com';
            $adminUser->password = Hash::make('password123');
            $adminUser->phone = '+966501234567';
            $adminUser->department_id = $department->id;
            $adminUser->is_active = true;
            $adminUser->email_verified_at = now();
            $adminUser->employee_id = 'EMP001';
            $adminUser->job_title = 'مدير النظام';
            $adminUser->hire_date = now();
            $adminUser->date_of_birth = '1985-01-01';
            $adminUser->gender = 'male';
            
            // Set national_id directly without using the mutator
            $adminUser->setRawAttributes(array_merge($adminUser->getAttributes(), [
                'national_id' => '1234567890'
            ]));
            
            $adminUser->address = 'الرياض، المملكة العربية السعودية';
            $adminUser->save();

            // Assign super-admin role
            $adminUser->assignRole('super-admin');
        }

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@hospital-hms.com');
        $this->command->info('Password: password123');
    }
}