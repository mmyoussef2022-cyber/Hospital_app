<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\Department;
use App\Models\QueueEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class QueueManagementService
{
    /**
     * إضافة مريض لطابور الانتظار
     */
    public function addPatientToQueue(Patient $patient, int $departmentId, string $priority = 'normal'): int
    {
        $queueNumber = $this->getNextQueueNumber($departmentId);
        
        // إنشاء إدخال في الطابور
        QueueEntry::create([
            'patient_id' => $patient->id,
            'department_id' => $departmentId,
            'queue_number' => $queueNumber,
            'priority' => $priority,
            'status' => 'waiting',
            'joined_at' => now(),
            'estimated_wait_time' => $this->calculateEstimatedWaitTime($departmentId)
        ]);

        // تحديث cache الطابور
        $this->updateQueueCache($departmentId);

        return $queueNumber;
    }

    /**
     * الحصول على رقم الطابور التالي
     */
    public function getNextQueueNumber(int $departmentId): int
    {
        $today = Carbon::today();
        
        $lastNumber = QueueEntry::where('department_id', $departmentId)
                                ->whereDate('created_at', $today)
                                ->max('queue_number') ?? 0;

        return $lastNumber + 1;
    }

    /**
     * حساب وقت الانتظار المتوقع
     */
    public function calculateEstimatedWaitTime(int $departmentId): int
    {
        // عدد المرضى في الانتظار
        $waitingCount = QueueEntry::where('department_id', $departmentId)
                                 ->where('status', 'waiting')
                                 ->count();

        // متوسط وقت الخدمة للقسم (بالدقائق)
        $averageServiceTime = $this->getAverageServiceTime($departmentId);

        return $waitingCount * $averageServiceTime;
    }

    /**
     * الحصول على متوسط وقت الخدمة للقسم
     */
    private function getAverageServiceTime(int $departmentId): int
    {
        $cacheKey = "avg_service_time_{$departmentId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($departmentId) {
            $lastWeek = Carbon::now()->subWeek();
            
            $averageTime = QueueEntry::where('department_id', $departmentId)
                                    ->where('status', 'completed')
                                    ->where('created_at', '>=', $lastWeek)
                                    ->whereNotNull('served_at')
                                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, joined_at, served_at)) as avg_time')
                                    ->value('avg_time');

            return (int) ($averageTime ?? 15); // افتراضي 15 دقيقة
        });
    }

    /**
     * الحصول على طابور قسم معين
     */
    public function getDepartmentQueue(int $departmentId): Collection
    {
        return QueueEntry::with(['patient', 'department'])
                         ->where('department_id', $departmentId)
                         ->where('status', 'waiting')
                         ->orderByRaw("CASE 
                             WHEN priority = 'emergency' THEN 1 
                             WHEN priority = 'urgent' THEN 2 
                             ELSE 3 
                         END")
                         ->orderBy('joined_at')
                         ->get();
    }

    /**
     * الحصول على جميع طوابير الأقسام
     */
    public function getAllDepartmentQueues(): array
    {
        $departments = Department::with(['queueEntries' => function($query) {
            $query->with('patient')
                  ->where('status', 'waiting')
                  ->orderByRaw("CASE 
                      WHEN priority = 'emergency' THEN 1 
                      WHEN priority = 'urgent' THEN 2 
                      ELSE 3 
                  END")
                  ->orderBy('joined_at');
        }])->get();

        $queues = [];
        foreach ($departments as $department) {
            $queues[] = [
                'department_id' => $department->id,
                'department_name' => $department->name_ar,
                'waiting_count' => $department->queueEntries->count(),
                'average_wait_time' => $this->getAverageServiceTime($department->id),
                'patients' => $department->queueEntries->map(function($entry) {
                    return [
                        'id' => $entry->id,
                        'patient_id' => $entry->patient_id,
                        'patient_name' => $entry->patient->name,
                        'queue_number' => $entry->queue_number,
                        'priority' => $entry->priority,
                        'joined_at' => $entry->joined_at->format('H:i'),
                        'estimated_wait_time' => $entry->estimated_wait_time,
                        'waiting_duration' => $entry->joined_at->diffInMinutes(now())
                    ];
                })
            ];
        }

        return $queues;
    }

    /**
     * استدعاء المريض التالي
     */
    public function callNextPatient(int $departmentId, ?int $doctorId = null): ?QueueEntry
    {
        $nextPatient = QueueEntry::with('patient')
                                ->where('department_id', $departmentId)
                                ->where('status', 'waiting')
                                ->orderByRaw("CASE 
                                    WHEN priority = 'emergency' THEN 1 
                                    WHEN priority = 'urgent' THEN 2 
                                    ELSE 3 
                                END")
                                ->orderBy('joined_at')
                                ->first();

        if ($nextPatient) {
            $nextPatient->update([
                'status' => 'called',
                'called_at' => now(),
                'doctor_id' => $doctorId
            ]);

            // تحديث زيارة المريض
            PatientVisit::where('patient_id', $nextPatient->patient_id)
                        ->where('status', 'waiting')
                        ->update([
                            'status' => 'called',
                            'called_at' => now(),
                            'doctor_id' => $doctorId
                        ]);

            // تحديث cache الطابور
            $this->updateQueueCache($departmentId);
        }

        return $nextPatient;
    }

    /**
     * تأكيد وصول المريض للطبيب
     */
    public function confirmPatientArrival(int $queueEntryId): bool
    {
        $queueEntry = QueueEntry::findOrFail($queueEntryId);
        
        $queueEntry->update([
            'status' => 'serving',
            'served_at' => now()
        ]);

        // تحديث زيارة المريض
        PatientVisit::where('patient_id', $queueEntry->patient_id)
                    ->where('status', 'called')
                    ->update([
                        'status' => 'in_progress',
                        'started_at' => now()
                    ]);

        // تحديث cache الطابور
        $this->updateQueueCache($queueEntry->department_id);

        return true;
    }

    /**
     * إكمال خدمة المريض
     */
    public function completePatientService(int $queueEntryId): bool
    {
        $queueEntry = QueueEntry::findOrFail($queueEntryId);
        
        $queueEntry->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // تحديث زيارة المريض
        PatientVisit::where('patient_id', $queueEntry->patient_id)
                    ->where('status', 'in_progress')
                    ->update([
                        'status' => 'completed',
                        'completed_at' => now()
                    ]);

        // تحديث cache الطابور
        $this->updateQueueCache($queueEntry->department_id);

        return true;
    }

    /**
     * إعطاء أولوية للحالات الطارئة
     */
    public function prioritizeEmergency(Patient $patient, int $departmentId): void
    {
        // تحديث جميع المرضى في الطابور لإعطاء أولوية للطوارئ
        QueueEntry::where('department_id', $departmentId)
                  ->where('status', 'waiting')
                  ->where('patient_id', '!=', $patient->id)
                  ->increment('queue_number');

        // إضافة المريض الطارئ في المقدمة
        QueueEntry::updateOrCreate(
            [
                'patient_id' => $patient->id,
                'department_id' => $departmentId,
                'status' => 'waiting'
            ],
            [
                'queue_number' => 0,
                'priority' => 'emergency',
                'joined_at' => now(),
                'estimated_wait_time' => 0
            ]
        );

        // تحديث cache الطابور
        $this->updateQueueCache($departmentId);
    }

    /**
     * إلغاء انتظار المريض
     */
    public function cancelPatientQueue(int $queueEntryId, string $reason = ''): bool
    {
        $queueEntry = QueueEntry::findOrFail($queueEntryId);
        
        $queueEntry->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason
        ]);

        // تحديث زيارة المريض
        PatientVisit::where('patient_id', $queueEntry->patient_id)
                    ->where('status', 'waiting')
                    ->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancellation_reason' => $reason
                    ]);

        // تحديث cache الطابور
        $this->updateQueueCache($queueEntry->department_id);

        return true;
    }

    /**
     * الحصول على إحصائيات الطابور
     */
    public function getQueueStatistics(int $departmentId): array
    {
        $today = Carbon::today();
        
        return [
            'total_today' => QueueEntry::where('department_id', $departmentId)
                                     ->whereDate('created_at', $today)
                                     ->count(),
            'waiting' => QueueEntry::where('department_id', $departmentId)
                                  ->where('status', 'waiting')
                                  ->count(),
            'serving' => QueueEntry::where('department_id', $departmentId)
                                  ->where('status', 'serving')
                                  ->count(),
            'completed_today' => QueueEntry::where('department_id', $departmentId)
                                          ->where('status', 'completed')
                                          ->whereDate('completed_at', $today)
                                          ->count(),
            'average_wait_time' => $this->getAverageServiceTime($departmentId),
            'emergency_cases' => QueueEntry::where('department_id', $departmentId)
                                          ->where('priority', 'emergency')
                                          ->whereDate('created_at', $today)
                                          ->count()
        ];
    }

    /**
     * تحديث cache الطابور
     */
    private function updateQueueCache(int $departmentId): void
    {
        $cacheKey = "department_queue_{$departmentId}";
        Cache::forget($cacheKey);
        
        // إعادة تحميل البيانات في cache
        Cache::remember($cacheKey, 300, function () use ($departmentId) {
            return $this->getDepartmentQueue($departmentId);
        });
    }

    /**
     * تنظيف الطوابير القديمة
     */
    public function cleanupOldQueues(): int
    {
        $cutoffDate = Carbon::now()->subDays(7);
        
        return QueueEntry::where('created_at', '<', $cutoffDate)
                         ->whereIn('status', ['completed', 'cancelled'])
                         ->delete();
    }

    /**
     * الحصول على موقع المريض في الطابور
     */
    public function getPatientQueuePosition(int $patientId, int $departmentId): ?array
    {
        $queueEntry = QueueEntry::where('patient_id', $patientId)
                               ->where('department_id', $departmentId)
                               ->where('status', 'waiting')
                               ->first();

        if (!$queueEntry) {
            return null;
        }

        // حساب الموقع في الطابور
        $position = QueueEntry::where('department_id', $departmentId)
                             ->where('status', 'waiting')
                             ->where(function($query) use ($queueEntry) {
                                 $query->where('priority', '>', $queueEntry->priority)
                                       ->orWhere(function($q) use ($queueEntry) {
                                           $q->where('priority', $queueEntry->priority)
                                             ->where('joined_at', '<', $queueEntry->joined_at);
                                       });
                             })
                             ->count() + 1;

        return [
            'queue_number' => $queueEntry->queue_number,
            'position' => $position,
            'estimated_wait_time' => $queueEntry->estimated_wait_time,
            'joined_at' => $queueEntry->joined_at,
            'priority' => $queueEntry->priority
        ];
    }
}