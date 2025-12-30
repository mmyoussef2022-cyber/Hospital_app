<?php

namespace App\Listeners;

use App\Models\Patient;
use App\Services\NotificationService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Log;

class PatientEventListener
{
    protected $notificationService;
    protected $auditLogService;

    public function __construct(NotificationService $notificationService, AuditLogService $auditLogService)
    {
        $this->notificationService = $notificationService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * معالجة تسجيل مريض جديد
     */
    public function onPatientRegistered($patient)
    {
        try {
            Log::info('تم تسجيل مريض جديد', [
                'patient_id' => $patient->id,
                'patient_name' => $patient->name,
                'national_id' => $patient->national_id
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'patient_registered',
                'model' => 'Patient',
                'model_id' => $patient->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $patient->name,
                    'national_id' => $patient->national_id,
                    'phone' => $patient->phone,
                    'registration_date' => $patient->created_at
                ]
            ]);

            // إرسال رسالة ترحيب للمريض
            $this->notificationService->send([
                'title' => 'مرحباً بك في مستشفانا',
                'message' => "مرحباً {$patient->name}، تم تسجيلك بنجاح في نظام المستشفى. رقم ملفك الطبي: {$patient->medical_record_number}",
                'type' => 'patient_welcome',
                'priority' => 'normal',
                'recipients' => [$patient],
                'reference_type' => get_class($patient),
                'reference_id' => $patient->id,
                'data' => [
                    'medical_record_number' => $patient->medical_record_number,
                    'registration_date' => $patient->created_at->format('Y-m-d')
                ]
            ]);

            // إرسال إشعار لقسم الاستقبال
            $receptionUsers = \App\Models\User::role('receptionist')->get();
            if ($receptionUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'مريض جديد',
                    'message' => "تم تسجيل مريض جديد: {$patient->name} - رقم الملف: {$patient->medical_record_number}",
                    'type' => 'new_patient_registration',
                    'priority' => 'low',
                    'recipients' => $receptionUsers->toArray(),
                    'reference_type' => get_class($patient),
                    'reference_id' => $patient->id
                ]);
            }

            // إنشاء تذكير لإكمال البيانات إذا كانت ناقصة
            if ($this->hasIncompleteData($patient)) {
                $this->scheduleDataCompletionReminder($patient);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة تسجيل المريض: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة تحديث بيانات المريض
     */
    public function onPatientUpdated($patient)
    {
        try {
            Log::info('تم تحديث بيانات مريض', [
                'patient_id' => $patient->id,
                'changes' => $patient->getChanges()
            ]);

            // تسجيل التحديث
            $this->auditLogService->log([
                'action' => 'patient_updated',
                'model' => 'Patient',
                'model_id' => $patient->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $patient->name,
                    'changes' => $patient->getChanges(),
                    'updated_at' => now()
                ]
            ]);

            // إرسال إشعار للمريض إذا تغيرت بيانات مهمة
            $importantFields = ['phone', 'email', 'address', 'emergency_contact'];
            $changedImportantFields = array_intersect(array_keys($patient->getChanges()), $importantFields);
            
            if (!empty($changedImportantFields)) {
                $this->notificationService->send([
                    'title' => 'تحديث بياناتك الشخصية',
                    'message' => "تم تحديث بياناتك الشخصية في ملفك الطبي. إذا لم تقم بهذا التحديث، يرجى الاتصال بنا فوراً.",
                    'type' => 'patient_data_updated',
                    'priority' => 'normal',
                    'recipients' => [$patient],
                    'reference_type' => get_class($patient),
                    'reference_id' => $patient->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة تحديث المريض: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة إضافة حساسية جديدة للمريض
     */
    public function onAllergyAdded($patient, $allergy)
    {
        try {
            Log::info('تم إضافة حساسية جديدة للمريض', [
                'patient_id' => $patient->id,
                'allergy' => $allergy
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'patient_allergy_added',
                'model' => 'Patient',
                'model_id' => $patient->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $patient->name,
                    'allergy' => $allergy,
                    'added_at' => now()
                ]
            ]);

            // إرسال تحذير لجميع الأطباء المعالجين
            $doctors = $patient->doctors()->get();
            if ($doctors->count() > 0) {
                $this->notificationService->send([
                    'title' => 'تحذير: حساسية جديدة',
                    'message' => "تم إضافة حساسية جديدة للمريض {$patient->name}: {$allergy}. يرجى مراعاة ذلك عند وصف الأدوية.",
                    'type' => 'patient_allergy_alert',
                    'priority' => 'high',
                    'recipients' => $doctors->map(fn($doctor) => $doctor->user)->toArray(),
                    'reference_type' => get_class($patient),
                    'reference_id' => $patient->id,
                    'data' => [
                        'allergy' => $allergy
                    ]
                ]);
            }

            // إرسال إشعار للصيدلية
            $pharmacyUsers = \App\Models\User::role('pharmacist')->get();
            if ($pharmacyUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'تحذير: حساسية جديدة',
                    'message' => "حساسية جديدة للمريض {$patient->name}: {$allergy}",
                    'type' => 'patient_allergy_alert',
                    'priority' => 'high',
                    'recipients' => $pharmacyUsers->toArray(),
                    'reference_type' => get_class($patient),
                    'reference_id' => $patient->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة إضافة الحساسية: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة تفعيل/إلغاء تفعيل المريض
     */
    public function onPatientStatusChanged($patient)
    {
        try {
            $status = $patient->is_active ? 'activated' : 'deactivated';
            
            Log::info("تم {$status} المريض", [
                'patient_id' => $patient->id,
                'new_status' => $patient->is_active
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => "patient_{$status}",
                'model' => 'Patient',
                'model_id' => $patient->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $patient->name,
                    'new_status' => $patient->is_active,
                    'changed_at' => now()
                ]
            ]);

            // إرسال إشعار للمريض
            if ($patient->is_active) {
                $this->notificationService->send([
                    'title' => 'تم تفعيل حسابك',
                    'message' => "تم تفعيل حسابك في المستشفى. يمكنك الآن حجز المواعيد والاستفادة من جميع الخدمات.",
                    'type' => 'account_activated',
                    'priority' => 'normal',
                    'recipients' => [$patient],
                    'reference_type' => get_class($patient),
                    'reference_id' => $patient->id
                ]);
            } else {
                $this->notificationService->send([
                    'title' => 'تم إيقاف حسابك مؤقتاً',
                    'message' => "تم إيقاف حسابك مؤقتاً. للاستفسار، يرجى الاتصال بخدمة العملاء.",
                    'type' => 'account_deactivated',
                    'priority' => 'high',
                    'recipients' => [$patient],
                    'reference_type' => get_class($patient),
                    'reference_id' => $patient->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة تغيير حالة المريض: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة عيد ميلاد المريض
     */
    public function onPatientBirthday($patient)
    {
        try {
            Log::info('عيد ميلاد مريض', [
                'patient_id' => $patient->id,
                'patient_name' => $patient->name,
                'birthday' => $patient->date_of_birth
            ]);

            // إرسال تهنئة بعيد الميلاد
            $this->notificationService->send([
                'title' => 'كل عام وأنت بخير! 🎉',
                'message' => "عيد ميلاد سعيد {$patient->name}! نتمنى لك عاماً مليئاً بالصحة والعافية. فريق {config('app.name')} يهنئك بهذه المناسبة السعيدة.",
                'type' => 'birthday_wishes',
                'priority' => 'low',
                'recipients' => [$patient],
                'reference_type' => get_class($patient),
                'reference_id' => $patient->id,
                'data' => [
                    'birthday_date' => $patient->date_of_birth->format('Y-m-d'),
                    'age' => $patient->date_of_birth->age
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('فشل في معالجة عيد ميلاد المريض: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة انتهاء صلاحية التأمين
     */
    public function onInsuranceExpiring($patient)
    {
        try {
            Log::info('انتهاء صلاحية تأمين المريض قريباً', [
                'patient_id' => $patient->id,
                'insurance_expiry' => $patient->insurance_expiry_date
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'patient_insurance_expiring',
                'model' => 'Patient',
                'model_id' => $patient->id,
                'user_id' => null,
                'data' => [
                    'patient_name' => $patient->name,
                    'insurance_expiry_date' => $patient->insurance_expiry_date->format('Y-m-d'),
                    'days_remaining' => $patient->insurance_expiry_date->diffInDays(now())
                ]
            ]);

            // إرسال تذكير للمريض
            $daysRemaining = $patient->insurance_expiry_date->diffInDays(now());
            $this->notificationService->send([
                'title' => 'تذكير: انتهاء صلاحية التأمين',
                'message' => "ستنتهي صلاحية تأمينك خلال {$daysRemaining} يوم في {$patient->insurance_expiry_date->format('Y-m-d')}. يرجى تجديد التأمين لتجنب انقطاع الخدمة.",
                'type' => 'insurance_expiry_reminder',
                'priority' => 'high',
                'recipients' => [$patient],
                'reference_type' => get_class($patient),
                'reference_id' => $patient->id,
                'data' => [
                    'expiry_date' => $patient->insurance_expiry_date->format('Y-m-d'),
                    'days_remaining' => $daysRemaining
                ]
            ]);

            // إرسال إشعار لقسم التأمين
            $insuranceUsers = \App\Models\User::role('insurance_coordinator')->get();
            if ($insuranceUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'تأمين منتهي الصلاحية قريباً',
                    'message' => "تأمين المريض {$patient->name} سينتهي خلال {$daysRemaining} يوم",
                    'type' => 'insurance_expiry_alert',
                    'priority' => 'normal',
                    'recipients' => $insuranceUsers->toArray(),
                    'reference_type' => get_class($patient),
                    'reference_id' => $patient->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة انتهاء صلاحية التأمين: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * التحقق من وجود بيانات ناقصة
     */
    protected function hasIncompleteData($patient)
    {
        $requiredFields = ['phone', 'email', 'address', 'emergency_contact', 'emergency_phone'];
        
        foreach ($requiredFields as $field) {
            if (empty($patient->$field)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * جدولة تذكير لإكمال البيانات
     */
    protected function scheduleDataCompletionReminder($patient)
    {
        try {
            // إرسال تذكير بعد 24 ساعة
            $this->notificationService->schedule([
                'title' => 'إكمال البيانات الشخصية',
                'message' => "يرجى إكمال بياناتك الشخصية في ملفك الطبي لضمان تقديم أفضل خدمة طبية لك.",
                'type' => 'data_completion_reminder',
                'priority' => 'normal',
                'recipients' => [$patient],
                'reference_type' => get_class($patient),
                'reference_id' => $patient->id
            ], now()->addDay());

        } catch (\Exception $e) {
            Log::error('فشل في جدولة تذكير إكمال البيانات: ' . $e->getMessage());
        }
    }
}