<?php

namespace App\Listeners;

use App\Models\InsuranceClaim;
use App\Models\InsurancePolicy;
use App\Models\PatientInsurance;
use App\Services\NotificationService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Log;

class InsuranceEventListener
{
    protected $notificationService;
    protected $auditLogService;

    public function __construct(NotificationService $notificationService, AuditLogService $auditLogService)
    {
        $this->notificationService = $notificationService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * معالجة إنشاء مطالبة تأمين جديدة
     */
    public function onInsuranceClaimCreated($claim)
    {
        try {
            Log::info('تم إنشاء مطالبة تأمين جديدة', [
                'claim_id' => $claim->id,
                'patient_id' => $claim->patient_id,
                'amount' => $claim->claim_amount
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'insurance_claim_created',
                'model' => 'InsuranceClaim',
                'model_id' => $claim->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $claim->patient->name,
                    'insurance_company' => $claim->insuranceCompany->name,
                    'claim_amount' => $claim->claim_amount,
                    'claim_number' => $claim->claim_number
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'مطالبة تأمين جديدة',
                'message' => "تم إرسال مطالبة تأمين بقيمة " . number_format($claim->claim_amount, 2) . " ريال إلى شركة {$claim->insuranceCompany->name}. رقم المطالبة: {$claim->claim_number}",
                'type' => 'insurance_claim_submitted',
                'priority' => 'normal',
                'recipients' => [$claim->patient],
                'reference_type' => get_class($claim),
                'reference_id' => $claim->id,
                'data' => [
                    'claim_number' => $claim->claim_number,
                    'claim_amount' => $claim->claim_amount,
                    'insurance_company' => $claim->insuranceCompany->name
                ]
            ]);

            // إرسال إشعار لقسم التأمين
            $insuranceUsers = \App\Models\User::role('insurance_coordinator')->get();
            if ($insuranceUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'مطالبة تأمين جديدة',
                    'message' => "مطالبة جديدة للمريض {$claim->patient->name} بقيمة " . number_format($claim->claim_amount, 2) . " ريال - {$claim->insuranceCompany->name}",
                    'type' => 'new_insurance_claim',
                    'priority' => 'normal',
                    'recipients' => $insuranceUsers->toArray(),
                    'reference_type' => get_class($claim),
                    'reference_id' => $claim->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة إنشاء مطالبة التأمين: ' . $e->getMessage(), [
                'claim_id' => $claim->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة موافقة على مطالبة تأمين
     */
    public function onInsuranceClaimApproved($claim)
    {
        try {
            Log::info('تم الموافقة على مطالبة تأمين', [
                'claim_id' => $claim->id,
                'approved_amount' => $claim->approved_amount
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'insurance_claim_approved',
                'model' => 'InsuranceClaim',
                'model_id' => $claim->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $claim->patient->name,
                    'claim_number' => $claim->claim_number,
                    'approved_amount' => $claim->approved_amount,
                    'approval_date' => now()
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'موافقة على مطالبة التأمين',
                'message' => "تم الموافقة على مطالبة التأمين رقم {$claim->claim_number} بقيمة " . number_format($claim->approved_amount, 2) . " ريال. سيتم تحويل المبلغ خلال 5-7 أيام عمل.",
                'type' => 'insurance_claim_approved',
                'priority' => 'normal',
                'recipients' => [$claim->patient],
                'reference_type' => get_class($claim),
                'reference_id' => $claim->id,
                'data' => [
                    'claim_number' => $claim->claim_number,
                    'approved_amount' => $claim->approved_amount
                ]
            ]);

            // إرسال إشعار للمحاسبة
            $accountingUsers = \App\Models\User::role('accountant')->get();
            if ($accountingUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'مطالبة تأمين معتمدة',
                    'message' => "تم اعتماد مطالبة {$claim->claim_number} للمريض {$claim->patient->name} بقيمة " . number_format($claim->approved_amount, 2) . " ريال",
                    'type' => 'insurance_claim_approved_accounting',
                    'priority' => 'normal',
                    'recipients' => $accountingUsers->toArray(),
                    'reference_type' => get_class($claim),
                    'reference_id' => $claim->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة موافقة مطالبة التأمين: ' . $e->getMessage(), [
                'claim_id' => $claim->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة رفض مطالبة تأمين
     */
    public function onInsuranceClaimRejected($claim)
    {
        try {
            Log::info('تم رفض مطالبة تأمين', [
                'claim_id' => $claim->id,
                'rejection_reason' => $claim->rejection_reason
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'insurance_claim_rejected',
                'model' => 'InsuranceClaim',
                'model_id' => $claim->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $claim->patient->name,
                    'claim_number' => $claim->claim_number,
                    'rejection_reason' => $claim->rejection_reason,
                    'rejection_date' => now()
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'رفض مطالبة التأمين',
                'message' => "تم رفض مطالبة التأمين رقم {$claim->claim_number}. السبب: {$claim->rejection_reason}. يمكنك تقديم اعتراض أو مطالبة جديدة.",
                'type' => 'insurance_claim_rejected',
                'priority' => 'high',
                'recipients' => [$claim->patient],
                'reference_type' => get_class($claim),
                'reference_id' => $claim->id,
                'data' => [
                    'claim_number' => $claim->claim_number,
                    'rejection_reason' => $claim->rejection_reason
                ]
            ]);

            // إرسال إشعار لقسم التأمين
            $insuranceUsers = \App\Models\User::role('insurance_coordinator')->get();
            if ($insuranceUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'مطالبة تأمين مرفوضة',
                    'message' => "تم رفض مطالبة {$claim->claim_number} للمريض {$claim->patient->name}. يرجى متابعة الحالة مع المريض.",
                    'type' => 'insurance_claim_rejected_internal',
                    'priority' => 'normal',
                    'recipients' => $insuranceUsers->toArray(),
                    'reference_type' => get_class($claim),
                    'reference_id' => $claim->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة رفض مطالبة التأمين: ' . $e->getMessage(), [
                'claim_id' => $claim->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة انتهاء صلاحية بوليصة تأمين
     */
    public function onInsurancePolicyExpiring($policy)
    {
        try {
            Log::info('بوليصة تأمين ستنتهي قريباً', [
                'policy_id' => $policy->id,
                'expiry_date' => $policy->expiry_date
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'insurance_policy_expiring',
                'model' => 'InsurancePolicy',
                'model_id' => $policy->id,
                'user_id' => null,
                'data' => [
                    'policy_number' => $policy->policy_number,
                    'insurance_company' => $policy->insuranceCompany->name,
                    'expiry_date' => $policy->expiry_date->format('Y-m-d'),
                    'days_remaining' => $policy->expiry_date->diffInDays(now())
                ]
            ]);

            $daysRemaining = $policy->expiry_date->diffInDays(now());

            // إرسال إشعار لقسم التأمين
            $insuranceUsers = \App\Models\User::role('insurance_coordinator')->get();
            if ($insuranceUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'انتهاء صلاحية بوليصة تأمين',
                    'message' => "بوليصة {$policy->policy_number} من شركة {$policy->insuranceCompany->name} ستنتهي خلال {$daysRemaining} يوم. يرجى التجديد.",
                    'type' => 'insurance_policy_expiring',
                    'priority' => 'high',
                    'recipients' => $insuranceUsers->toArray(),
                    'reference_type' => get_class($policy),
                    'reference_id' => $policy->id,
                    'data' => [
                        'policy_number' => $policy->policy_number,
                        'expiry_date' => $policy->expiry_date->format('Y-m-d'),
                        'days_remaining' => $daysRemaining
                    ]
                ]);
            }

            // إرسال إشعار للإدارة
            $managementUsers = \App\Models\User::role('admin')->get();
            if ($managementUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'تجديد بوليصة تأمين مطلوب',
                    'message' => "بوليصة التأمين {$policy->policy_number} تحتاج تجديد خلال {$daysRemaining} يوم",
                    'type' => 'insurance_renewal_required',
                    'priority' => 'high',
                    'recipients' => $managementUsers->toArray(),
                    'reference_type' => get_class($policy),
                    'reference_id' => $policy->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة انتهاء صلاحية البوليصة: ' . $e->getMessage(), [
                'policy_id' => $policy->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة تجديد بوليصة تأمين
     */
    public function onInsurancePolicyRenewed($policy)
    {
        try {
            Log::info('تم تجديد بوليصة تأمين', [
                'policy_id' => $policy->id,
                'new_expiry_date' => $policy->expiry_date
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'insurance_policy_renewed',
                'model' => 'InsurancePolicy',
                'model_id' => $policy->id,
                'user_id' => auth()->id(),
                'data' => [
                    'policy_number' => $policy->policy_number,
                    'insurance_company' => $policy->insuranceCompany->name,
                    'new_expiry_date' => $policy->expiry_date->format('Y-m-d'),
                    'renewal_date' => now()
                ]
            ]);

            // إرسال إشعار لقسم التأمين
            $insuranceUsers = \App\Models\User::role('insurance_coordinator')->get();
            if ($insuranceUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'تم تجديد بوليصة التأمين',
                    'message' => "تم تجديد بوليصة {$policy->policy_number} من شركة {$policy->insuranceCompany->name} حتى {$policy->expiry_date->format('Y-m-d')}",
                    'type' => 'insurance_policy_renewed',
                    'priority' => 'normal',
                    'recipients' => $insuranceUsers->toArray(),
                    'reference_type' => get_class($policy),
                    'reference_id' => $policy->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة تجديد البوليصة: ' . $e->getMessage(), [
                'policy_id' => $policy->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة تجاوز حد التغطية التأمينية
     */
    public function onCoverageLimitExceeded($patientInsurance, $amount)
    {
        try {
            Log::warning('تجاوز حد التغطية التأمينية', [
                'patient_insurance_id' => $patientInsurance->id,
                'patient_id' => $patientInsurance->patient_id,
                'exceeded_amount' => $amount
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'insurance_coverage_exceeded',
                'model' => 'PatientInsurance',
                'model_id' => $patientInsurance->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $patientInsurance->patient->name,
                    'insurance_company' => $patientInsurance->insuranceCompany->name,
                    'coverage_limit' => $patientInsurance->coverage_limit,
                    'exceeded_amount' => $amount
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'تجاوز حد التغطية التأمينية',
                'message' => "تم تجاوز حد التغطية التأمينية الخاص بك. المبلغ الإضافي " . number_format($amount, 2) . " ريال سيكون على نفقتك الشخصية.",
                'type' => 'insurance_limit_exceeded',
                'priority' => 'high',
                'recipients' => [$patientInsurance->patient],
                'reference_type' => get_class($patientInsurance),
                'reference_id' => $patientInsurance->id,
                'data' => [
                    'exceeded_amount' => $amount,
                    'coverage_limit' => $patientInsurance->coverage_limit
                ]
            ]);

            // إرسال إشعار لقسم التأمين
            $insuranceUsers = \App\Models\User::role('insurance_coordinator')->get();
            if ($insuranceUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'تجاوز حد التغطية',
                    'message' => "المريض {$patientInsurance->patient->name} تجاوز حد التغطية التأمينية بمبلغ " . number_format($amount, 2) . " ريال",
                    'type' => 'coverage_limit_exceeded_internal',
                    'priority' => 'normal',
                    'recipients' => $insuranceUsers->toArray(),
                    'reference_type' => get_class($patientInsurance),
                    'reference_id' => $patientInsurance->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة تجاوز حد التغطية: ' . $e->getMessage(), [
                'patient_insurance_id' => $patientInsurance->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة تأخر دفع شركة التأمين
     */
    public function onInsurancePaymentDelayed($claim)
    {
        try {
            Log::warning('تأخر دفع شركة التأمين', [
                'claim_id' => $claim->id,
                'days_delayed' => $claim->expected_payment_date->diffInDays(now())
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'insurance_payment_delayed',
                'model' => 'InsuranceClaim',
                'model_id' => $claim->id,
                'user_id' => null,
                'data' => [
                    'claim_number' => $claim->claim_number,
                    'insurance_company' => $claim->insuranceCompany->name,
                    'expected_payment_date' => $claim->expected_payment_date->format('Y-m-d'),
                    'days_delayed' => $claim->expected_payment_date->diffInDays(now())
                ]
            ]);

            $daysDelayed = $claim->expected_payment_date->diffInDays(now());

            // إرسال إشعار لقسم التأمين
            $insuranceUsers = \App\Models\User::role('insurance_coordinator')->get();
            if ($insuranceUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'تأخر دفع شركة التأمين',
                    'message' => "تأخر دفع مطالبة {$claim->claim_number} من شركة {$claim->insuranceCompany->name} لمدة {$daysDelayed} يوم. يرجى المتابعة.",
                    'type' => 'insurance_payment_delayed',
                    'priority' => 'high',
                    'recipients' => $insuranceUsers->toArray(),
                    'reference_type' => get_class($claim),
                    'reference_id' => $claim->id,
                    'data' => [
                        'claim_number' => $claim->claim_number,
                        'days_delayed' => $daysDelayed
                    ]
                ]);
            }

            // إرسال إشعار للإدارة المالية
            $financeUsers = \App\Models\User::role('finance_manager')->get();
            if ($financeUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'تأخر دفع تأمين',
                    'message' => "مطالبة {$claim->claim_number} متأخرة {$daysDelayed} يوم - " . number_format($claim->approved_amount, 2) . " ريال",
                    'type' => 'insurance_payment_delayed_finance',
                    'priority' => 'high',
                    'recipients' => $financeUsers->toArray(),
                    'reference_type' => get_class($claim),
                    'reference_id' => $claim->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة تأخر دفع التأمين: ' . $e->getMessage(), [
                'claim_id' => $claim->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}