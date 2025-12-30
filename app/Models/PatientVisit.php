<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PatientVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'visit_number',
        'visit_type',
        'visit_status',
        'check_in_time',
        'check_out_time',
        'waiting_time_minutes',
        'consultation_time_minutes',
        'priority_level',
        'department',
        'room_number',
        'chief_complaint',
        'vital_signs',
        'diagnosis',
        'treatment_plan',
        'follow_up_required',
        'follow_up_date',
        'notes',
        'insurance_approval',
        'payment_status',
        'total_amount',
        'insurance_amount',
        'patient_amount',
        'is_emergency',
        'emergency_level',
        'referred_from',
        'referred_to',
        'visit_outcome'
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'follow_up_date' => 'date',
        'vital_signs' => 'array',
        'treatment_plan' => 'array',
        'insurance_approval' => 'array',
        'total_amount' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'patient_amount' => 'decimal:2',
        'is_emergency' => 'boolean',
        'follow_up_required' => 'boolean'
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function queueEntry()
    {
        return $this->hasOne(QueueEntry::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('visit_status', $status);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeEmergency($query)
    {
        return $query->where('is_emergency', true);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    // Accessors
    public function getVisitStatusDisplayAttribute()
    {
        return match($this->visit_status) {
            'registered' => 'مسجل',
            'waiting' => 'في الانتظار',
            'in_consultation' => 'في الكشف',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'no_show' => 'لم يحضر',
            'transferred' => 'محول',
            default => 'غير محدد'
        };
    }

    public function getVisitTypeDisplayAttribute()
    {
        return match($this->visit_type) {
            'consultation' => 'استشارة',
            'follow_up' => 'متابعة',
            'emergency' => 'طوارئ',
            'surgery' => 'جراحة',
            'procedure' => 'إجراء',
            'lab' => 'مختبر',
            'radiology' => 'أشعة',
            default => 'استشارة'
        };
    }

    public function getPriorityLevelDisplayAttribute()
    {
        return match($this->priority_level) {
            'critical' => 'حرج',
            'urgent' => 'عاجل',
            'normal' => 'عادي',
            'low' => 'منخفض',
            default => 'عادي'
        };
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

    public function getEmergencyLevelDisplayAttribute()
    {
        if (!$this->is_emergency) return null;
        
        return match($this->emergency_level) {
            'level_1' => 'مستوى 1 - حرج جداً',
            'level_2' => 'مستوى 2 - حرج',
            'level_3' => 'مستوى 3 - عاجل',
            'level_4' => 'مستوى 4 - أقل عجلة',
            'level_5' => 'مستوى 5 - غير عاجل',
            default => 'غير محدد'
        ];
    }

    public function getTotalWaitingTimeAttribute()
    {
        if ($this->check_in_time && $this->visit_status === 'waiting') {
            return $this->check_in_time->diffInMinutes(now());
        }
        return $this->waiting_time_minutes ?? 0;
    }

    public function getEstimatedWaitingTimeAttribute()
    {
        // Calculate based on queue position and average consultation time
        $queueEntry = $this->queueEntry;
        if (!$queueEntry) return 0;

        $averageConsultationTime = 30; // minutes - can be made dynamic
        $patientsAhead = QueueEntry::where('department', $this->department)
            ->where('queue_number', '<', $queueEntry->queue_number)
            ->where('status', 'waiting')
            ->count();

        return $patientsAhead * $averageConsultationTime;
    }

    // Helper methods
    public function checkIn()
    {
        $this->update([
            'check_in_time' => now(),
            'visit_status' => 'waiting'
        ]);

        // Create queue entry
        $this->createQueueEntry();

        return $this;
    }

    public function startConsultation()
    {
        $this->update([
            'visit_status' => 'in_consultation'
        ]);

        // Update queue entry
        if ($this->queueEntry) {
            $this->queueEntry->update(['status' => 'in_consultation']);
        }

        return $this;
    }

    public function completeVisit($data = [])
    {
        $checkOutTime = now();
        $consultationTime = $this->check_in_time ? 
            $this->check_in_time->diffInMinutes($checkOutTime) : 0;

        $this->update(array_merge([
            'check_out_time' => $checkOutTime,
            'visit_status' => 'completed',
            'consultation_time_minutes' => $consultationTime
        ], $data));

        // Update queue entry
        if ($this->queueEntry) {
            $this->queueEntry->update(['status' => 'completed']);
        }

        return $this;
    }

    public function cancelVisit($reason = null)
    {
        $this->update([
            'visit_status' => 'cancelled',
            'notes' => $this->notes . "\nسبب الإلغاء: " . $reason
        ]);

        // Update queue entry
        if ($this->queueEntry) {
            $this->queueEntry->update(['status' => 'cancelled']);
        }

        return $this;
    }

    public function transferToDoctor($newDoctorId, $reason = null)
    {
        $this->update([
            'doctor_id' => $newDoctorId,
            'visit_status' => 'transferred',
            'referred_to' => $newDoctorId,
            'notes' => $this->notes . "\nسبب التحويل: " . $reason
        ]);

        return $this;
    }

    public function createQueueEntry()
    {
        if ($this->queueEntry) return $this->queueEntry;

        $nextQueueNumber = QueueEntry::where('department', $this->department)
            ->whereDate('created_at', today())
            ->max('queue_number') + 1;

        return $this->queueEntry()->create([
            'queue_number' => $nextQueueNumber,
            'department' => $this->department,
            'priority_level' => $this->priority_level,
            'status' => 'waiting',
            'estimated_wait_time' => $this->getEstimatedWaitingTimeAttribute()
        ]);
    }

    // Static methods
    public static function generateVisitNumber()
    {
        $today = now()->format('Ymd');
        $lastVisit = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastVisit ? 
            (int)substr($lastVisit->visit_number, -4) + 1 : 1;

        return 'V' . $today . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function getTodayStatistics()
    {
        $today = today();
        
        return [
            'total_visits' => self::whereDate('created_at', $today)->count(),
            'completed_visits' => self::whereDate('created_at', $today)->where('visit_status', 'completed')->count(),
            'waiting_visits' => self::whereDate('created_at', $today)->where('visit_status', 'waiting')->count(),
            'emergency_visits' => self::whereDate('created_at', $today)->where('is_emergency', true)->count(),
            'average_waiting_time' => self::whereDate('created_at', $today)
                ->where('visit_status', 'completed')
                ->avg('waiting_time_minutes') ?? 0,
            'average_consultation_time' => self::whereDate('created_at', $today)
                ->where('visit_status', 'completed')
                ->avg('consultation_time_minutes') ?? 0
        ];
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($visit) {
            if (empty($visit->visit_number)) {
                $visit->visit_number = self::generateVisitNumber();
            }
        });
    }
}