<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_visit_id',
        'queue_number',
        'department',
        'doctor_id',
        'priority_level',
        'status',
        'estimated_wait_time',
        'actual_wait_time',
        'called_at',
        'served_at',
        'notes'
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'served_at' => 'datetime'
    ];

    // Relationships
    public function patientVisit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeByPriority($query)
    {
        return $query->orderByRaw("
            CASE priority_level 
                WHEN 'critical' THEN 1 
                WHEN 'urgent' THEN 2 
                WHEN 'normal' THEN 3 
                WHEN 'low' THEN 4 
                ELSE 5 
            END
        ");
    }

    // Accessors
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'waiting' => 'في الانتظار',
            'called' => 'تم الاستدعاء',
            'in_consultation' => 'في الكشف',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'no_show' => 'لم يحضر',
            default => 'غير محدد'
        };
    }

    public function getPriorityDisplayAttribute()
    {
        return match($this->priority_level) {
            'critical' => 'حرج',
            'urgent' => 'عاجل',
            'normal' => 'عادي',
            'low' => 'منخفض',
            default => 'عادي'
        ];
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority_level) {
            'critical' => 'danger',
            'urgent' => 'warning',
            'normal' => 'primary',
            'low' => 'secondary',
            default => 'primary'
        };
    }

    public function getCurrentWaitTimeAttribute()
    {
        if ($this->status === 'waiting' && $this->created_at) {
            return $this->created_at->diffInMinutes(now());
        }
        return $this->actual_wait_time ?? 0;
    }

    public function getPositionInQueueAttribute()
    {
        return self::where('department', $this->department)
            ->where('status', 'waiting')
            ->where('created_at', '<', $this->created_at)
            ->count() + 1;
    }

    // Helper methods
    public function callPatient()
    {
        $this->update([
            'status' => 'called',
            'called_at' => now()
        ]);

        return $this;
    }

    public function startService()
    {
        $waitTime = $this->created_at->diffInMinutes(now());
        
        $this->update([
            'status' => 'in_consultation',
            'served_at' => now(),
            'actual_wait_time' => $waitTime
        ]);

        return $this;
    }

    public function completeService()
    {
        $this->update([
            'status' => 'completed'
        ]);

        return $this;
    }

    public function markNoShow()
    {
        $this->update([
            'status' => 'no_show'
        ]);

        return $this;
    }

    public function updateEstimatedWaitTime()
    {
        $patientsAhead = self::where('department', $this->department)
            ->where('status', 'waiting')
            ->where('queue_number', '<', $this->queue_number)
            ->count();

        $averageServiceTime = self::where('department', $this->department)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->avg('actual_wait_time') ?? 30;

        $estimatedTime = $patientsAhead * $averageServiceTime;

        $this->update(['estimated_wait_time' => $estimatedTime]);

        return $this;
    }

    // Static methods
    public static function getNextQueueNumber($department)
    {
        return self::where('department', $department)
            ->whereDate('created_at', today())
            ->max('queue_number') + 1;
    }

    public static function getDepartmentQueue($department, $status = 'waiting')
    {
        return self::with(['patientVisit.patient', 'doctor'])
            ->where('department', $department)
            ->where('status', $status)
            ->whereDate('created_at', today())
            ->byPriority()
            ->orderBy('queue_number')
            ->get();
    }

    public static function getDepartmentStatistics($department)
    {
        $today = today();
        
        return [
            'total_queue' => self::where('department', $department)->whereDate('created_at', $today)->count(),
            'waiting' => self::where('department', $department)->whereDate('created_at', $today)->where('status', 'waiting')->count(),
            'in_service' => self::where('department', $department)->whereDate('created_at', $today)->where('status', 'in_consultation')->count(),
            'completed' => self::where('department', $department)->whereDate('created_at', $today)->where('status', 'completed')->count(),
            'average_wait_time' => self::where('department', $department)
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->avg('actual_wait_time') ?? 0,
            'longest_wait' => self::where('department', $department)
                ->whereDate('created_at', $today)
                ->where('status', 'waiting')
                ->max('estimated_wait_time') ?? 0
        ];
    }

    public static function getAllDepartmentsOverview()
    {
        $departments = ['internal_medicine', 'cardiology', 'pediatrics', 'orthopedics', 'emergency'];
        $overview = [];

        foreach ($departments as $dept) {
            $overview[$dept] = self::getDepartmentStatistics($dept);
        }

        return $overview;
    }

    public static function getEmergencyQueue()
    {
        return self::with(['patientVisit.patient', 'doctor'])
            ->whereHas('patientVisit', function($query) {
                $query->where('is_emergency', true);
            })
            ->where('status', 'waiting')
            ->whereDate('created_at', today())
            ->byPriority()
            ->orderBy('created_at')
            ->get();
    }

    public static function getCriticalPatients()
    {
        return self::with(['patientVisit.patient', 'doctor'])
            ->where('priority_level', 'critical')
            ->whereIn('status', ['waiting', 'called'])
            ->whereDate('created_at', today())
            ->orderBy('created_at')
            ->get();
    }
}