<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class RadiologyOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'patient_id',
        'doctor_id',
        'radiology_study_id',
        'status',
        'priority',
        'ordered_at',
        'scheduled_at',
        'started_at',
        'completed_at',
        'reported_at',
        'clinical_indication',
        'clinical_history',
        'special_instructions',
        'contrast_used',
        'contrast_notes',
        'total_amount',
        'is_paid',
        'has_urgent_findings',
        'urgent_notified_at'
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'reported_at' => 'datetime',
        'urgent_notified_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'contrast_used' => 'boolean',
        'has_urgent_findings' => 'boolean'
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

    public function radiologyStudy(): BelongsTo
    {
        return $this->belongsTo(RadiologyStudy::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(RadiologyReport::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('ordered_at', today());
    }

    public function scopeScheduledToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['ordered', 'scheduled', 'in_progress']);
    }

    public function scopeUrgent($query)
    {
        return $query->whereIn('priority', ['urgent', 'stat']);
    }

    public function scopeWithUrgentFindings($query)
    {
        return $query->where('has_urgent_findings', true);
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'ordered' => 'مطلوب',
            'scheduled' => 'مجدول',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'reported' => 'تم التقرير',
            default => $this->status
        };
    }

    public function getPriorityDisplayAttribute(): string
    {
        return match($this->priority) {
            'routine' => 'عادي',
            'urgent' => 'عاجل',
            'stat' => 'طارئ',
            default => $this->priority
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'ordered' => 'warning',
            'scheduled' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'reported' => 'dark',
            default => 'secondary'
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'routine' => 'secondary',
            'urgent' => 'warning',
            'stat' => 'danger',
            default => 'secondary'
        };
    }

    // Helper methods
    public function canBeScheduled(): bool
    {
        return $this->status === 'ordered';
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'scheduled';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['ordered', 'scheduled']);
    }

    public function canBeReported(): bool
    {
        return $this->status === 'completed' && !$this->report;
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'completed' || $this->status === 'cancelled' || $this->status === 'reported') {
            return false;
        }

        if ($this->scheduled_at) {
            return now()->gt($this->scheduled_at->addMinutes($this->radiologyStudy->duration_minutes ?? 30));
        }

        // If not scheduled, check if it's been too long since ordering
        $expectedDuration = ($this->radiologyStudy->duration_minutes ?? 30) + 60; // Add 1 hour buffer
        $deadline = $this->ordered_at->addMinutes($expectedDuration);

        return now()->gt($deadline);
    }

    public function requiresPreparation(): bool
    {
        return $this->radiologyStudy && $this->radiologyStudy->requiresPreparation();
    }

    public function hasReport(): bool
    {
        return $this->report !== null;
    }

    public function isReported(): bool
    {
        return $this->status === 'reported' && $this->hasReport();
    }

    // Static methods
    public static function generateOrderNumber(): string
    {
        $prefix = 'RAD';
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = static::generateOrderNumber();
            }
            
            if (!$order->ordered_at) {
                $order->ordered_at = now();
            }
            
            if (!$order->total_amount && $order->radiologyStudy) {
                $order->total_amount = $order->radiologyStudy->price;
            }
        });

        static::updated(function ($order) {
            // Auto-update timestamps based on status changes
            if ($order->isDirty('status')) {
                switch ($order->status) {
                    case 'in_progress':
                        if (!$order->started_at) {
                            $order->started_at = now();
                        }
                        break;
                    case 'completed':
                        if (!$order->completed_at) {
                            $order->completed_at = now();
                        }
                        break;
                    case 'reported':
                        if (!$order->reported_at) {
                            $order->reported_at = now();
                        }
                        break;
                }
            }
        });
    }
}
