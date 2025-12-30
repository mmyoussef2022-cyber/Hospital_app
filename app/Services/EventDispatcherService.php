<?php

namespace App\Services;

use App\Events\AppointmentCreated;
use App\Events\AppointmentUpdated;
use App\Events\PaymentReceived;
use App\Events\LabResultCreated;
use App\Events\PatientRegistered;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\LabResult;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\InsuranceClaim;
use Illuminate\Support\Facades\Log;

class EventDispatcherService
{
    /**
     * إرسال أحداث المواعيد
     */
    public function dispatchAppointmentEvents($appointment, $eventType)
    {
        try {
            switch ($eventType) {
                case 'created':
                    event(new AppointmentCreated($appointment));
                    break;
                case 'updated':
                    event(new AppointmentUpdated($appointment));
                    break;
                case 'cancelled':
                    $this->handleAppointmentCancellation($appointment);
                    break;
                case 'completed':
                    $this->handleAppointmentCompletion($appointment);
                    break;
                case 'no_show':
                    $this->handleAppointmentNoShow($appointment);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('فشل في إرسال حدث الموعد: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id,
                'event_type' => $eventType
            ]);
        }
    }

    /**
     * إرسال أحداث المدفوعات
     */
    public function dispatchPaymentEvents($payment, $eventType)
    {
        try {
            switch ($eventType) {
                case 'received':
                    event(new PaymentReceived($payment));
                    break;
                case 'failed':
                    $this->handlePaymentFailure($payment);
                    break;
                case 'refunded':
                    $this->handlePaymentRefund($payment);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('فشل في إرسال حدث الدفع: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'event_type' => $eventType
            ]);
        }
    }

    /**
     * إرسال أحداث المختبر
     */
    public function dispatchLabEvents($labResult, $eventType)
    {
        try {
            switch ($eventType) {
                case 'result_created':
                    event(new LabResultCreated($labResult));
                    break;
                case 'critical_result':
                    $this->handleCriticalLabResult($labResult);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('فشل في إرسال حدث المختبر: ' . $e->getMessage(), [
                'lab_result_id' => $labResult->id,
                'event_type' => $eventType
            ]);
        }
    }

    /**
     * إرسال أحداث المرضى
     */
    public function dispatchPatientEvents($patient, $eventType, $additionalData = [])
    {
        try {
            switch ($eventType) {
                case 'registered':
                    event(new PatientRegistered($patient));
                    break;
                case 'updated':
                    $this->handlePatientUpdate($patient);
                    break;
                case 'allergy_added':
                    $this->handleAllergyAddition($patient, $additionalData['allergy'] ?? null);
                    break;
                case 'status_changed':
                    $this->handlePatientStatusChange($patient);
                    break;
                case 'birthday':
                    $this->handlePatientBirthday($patient);
                    break;
                case 'insurance_expiring':
                    $this->handleInsuranceExpiry($patient);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('فشل في إرسال حدث المريض: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'event_type' => $eventType
            ]);
        }
    }

    /**
     * إرسال أحداث التأمين
     */
    public function dispatchInsuranceEvents($model, $eventType, $additionalData = [])
    {
        try {
            switch ($eventType) {
                case 'claim_created':
                    $this->handleInsuranceClaimCreation($model);
                    break;
                case 'claim_approved':
                    $this->handleInsuranceClaimApproval($model);
                    break;
                case 'claim_rejected':
                    $this->handleInsuranceClaimRejection($model);
                    break;
                case 'policy_expiring':
                    $this->handlePolicyExpiry($model);
                    break;
                case 'coverage_exceeded':
                    $this->handleCoverageExceeded($model, $additionalData['amount'] ?? 0);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('فشل في إرسال حدث التأمين: ' . $e->getMessage(), [
                'model_id' => $model->id,
                'event_type' => $eventType
            ]);
        }
    }

    /**
     * معالجة إلغاء الموعد
     */
    protected function handleAppointmentCancellation($appointment)
    {
        $listener = app(\App\Listeners\AppointmentEventListener::class);
        $listener->onAppointmentCancelled($appointment);
    }

    /**
     * معالجة إكمال الموعد
     */
    protected function handleAppointmentCompletion($appointment)
    {
        $listener = app(\App\Listeners\AppointmentEventListener::class);
        $listener->onAppointmentCompleted($appointment);
    }

    /**
     * معالجة عدم حضور الموعد
     */
    protected function handleAppointmentNoShow($appointment)
    {
        $listener = app(\App\Listeners\AppointmentEventListener::class);
        $listener->onAppointmentNoShow($appointment);
    }

    /**
     * معالجة فشل الدفع
     */
    protected function handlePaymentFailure($payment)
    {
        $listener = app(\App\Listeners\FinancialEventListener::class);
        $listener->onPaymentFailed($payment);
    }

    /**
     * معالجة استرداد الدفع
     */
    protected function handlePaymentRefund($payment)
    {
        $listener = app(\App\Listeners\FinancialEventListener::class);
        $listener->onPaymentRefunded($payment);
    }

    /**
     * معالجة نتيجة مختبر حرجة
     */
    protected function handleCriticalLabResult($labResult)
    {
        $listener = app(\App\Listeners\MedicalEventListener::class);
        $listener->onLabResultCreated($labResult);
    }

    /**
     * معالجة تحديث المريض
     */
    protected function handlePatientUpdate($patient)
    {
        $listener = app(\App\Listeners\PatientEventListener::class);
        $listener->onPatientUpdated($patient);
    }

    /**
     * معالجة إضافة حساسية
     */
    protected function handleAllergyAddition($patient, $allergy)
    {
        $listener = app(\App\Listeners\PatientEventListener::class);
        $listener->onAllergyAdded($patient, $allergy);
    }

    /**
     * معالجة تغيير حالة المريض
     */
    protected function handlePatientStatusChange($patient)
    {
        $listener = app(\App\Listeners\PatientEventListener::class);
        $listener->onPatientStatusChanged($patient);
    }

    /**
     * معالجة عيد ميلاد المريض
     */
    protected function handlePatientBirthday($patient)
    {
        $listener = app(\App\Listeners\PatientEventListener::class);
        $listener->onPatientBirthday($patient);
    }

    /**
     * معالجة انتهاء صلاحية التأمين
     */
    protected function handleInsuranceExpiry($patient)
    {
        $listener = app(\App\Listeners\PatientEventListener::class);
        $listener->onInsuranceExpiring($patient);
    }

    /**
     * معالجة إنشاء مطالبة تأمين
     */
    protected function handleInsuranceClaimCreation($claim)
    {
        $listener = app(\App\Listeners\InsuranceEventListener::class);
        $listener->onInsuranceClaimCreated($claim);
    }

    /**
     * معالجة موافقة مطالبة تأمين
     */
    protected function handleInsuranceClaimApproval($claim)
    {
        $listener = app(\App\Listeners\InsuranceEventListener::class);
        $listener->onInsuranceClaimApproved($claim);
    }

    /**
     * معالجة رفض مطالبة تأمين
     */
    protected function handleInsuranceClaimRejection($claim)
    {
        $listener = app(\App\Listeners\InsuranceEventListener::class);
        $listener->onInsuranceClaimRejected($claim);
    }

    /**
     * معالجة انتهاء صلاحية البوليصة
     */
    protected function handlePolicyExpiry($policy)
    {
        $listener = app(\App\Listeners\InsuranceEventListener::class);
        $listener->onInsurancePolicyExpiring($policy);
    }

    /**
     * معالجة تجاوز حد التغطية
     */
    protected function handleCoverageExceeded($patientInsurance, $amount)
    {
        $listener = app(\App\Listeners\InsuranceEventListener::class);
        $listener->onCoverageLimitExceeded($patientInsurance, $amount);
    }

    /**
     * إرسال أحداث متعددة دفعة واحدة
     */
    public function dispatchBulkEvents($events)
    {
        foreach ($events as $event) {
            try {
                $this->dispatchSingleEvent($event);
            } catch (\Exception $e) {
                Log::error('فشل في إرسال حدث مجمع: ' . $e->getMessage(), [
                    'event' => $event
                ]);
            }
        }
    }

    /**
     * إرسال حدث واحد
     */
    protected function dispatchSingleEvent($eventData)
    {
        $model = $eventData['model'];
        $eventType = $eventData['event_type'];
        $additionalData = $eventData['additional_data'] ?? [];

        switch (get_class($model)) {
            case Appointment::class:
                $this->dispatchAppointmentEvents($model, $eventType);
                break;
            case Payment::class:
                $this->dispatchPaymentEvents($model, $eventType);
                break;
            case LabResult::class:
                $this->dispatchLabEvents($model, $eventType);
                break;
            case Patient::class:
                $this->dispatchPatientEvents($model, $eventType, $additionalData);
                break;
            case InsuranceClaim::class:
                $this->dispatchInsuranceEvents($model, $eventType, $additionalData);
                break;
        }
    }

    /**
     * جدولة أحداث مؤجلة
     */
    public function scheduleDelayedEvents($events, $delay)
    {
        foreach ($events as $event) {
            \Illuminate\Support\Facades\Queue::later($delay, function() use ($event) {
                $this->dispatchSingleEvent($event);
            });
        }
    }
}