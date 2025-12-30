<?php

namespace App\Listeners;

use App\Models\MedicalRecord;
use App\Models\LabResult;
use App\Models\RadiologyReport;
use App\Models\Prescription;
use App\Services\NotificationService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Log;

class MedicalEventListener
{
    protected $notificationService;
    protected $auditLogService;

    public function __construct(NotificationService $notificationService, AuditLogService $auditLogService)
    {
        $this->notificationService = $notificationService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * معالجة إنشاء سجل طبي جديد
     */
    public function onMedicalRecordCreated($medicalRecord)
    {
        try {
            Log::info('تم إنشاء سجل طبي جديد', [
                'medical_record_id' => $medicalRecord->id,
                'patient_id' => $medicalRecord->patient_id,
                'doctor_id' => $medicalRecord->doctor_id
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'medical_record_created',
                'model' => 'MedicalRecord',
                'model_id' => $medicalRecord->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $medicalRecord->patient->name,
                    'doctor_name' => $medicalRecord->doctor->name,
                    'diagnosis' => $medicalRecord->diagnosis,
                    'visit_type' => $medicalRecord->visit_type
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'سجل طبي جديد',
                'message' => "تم إنشاء سجل طبي جديد لزيارتك مع د. {$medicalRecord->doctor->name}. يمكنك مراجعة التفاصيل في ملفك الطبي.",
                'type' => 'medical_record_created',
                'priority' => 'normal',
                'recipients' => [$medicalRecord->patient],
                'reference_type' => get_class($medicalRecord),
                'reference_id' => $medicalRecord->id
            ]);

            // التحقق من وجود تشخيص حرج
            if ($this->isCriticalDiagnosis($medicalRecord->diagnosis)) {
                $this->handleCriticalDiagnosis($medicalRecord);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة إنشاء السجل الطبي: ' . $e->getMessage(), [
                'medical_record_id' => $medicalRecord->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة نتيجة مختبر جديدة
     */
    public function onLabResultCreated($labResult)
    {
        try {
            Log::info('تم إنشاء نتيجة مختبر جديدة', [
                'lab_result_id' => $labResult->id,
                'patient_id' => $labResult->labOrder->patient_id,
                'test_name' => $labResult->labOrder->labTest->name
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'lab_result_created',
                'model' => 'LabResult',
                'model_id' => $labResult->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $labResult->labOrder->patient->name,
                    'test_name' => $labResult->labOrder->labTest->name,
                    'result_status' => $labResult->status,
                    'is_critical' => $labResult->is_critical
                ]
            ]);

            // إرسال إشعار للطبيب المطلوب
            $this->notificationService->send([
                'title' => 'نتيجة مختبر جاهزة',
                'message' => "نتيجة فحص {$labResult->labOrder->labTest->name} للمريض {$labResult->labOrder->patient->name} جاهزة للمراجعة.",
                'type' => 'lab_result_ready',
                'priority' => $labResult->is_critical ? 'critical' : 'normal',
                'recipients' => [$labResult->labOrder->doctor->user],
                'reference_type' => get_class($labResult),
                'reference_id' => $labResult->id,
                'data' => [
                    'patient_name' => $labResult->labOrder->patient->name,
                    'test_name' => $labResult->labOrder->labTest->name,
                    'result' => $labResult->result_value
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'نتيجة فحص جاهزة',
                'message' => "نتيجة فحص {$labResult->labOrder->labTest->name} جاهزة. يرجى مراجعة طبيبك لمناقشة النتائج.",
                'type' => 'lab_result_ready',
                'priority' => 'normal',
                'recipients' => [$labResult->labOrder->patient],
                'reference_type' => get_class($labResult),
                'reference_id' => $labResult->id
            ]);

            // معالجة النتائج الحرجة
            if ($labResult->is_critical) {
                $this->handleCriticalLabResult($labResult);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة نتيجة المختبر: ' . $e->getMessage(), [
                'lab_result_id' => $labResult->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة تقرير أشعة جديد
     */
    public function onRadiologyReportCreated($radiologyReport)
    {
        try {
            Log::info('تم إنشاء تقرير أشعة جديد', [
                'radiology_report_id' => $radiologyReport->id,
                'patient_id' => $radiologyReport->radiologyOrder->patient_id,
                'study_name' => $radiologyReport->radiologyOrder->radiologyStudy->name
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'radiology_report_created',
                'model' => 'RadiologyReport',
                'model_id' => $radiologyReport->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $radiologyReport->radiologyOrder->patient->name,
                    'study_name' => $radiologyReport->radiologyOrder->radiologyStudy->name,
                    'findings' => $radiologyReport->findings,
                    'is_urgent' => $radiologyReport->is_urgent
                ]
            ]);

            // إرسال إشعار للطبيب المطلوب
            $this->notificationService->send([
                'title' => 'تقرير أشعة جاهز',
                'message' => "تقرير {$radiologyReport->radiologyOrder->radiologyStudy->name} للمريض {$radiologyReport->radiologyOrder->patient->name} جاهز للمراجعة.",
                'type' => 'radiology_result_ready',
                'priority' => $radiologyReport->is_urgent ? 'high' : 'normal',
                'recipients' => [$radiologyReport->radiologyOrder->doctor->user],
                'reference_type' => get_class($radiologyReport),
                'reference_id' => $radiologyReport->id
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'تقرير أشعة جاهز',
                'message' => "تقرير {$radiologyReport->radiologyOrder->radiologyStudy->name} جاهز. يرجى مراجعة طبيبك لمناقشة النتائج.",
                'type' => 'radiology_result_ready',
                'priority' => 'normal',
                'recipients' => [$radiologyReport->radiologyOrder->patient],
                'reference_type' => get_class($radiologyReport),
                'reference_id' => $radiologyReport->id
            ]);

            // معالجة النتائج العاجلة
            if ($radiologyReport->is_urgent) {
                $this->handleUrgentRadiologyResult($radiologyReport);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة تقرير الأشعة: ' . $e->getMessage(), [
                'radiology_report_id' => $radiologyReport->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة وصفة طبية جديدة
     */
    public function onPrescriptionCreated($prescription)
    {
        try {
            Log::info('تم إنشاء وصفة طبية جديدة', [
                'prescription_id' => $prescription->id,
                'patient_id' => $prescription->patient_id,
                'doctor_id' => $prescription->doctor_id
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'prescription_created',
                'model' => 'Prescription',
                'model_id' => $prescription->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $prescription->patient->name,
                    'doctor_name' => $prescription->doctor->name,
                    'medications_count' => $prescription->medications ? count($prescription->medications) : 0
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'وصفة طبية جديدة',
                'message' => "تم إصدار وصفة طبية جديدة من د. {$prescription->doctor->name}. يرجى مراجعة الصيدلية لصرف الأدوية.",
                'type' => 'prescription_created',
                'priority' => 'normal',
                'recipients' => [$prescription->patient],
                'reference_type' => get_class($prescription),
                'reference_id' => $prescription->id
            ]);

            // إرسال إشعار للصيدلية
            $pharmacyUsers = \App\Models\User::role('pharmacist')->get();
            if ($pharmacyUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'وصفة طبية جديدة',
                    'message' => "وصفة طبية جديدة للمريض {$prescription->patient->name} من د. {$prescription->doctor->name}",
                    'type' => 'new_prescription_pharmacy',
                    'priority' => 'normal',
                    'recipients' => $pharmacyUsers->toArray(),
                    'reference_type' => get_class($prescription),
                    'reference_id' => $prescription->id
                ]);
            }

            // التحقق من التفاعلات الدوائية
            $this->checkDrugInteractions($prescription);

        } catch (\Exception $e) {
            Log::error('فشل في معالجة الوصفة الطبية: ' . $e->getMessage(), [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة التشخيص الحرج
     */
    protected function handleCriticalDiagnosis($medicalRecord)
    {
        try {
            Log::warning('تشخيص حرج تم اكتشافه', [
                'medical_record_id' => $medicalRecord->id,
                'diagnosis' => $medicalRecord->diagnosis
            ]);

            // إرسال إشعار حرج للطبيب المعالج
            $this->notificationService->sendCritical(
                'تشخيص حرج',
                "تم تسجيل تشخيص حرج للمريض {$medicalRecord->patient->name}: {$medicalRecord->diagnosis}",
                [$medicalRecord->doctor->user],
                'medical_critical'
            );

            // إرسال إشعار لرئيس القسم الطبي
            $chiefMedicalOfficer = \App\Models\User::role('chief_medical_officer')->first();
            if ($chiefMedicalOfficer) {
                $this->notificationService->sendCritical(
                    'تشخيص حرج - تتطلب مراجعة',
                    "تشخيص حرج للمريض {$medicalRecord->patient->name} من د. {$medicalRecord->doctor->name}: {$medicalRecord->diagnosis}",
                    [$chiefMedicalOfficer],
                    'medical_critical'
                );
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة التشخيص الحرج: ' . $e->getMessage());
        }
    }

    /**
     * معالجة نتيجة مختبر حرجة
     */
    protected function handleCriticalLabResult($labResult)
    {
        try {
            Log::warning('نتيجة مختبر حرجة', [
                'lab_result_id' => $labResult->id,
                'test_name' => $labResult->labOrder->labTest->name,
                'result_value' => $labResult->result_value
            ]);

            // إرسال إشعار حرج للطبيب
            $this->notificationService->sendCritical(
                'نتيجة مختبر حرجة',
                "نتيجة حرجة لفحص {$labResult->labOrder->labTest->name} للمريض {$labResult->labOrder->patient->name}. يرجى المراجعة فوراً.",
                [$labResult->labOrder->doctor->user],
                'lab_critical'
            );

            // إرسال إشعار لمشرف المختبر
            $labSupervisor = \App\Models\User::role('lab_supervisor')->first();
            if ($labSupervisor) {
                $this->notificationService->sendCritical(
                    'نتيجة مختبر حرجة - تم الإبلاغ',
                    "تم إبلاغ د. {$labResult->labOrder->doctor->name} بنتيجة حرجة لفحص {$labResult->labOrder->labTest->name} للمريض {$labResult->labOrder->patient->name}",
                    [$labSupervisor],
                    'lab_critical'
                );
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة نتيجة المختبر الحرجة: ' . $e->getMessage());
        }
    }

    /**
     * معالجة نتيجة أشعة عاجلة
     */
    protected function handleUrgentRadiologyResult($radiologyReport)
    {
        try {
            Log::warning('نتيجة أشعة عاجلة', [
                'radiology_report_id' => $radiologyReport->id,
                'study_name' => $radiologyReport->radiologyOrder->radiologyStudy->name,
                'findings' => $radiologyReport->findings
            ]);

            // إرسال إشعار عاجل للطبيب
            $this->notificationService->send([
                'title' => 'نتيجة أشعة عاجلة',
                'message' => "نتيجة عاجلة لفحص {$radiologyReport->radiologyOrder->radiologyStudy->name} للمريض {$radiologyReport->radiologyOrder->patient->name}. يرجى المراجعة.",
                'type' => 'radiology_urgent',
                'priority' => 'critical',
                'recipients' => [$radiologyReport->radiologyOrder->doctor->user],
                'reference_type' => get_class($radiologyReport),
                'reference_id' => $radiologyReport->id
            ]);

            // إرسال إشعار لمشرف الأشعة
            $radiologySupervisor = \App\Models\User::role('radiology_supervisor')->first();
            if ($radiologySupervisor) {
                $this->notificationService->send([
                    'title' => 'نتيجة أشعة عاجلة - تم الإبلاغ',
                    'message' => "تم إبلاغ د. {$radiologyReport->radiologyOrder->doctor->name} بنتيجة عاجلة لفحص {$radiologyReport->radiologyOrder->radiologyStudy->name}",
                    'type' => 'radiology_urgent',
                    'priority' => 'high',
                    'recipients' => [$radiologySupervisor],
                    'reference_type' => get_class($radiologyReport),
                    'reference_id' => $radiologyReport->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة نتيجة الأشعة العاجلة: ' . $e->getMessage());
        }
    }

    /**
     * التحقق من التفاعلات الدوائية
     */
    protected function checkDrugInteractions($prescription)
    {
        try {
            // هنا يمكن تنفيذ منطق التحقق من التفاعلات الدوائية
            // يمكن استخدام خدمة خارجية أو قاعدة بيانات محلية للتفاعلات

            $interactions = $this->findDrugInteractions($prescription->medications);
            
            if (!empty($interactions)) {
                Log::warning('تفاعلات دوائية محتملة', [
                    'prescription_id' => $prescription->id,
                    'interactions' => $interactions
                ]);

                // إرسال تحذير للطبيب
                $this->notificationService->send([
                    'title' => 'تحذير: تفاعلات دوائية محتملة',
                    'message' => "تم اكتشاف تفاعلات دوائية محتملة في الوصفة الطبية للمريض {$prescription->patient->name}. يرجى المراجعة.",
                    'type' => 'drug_interaction_warning',
                    'priority' => 'high',
                    'recipients' => [$prescription->doctor->user],
                    'reference_type' => get_class($prescription),
                    'reference_id' => $prescription->id,
                    'data' => [
                        'interactions' => $interactions
                    ]
                ]);

                // إرسال تحذير للصيدلي
                $pharmacyUsers = \App\Models\User::role('pharmacist')->get();
                if ($pharmacyUsers->count() > 0) {
                    $this->notificationService->send([
                        'title' => 'تحذير: تفاعلات دوائية',
                        'message' => "تفاعلات دوائية محتملة في وصفة المريض {$prescription->patient->name}",
                        'type' => 'drug_interaction_warning',
                        'priority' => 'high',
                        'recipients' => $pharmacyUsers->toArray(),
                        'reference_type' => get_class($prescription),
                        'reference_id' => $prescription->id
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('فشل في التحقق من التفاعلات الدوائية: ' . $e->getMessage());
        }
    }

    /**
     * التحقق من كون التشخيص حرجاً
     */
    protected function isCriticalDiagnosis($diagnosis)
    {
        $criticalKeywords = [
            'سرطان', 'cancer', 'tumor', 'ورم خبيث',
            'جلطة', 'stroke', 'heart attack', 'نوبة قلبية',
            'فشل كلوي', 'kidney failure', 'renal failure',
            'غيبوبة', 'coma', 'unconscious',
            'نزيف داخلي', 'internal bleeding',
            'التهاب السحايا', 'meningitis'
        ];

        foreach ($criticalKeywords as $keyword) {
            if (stripos($diagnosis, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * البحث عن التفاعلات الدوائية
     */
    protected function findDrugInteractions($medications)
    {
        // هذه دالة مبسطة للمثال
        // في التطبيق الحقيقي، يجب استخدام قاعدة بيانات شاملة للتفاعلات الدوائية
        
        $interactions = [];
        
        if (is_array($medications) && count($medications) > 1) {
            // فحص مبسط للتفاعلات الشائعة
            $drugNames = array_column($medications, 'name');
            
            // مثال: تفاعل الوارفارين مع الأسبرين
            if (in_array('warfarin', $drugNames) && in_array('aspirin', $drugNames)) {
                $interactions[] = [
                    'drugs' => ['warfarin', 'aspirin'],
                    'severity' => 'high',
                    'description' => 'زيادة خطر النزيف'
                ];
            }
        }
        
        return $interactions;
    }
}