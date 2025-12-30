<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\InsurancePolicy;
use App\Models\InsuranceCompany;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InsuranceCoverageService
{
    /**
     * حساب التغطية التأمينية للمريض
     */
    public function calculateCoverage(Patient $patient, float $totalAmount, array $services = [])
    {
        try {
            // التحقق من وجود تأمين نشط
            $insurancePolicy = $patient->insurancePolicy;
            
            if (!$insurancePolicy || $insurancePolicy->status !== 'active') {
                return $this->getCashPatientCoverage($totalAmount);
            }
            
            // التحقق من صلاحية البوليصة
            if ($insurancePolicy->end_date < now()) {
                return $this->getCashPatientCoverage($totalAmount, 'انتهت صلاحية البوليصة');
            }
            
            // حساب التغطية حسب نوع الخدمات
            $coverageDetails = $this->calculateServiceCoverage($insurancePolicy, $totalAmount, $services);
            
            return $coverageDetails;
            
        } catch (\Exception $e) {
            Log::error('خطأ في حساب التغطية التأمينية: ' . $e->getMessage());
            
            return $this->getCashPatientCoverage($totalAmount, 'خطأ في حساب التأمين');
        }
    }

    /**
     * حساب التغطية حسب نوع الخدمات
     */
    private function calculateServiceCoverage(InsurancePolicy $policy, float $totalAmount, array $services)
    {
        $company = $policy->company;
        $coverageRates = $company->coverage_rates ?? [];
        
        // إذا لم تكن هناك خدمات محددة، استخدم النسبة العامة
        if (empty($services)) {
            return $this->calculateGeneralCoverage($policy, $totalAmount);
        }
        
        $totalInsuranceAmount = 0;
        $serviceBreakdown = [];
        
        foreach ($services as $service) {
            $serviceType = $service['type'] ?? 'general';
            $serviceAmount = $service['amount'] ?? 0;
            
            // الحصول على نسبة التغطية لهذا النوع من الخدمة
            $coverageRate = $this->getCoverageRateForService($coverageRates, $serviceType);
            
            // حساب مبلغ التأمين لهذه الخدمة
            $insuranceAmount = $serviceAmount * ($coverageRate / 100);
            $patientAmount = $serviceAmount - $insuranceAmount;
            
            $totalInsuranceAmount += $insuranceAmount;
            
            $serviceBreakdown[] = [
                'service_name' => $service['name'] ?? $serviceType,
                'service_type' => $serviceType,
                'service_amount' => $serviceAmount,
                'coverage_rate' => $coverageRate,
                'insurance_amount' => $insuranceAmount,
                'patient_amount' => $patientAmount
            ];
        }
        
        $totalPatientAmount = $totalAmount - $totalInsuranceAmount;
        $overallCoverageRate = $totalAmount > 0 ? ($totalInsuranceAmount / $totalAmount) * 100 : 0;
        
        return [
            'is_insured' => true,
            'patient_type' => 'مؤمن',
            'insurance_company' => $company->name,
            'policy_number' => $policy->policy_number,
            'total_amount' => $totalAmount,
            'insurance_amount' => $totalInsuranceAmount,
            'patient_amount' => $totalPatientAmount,
            'coverage_percentage' => round($overallCoverageRate, 2),
            'service_breakdown' => $serviceBreakdown,
            'policy_limits' => $this->checkPolicyLimits($policy, $totalInsuranceAmount),
            'deductible_info' => $this->calculateDeductible($policy, $totalAmount),
            'copay_info' => $this->calculateCopay($policy, $services)
        ];
    }

    /**
     * حساب التغطية العامة
     */
    private function calculateGeneralCoverage(InsurancePolicy $policy, float $totalAmount)
    {
        $company = $policy->company;
        $generalCoverageRate = $company->general_coverage_rate ?? 80; // افتراضي 80%
        
        $insuranceAmount = $totalAmount * ($generalCoverageRate / 100);
        $patientAmount = $totalAmount - $insuranceAmount;
        
        return [
            'is_insured' => true,
            'patient_type' => 'مؤمن',
            'insurance_company' => $company->name,
            'policy_number' => $policy->policy_number,
            'total_amount' => $totalAmount,
            'insurance_amount' => $insuranceAmount,
            'patient_amount' => $patientAmount,
            'coverage_percentage' => $generalCoverageRate,
            'service_breakdown' => [],
            'policy_limits' => $this->checkPolicyLimits($policy, $insuranceAmount),
            'deductible_info' => $this->calculateDeductible($policy, $totalAmount),
            'copay_info' => []
        ];
    }

    /**
     * الحصول على نسبة التغطية لنوع خدمة معين
     */
    private function getCoverageRateForService(array $coverageRates, string $serviceType)
    {
        // البحث عن نسبة التغطية المحددة لهذا النوع
        foreach ($coverageRates as $rate) {
            if ($rate['service_type'] === $serviceType) {
                return $rate['coverage_percentage'];
            }
        }
        
        // إذا لم توجد نسبة محددة، استخدم النسبة العامة
        $generalRate = collect($coverageRates)->where('service_type', 'general')->first();
        return $generalRate['coverage_percentage'] ?? 80; // افتراضي 80%
    }

    /**
     * التحقق من حدود البوليصة
     */
    private function checkPolicyLimits(InsurancePolicy $policy, float $requestedAmount)
    {
        $limits = [
            'annual_limit' => $policy->annual_limit ?? 0,
            'used_amount' => $policy->used_amount ?? 0,
            'remaining_amount' => 0,
            'limit_exceeded' => false,
            'available_amount' => 0
        ];
        
        if ($limits['annual_limit'] > 0) {
            $limits['remaining_amount'] = $limits['annual_limit'] - $limits['used_amount'];
            $limits['available_amount'] = min($requestedAmount, $limits['remaining_amount']);
            $limits['limit_exceeded'] = $requestedAmount > $limits['remaining_amount'];
        } else {
            $limits['available_amount'] = $requestedAmount;
        }
        
        return $limits;
    }

    /**
     * حساب الخصم الثابت (Deductible)
     */
    private function calculateDeductible(InsurancePolicy $policy, float $totalAmount)
    {
        $deductible = $policy->deductible ?? 0;
        $deductibleUsed = $policy->deductible_used ?? 0;
        $remainingDeductible = max(0, $deductible - $deductibleUsed);
        
        return [
            'total_deductible' => $deductible,
            'used_deductible' => $deductibleUsed,
            'remaining_deductible' => $remainingDeductible,
            'current_deductible' => min($remainingDeductible, $totalAmount)
        ];
    }

    /**
     * حساب المشاركة الثابتة (Copay)
     */
    private function calculateCopay(InsurancePolicy $policy, array $services)
    {
        $copayInfo = [];
        $totalCopay = 0;
        
        foreach ($services as $service) {
            $serviceType = $service['type'] ?? 'general';
            $copayAmount = $this->getCopayForService($policy, $serviceType);
            
            if ($copayAmount > 0) {
                $copayInfo[] = [
                    'service_name' => $service['name'] ?? $serviceType,
                    'copay_amount' => $copayAmount
                ];
                $totalCopay += $copayAmount;
            }
        }
        
        return [
            'total_copay' => $totalCopay,
            'service_copays' => $copayInfo
        ];
    }

    /**
     * الحصول على مبلغ المشاركة لخدمة معينة
     */
    private function getCopayForService(InsurancePolicy $policy, string $serviceType)
    {
        $copayRates = $policy->company->copay_rates ?? [];
        
        foreach ($copayRates as $rate) {
            if ($rate['service_type'] === $serviceType) {
                return $rate['copay_amount'] ?? 0;
            }
        }
        
        return 0;
    }

    /**
     * إرجاع تفاصيل المريض النقدي
     */
    private function getCashPatientCoverage(float $totalAmount, string $reason = null)
    {
        return [
            'is_insured' => false,
            'patient_type' => 'نقدي',
            'insurance_company' => null,
            'policy_number' => null,
            'total_amount' => $totalAmount,
            'insurance_amount' => 0,
            'patient_amount' => $totalAmount,
            'coverage_percentage' => 0,
            'service_breakdown' => [],
            'policy_limits' => [],
            'deductible_info' => [],
            'copay_info' => [],
            'reason' => $reason
        ];
    }

    /**
     * التحقق من أهلية التأمين
     */
    public function checkInsuranceEligibility(Patient $patient)
    {
        try {
            $policy = $patient->insurancePolicy;
            
            if (!$policy) {
                return [
                    'eligible' => false,
                    'reason' => 'لا يوجد تأمين مسجل للمريض'
                ];
            }
            
            if ($policy->status !== 'active') {
                return [
                    'eligible' => false,
                    'reason' => 'البوليصة غير نشطة'
                ];
            }
            
            if ($policy->end_date < now()) {
                return [
                    'eligible' => false,
                    'reason' => 'انتهت صلاحية البوليصة'
                ];
            }
            
            if ($policy->start_date > now()) {
                return [
                    'eligible' => false,
                    'reason' => 'لم تبدأ البوليصة بعد'
                ];
            }
            
            return [
                'eligible' => true,
                'policy' => $policy,
                'company' => $policy->company
            ];
            
        } catch (\Exception $e) {
            Log::error('خطأ في التحقق من أهلية التأمين: ' . $e->getMessage());
            
            return [
                'eligible' => false,
                'reason' => 'خطأ في التحقق من التأمين'
            ];
        }
    }

    /**
     * حساب التغطية لخدمات متعددة
     */
    public function calculateMultiServiceCoverage(Patient $patient, array $invoices)
    {
        try {
            $totalCoverage = [
                'total_amount' => 0,
                'total_insurance_amount' => 0,
                'total_patient_amount' => 0,
                'invoices_breakdown' => []
            ];
            
            foreach ($invoices as $invoice) {
                $coverage = $this->calculateCoverage(
                    $patient,
                    $invoice['amount'],
                    $invoice['services'] ?? []
                );
                
                $totalCoverage['total_amount'] += $coverage['total_amount'];
                $totalCoverage['total_insurance_amount'] += $coverage['insurance_amount'];
                $totalCoverage['total_patient_amount'] += $coverage['patient_amount'];
                
                $totalCoverage['invoices_breakdown'][] = [
                    'invoice_id' => $invoice['id'],
                    'coverage' => $coverage
                ];
            }
            
            $totalCoverage['overall_coverage_percentage'] = $totalCoverage['total_amount'] > 0 
                ? ($totalCoverage['total_insurance_amount'] / $totalCoverage['total_amount']) * 100 
                : 0;
            
            return $totalCoverage;
            
        } catch (\Exception $e) {
            Log::error('خطأ في حساب التغطية المتعددة: ' . $e->getMessage());
            
            return [
                'error' => true,
                'message' => 'خطأ في حساب التغطية'
            ];
        }
    }

    /**
     * تحديث استخدام البوليصة
     */
    public function updatePolicyUsage(InsurancePolicy $policy, float $usedAmount)
    {
        try {
            $policy->increment('used_amount', $usedAmount);
            
            // تحديث تاريخ آخر استخدام
            $policy->update(['last_used_at' => now()]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('خطأ في تحديث استخدام البوليصة: ' . $e->getMessage());
            return false;
        }
    }
}