<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            // إضافة seeders التأمين والبيانات الأساسية
            InsuranceCompanySeeder::class,
            InsurancePolicySeeder::class,
            PaymentTermSeeder::class,
            LabTestSeeder::class,
            RadiologyStudySeeder::class,
            OperatingRoomSeeder::class,
            SurgicalProcedureSeeder::class,
            RoomSeeder::class,
            // البيانات التجريبية الشاملة
            EnhancedHospitalDataSeeder::class,
            ComprehensiveTestDataSeeder::class,
        ]);
    }
}