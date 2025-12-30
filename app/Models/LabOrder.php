<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'patient_id',
        'doctor_id',
        'lab_test_id',
        'status',
        'priority',
        'ordered_at',
        'collected_at',
        'completed_at',
        'clinical_notes',
        'collection_notes',
        'total_amount',
        'is_paid'
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'collected_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'is_paid' => 'boolean'
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

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabResult::class);
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

    public function scopePending($query)
    {
        return $query->whereIn('status', ['ordered', 'collected', 'processing']);
    }

    public function scopeUrgent($query)
    {
        return $query->whereIn('priority', ['urgent', 'stat']);
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'ordered' => 'مطلوب',
            'collected' => 'تم جمع العينة',
            'processing' => 'قيد المعالجة',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
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
            'collected' => 'info',
            'processing' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
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
    public function canBeCollected(): bool
    {
        return $this->status === 'ordered';
    }

    public function canBeProcessed(): bool
    {
        return $this->status === 'collected';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'processing';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['ordered', 'collected']);
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return false;
        }

        $expectedDuration = $this->labTest->duration_minutes ?? 60;
        $deadline = $this->ordered_at->addMinutes($expectedDuration);

        return now()->gt($deadline);
    }

    public function hasCriticalResults(): bool
    {
        return $this->results()->where('is_critical', true)->exists();
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['ordered', 'collected']);
    }

    public function canAddResults(): bool
    {
        return $this->status === 'processing' || $this->status === 'collected';
    }

    public function canBeVerified(): bool
    {
        return $this->status === 'completed' && $this->results()->exists();
    }

    public function getHasCriticalValuesAttribute(): bool
    {
        return $this->hasCriticalResults();
    }

    // Static methods
    public static function generateOrderNumber(): string
    {
        $prefix = 'LAB';
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
            
            if (!$order->total_amount && $order->labTest) {
                $order->total_amount = $order->labTest->price;
            }
        });
    }
}
