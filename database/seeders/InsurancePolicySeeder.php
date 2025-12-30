<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InsuranceCompany;
use App\Models\InsurancePolicy;

class InsurancePolicySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $companies = InsuranceCompany::all();

        if ($companies->isEmpty()) {
            $this->command->warn('No insurance companies found. Please run InsuranceCompanySeeder first.');
            return;
        }

        $policies = [
            // شركة التأمين الطبي الشامل
            [
                'company_name' => 'شركة التأمين الطبي الشامل',
                'policies' => [
                    [
                        'policy_number' => 'CMIC-IND-001',
                        'policy_name_ar' => 'بوليصة التأمين الفردي الأساسي',
                        'policy_name_en' => 'Basic Individual Insurance Policy',
                        'policy_type' => 'individual',
                        'coverage_percentage' => 80.00,
                        'deductible_amount' => 100.00,
                        'max_coverage_per_year' => 50000.00,
                        'max_coverage_per_visit' => 2000.00,
                        'covered_services' => ['consultation', 'laboratory', 'radiology', 'medication'],
                        'excluded_services' => ['cosmetic_surgery', 'dental_cosmetic'],
                        'requires_pre_approval' => false,
                        'co_payment_amount' => 50.00,
                        'waiting_period_days' => 30,
                        'effective_date' => '2024-01-01',
                        'expiry_date' => '2025-12-31',
                        'status' => 'active'
                    ],
                    [
                        'policy_number' => 'CMIC-FAM-001',
                        'policy_name_ar' => 'بوليصة التأمين العائلي الشامل',
                        'policy_name_en' => 'Comprehensive Family Insurance Policy',
                        'policy_type' => 'family',
                        'coverage_percentage' => 85.00,
                        'deductible_amount' => 200.00,
                        'max_coverage_per_year' => 100000.00,
                        'max_coverage_per_visit' => 3000.00,
                        'covered_services' => ['consultation', 'laboratory', 'radiology', 'medication', 'surgery', 'emergency'],
                        'excluded_services' => ['cosmetic_surgery'],
                        'requires_pre_approval' => true,
                        'pre_approval_days' => 3,
                        'co_payment_amount' => 75.00,
                        'waiting_period_days' => 0,
                        'effective_date' => '2024-01-01',
                        'expiry_date' => '2025-12-31',
                        'status' => 'active'
                    ]
                ]
            ],
            // شركة الحماية للتأمين الصحي
            [
                'company_name' => 'شركة الحماية للتأمين الصحي',
                'policies' => [
                    [
                        'policy_number' => 'AHIC-CORP-001',
                        'policy_name_ar' => 'بوليصة تأمين الشركات الذهبية',
                        'policy_name_en' => 'Corporate Gold Insurance Policy',
                        'policy_type' => 'corporate',
                        'coverage_percentage' => 90.00,
                        'deductible_amount' => 0.00,
                        'max_coverage_per_year' => 200000.00,
                        'max_coverage_per_visit' => 5000.00,
                        'covered_services' => ['consultation', 'laboratory', 'radiology', 'medication', 'surgery', 'emergency', 'dental'],
                        'excluded_services' => ['cosmetic_surgery', 'experimental_treatment'],
                        'requires_pre_approval' => true,
                        'pre_approval_days' => 5,
                        'co_payment_percentage' => 5.00,
                        'waiting_period_days' => 0,
                        'effective_date' => '2024-01-01',
                        'expiry_date' => '2025-12-31',
                        'status' => 'active'
                    ],
                    [
                        'policy_number' => 'AHIC-GRP-001',
                        'policy_name_ar' => 'بوليصة التأمين الجماعي الفضية',
                        'policy_name_en' => 'Group Silver Insurance Policy',
                        'policy_type' => 'group',
                        'coverage_percentage' => 75.00,
                        'deductible_amount' => 150.00,
                        'max_coverage_per_year' => 75000.00,
                        'max_coverage_per_visit' => 2500.00,
                        'covered_services' => ['consultation', 'laboratory', 'radiology', 'medication'],
                        'excluded_services' => ['cosmetic_surgery', 'dental_cosmetic', 'experimental_treatment'],
                        'requires_pre_approval' => false,
                        'co_payment_amount' => 100.00,
                        'waiting_period_days' => 60,
                        'effective_date' => '2024-01-01',
                        'expiry_date' => '2025-12-31',
                        'status' => 'active'
                    ]
                ]
            ],
            // شركة الأمان للتأمين التعاوني
            [
                'company_name' => 'شركة الأمان للتأمين التعاوني',
                'policies' => [
                    [
                        'policy_number' => 'ACIC-IND-BASIC',
                        'policy_name_ar' => 'بوليصة التأمين الفردي الاقتصادي',
                        'policy_name_en' => 'Economic Individual Insurance Policy',
                        'policy_type' => 'individual',
                        'coverage_percentage' => 70.00,
                        'deductible_amount' => 200.00,
                        'max_coverage_per_year' => 30000.00,
                        'max_coverage_per_visit' => 1500.00,
                        'covered_services' => ['consultation', 'laboratory', 'medication'],
                        'excluded_services' => ['cosmetic_surgery', 'dental_cosmetic', 'radiology_advanced'],
                        'requires_pre_approval' => false,
                        'co_payment_amount' => 75.00,
                        'waiting_period_days' => 90,
                        'effective_date' => '2024-01-01',
                        'expiry_date' => '2025-12-31',
                        'status' => 'active'
                    ]
                ]
            ],
            // شركة الوطنية للتأمين الصحي
            [
                'company_name' => 'شركة الوطنية للتأمين الصحي',
                'policies' => [
                    [
                        'policy_number' => 'NHIC-FAM-PREMIUM',
                        'policy_name_ar' => 'بوليصة التأمين العائلي المميز',
                        'policy_name_en' => 'Premium Family Insurance Policy',
                        'policy_type' => 'family',
                        'coverage_percentage' => 95.00,
                        'deductible_amount' => 0.00,
                        'max_coverage_per_year' => 300000.00,
                        'max_coverage_per_visit' => 10000.00,
                        'covered_services' => ['consultation', 'laboratory', 'radiology', 'medication', 'surgery', 'emergency', 'dental', 'physiotherapy'],
                        'excluded_services' => ['cosmetic_surgery'],
                        'requires_pre_approval' => true,
                        'pre_approval_days' => 2,
                        'co_payment_percentage' => 2.00,
                        'waiting_period_days' => 0,
                        'effective_date' => '2024-01-01',
                        'expiry_date' => '2025-12-31',
                        'status' => 'active'
                    ]
                ]
            ],
            // شركة المتحدة للتأمين الطبي
            [
                'company_name' => 'شركة المتحدة للتأمين الطبي',
                'policies' => [
                    [
                        'policy_number' => 'UMIC-CORP-STANDARD',
                        'policy_name_ar' => 'بوليصة تأمين الشركات القياسية',
                        'policy_name_en' => 'Standard Corporate Insurance Policy',
                        'policy_type' => 'corporate',
                        'coverage_percentage' => 80.00,
                        'deductible_amount' => 100.00,
                        'max_coverage_per_year' => 150000.00,
                        'max_coverage_per_visit' => 4000.00,
                        'covered_services' => ['consultation', 'laboratory', 'radiology', 'medication', 'surgery', 'emergency'],
                        'excluded_services' => ['cosmetic_surgery', 'experimental_treatment', 'dental_cosmetic'],
                        'requires_pre_approval' => true,
                        'pre_approval_days' => 7,
                        'co_payment_amount' => 100.00,
                        'waiting_period_days' => 30,
                        'effective_date' => '2024-01-01',
                        'expiry_date' => '2025-12-31',
                        'status' => 'active'
                    ]
                ]
            ]
        ];

        foreach ($policies as $companyData) {
            $company = $companies->where('name_ar', $companyData['company_name'])->first();
            
            if (!$company) {
                $this->command->warn("Company '{$companyData['company_name']}' not found. Skipping its policies.");
                continue;
            }

            foreach ($companyData['policies'] as $policyData) {
                $policyData['insurance_company_id'] = $company->id;
                
                InsurancePolicy::create($policyData);
                
                $this->command->info("Created policy: {$policyData['policy_name_ar']} for {$company->name_ar}");
            }
        }

        $this->command->info('Insurance policies seeded successfully!');
    }
}