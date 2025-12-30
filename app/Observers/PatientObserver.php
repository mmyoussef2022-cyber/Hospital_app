<?php

namespace App\Observers;

use App\Models\Patient;
use App\Services\EventDispatcherService;
use Illuminate\Support\Facades\Log;

class PatientObserver
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherService $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handle the Patient "created" event.
     */
    public function created(Patient $patient)
    {
        try {
            $this->eventDispatcher->dispatchPatientEvents($patient, 'registered');
        } catch (\Exception $e) {
            Log::error('فشل في معالجة تسجيل المريض: ' . $e->getMessage(), [
                'patient_id' => $patient->id
            ]);
        }
    }

    /**
     * Handle the Patient "updated" event.
     */
    public function updated(Patient $patient)
    {
        try {
            // التحقق من تغيير حالة التفعيل
            if ($patient->wasChanged('is_active')) {
                $this->eventDispatcher->dispatchPatientEvents($patient, 'status_changed');
            } else {
                $this->eventDispatcher->dispatchPatientEvents($patient, 'updated');
            }
        } catch (\Exception $e) {
            Log::error('فشل في معالجة تحديث المريض: ' . $e->getMessage(), [
                'patient_id' => $patient->id
            ]);
        }
    }

    /**
     * Handle the Patient "deleted" event.
     */
    public function deleted(Patient $patient)
    {
        try {
            Log::info('تم حذف مريض', [
                'patient_id' => $patient->id,
                'patient_name' => $patient->name
            ]);
        } catch (\Exception $e) {
            Log::error('فشل في معالجة حذف المريض: ' . $e->getMessage(), [
                'patient_id' => $patient->id
            ]);
        }
    }
}