<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class DentalTreatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'treatment_number',
        'patient_id',
        'doctor_id',
        'treatment_type',
        'title',
        'description',
        'teeth_involved',
        'total_cost',
        'paid_amount',
        'remaining_amount',
        'total_sessions',
        'completed_sessions',
        'payment_type',
        'installment_months',
        'monthly_installment',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'status',
        'priority',
        'notes',
        'treatment_plan',
        'before_photos',
        'after_photos',
        'is_active'
    ];

    protected $casts = [
        'teeth_involved' => 'array',
        'treatment_plan' => 'array',
        'before_photos' => 'array',
        'after_photos' => 'array',
        'total_cost' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'is_active' => 'boolean'
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

    public function sessions(): HasMany
    {
        return $this->hasMany(DentalSession::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(DentalInstallment::class);
    }

    public function completedSessions(): HasMany
    {
        return $this->hasMany(DentalSession::class)->where('status', 'completed');
    }

    public function pendingInstallments(): HasMany
    {
        return $this->hasMany(DentalInstallment::class)->where('status', 'pending');
    }

    public function overdueInstallments(): HasMany
    {
        return $this->hasMany(DentalInstallment::class)->where('status', 'overdue');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByTreatmentType($query, $type)
    {
        return $query->where('treatment_type', $type);
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeWithInstallments($query)
    {
        return $query->where('payment_type', 'installments');
    }

    // Accessors
    public function getProgressPercentageAttribute()
    {
        if ($this->total_sessions == 0) return 0;
        return round(($this->completed_sessions / $this->total_sessions) * 100, 1);
    }

    public function getPaymentProgressPercentageAttribute()
    {
        if ($this->total_cost == 0) return 0;
        return round(($this->paid_amount / $this->total_cost) * 100, 1);
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'planned' => 'مخطط',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'on_hold' => 'معلق',
            default => 'غير محدد'
        };
    }

    public function getTreatmentTypeDisplayAttribute()
    {
        return match($this->treatment_type) {
            'orthodontics' => 'تقويم الأسنان',
            'implants' => 'زراعة الأسنان',
            'cosmetic' => 'تجميل الأسنان',
            'general' => 'علاج عام',
            'surgery' => 'جراحة الفم',
            'endodontics' => 'علاج الجذور',
            'periodontics' => 'علاج اللثة',
            'prosthodontics' => 'تركيبات الأسنان',
            default => 'غير محدد'
        };
    }

    public function getPriorityDisplayAttribute()
    {
        return match($this->priority) {
            'low' => 'منخفضة',
            'normal' => 'عادية',
            'high' => 'عالية',
            'urgent' => 'عاجلة',
            default => 'عادية'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'low' => 'secondary',
            'normal' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'primary'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'planned' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            'on_hold' => 'secondary',
            default => 'primary'
        };
    }

    public function getPaymentTypeDisplayAttribute()
    {
        return match($this->payment_type) {
            'cash' => 'نقدي',
            'installments' => 'أقساط',
            'insurance' => 'تأمين',
            default => 'نقدي'
        };
    }

    public function getDurationInDaysAttribute()
    {
        if (!$this->start_date || !$this->expected_end_date) return 0;
        return $this->start_date->diffInDays($this->expected_end_date);
    }

    public function getRemainingDaysAttribute()
    {
        if (!$this->expected_end_date) return null;
        if ($this->status === 'completed') return 0;
        
        $today = Carbon::today();
        if ($this->expected_end_date->isPast()) {
            return -$today->diffInDays($this->expected_end_date);
        }
        return $today->diffInDays($this->expected_end_date);
    }

    public function getIsOverdueAttribute()
    {
        return $this->expected_end_date && 
               $this->expected_end_date->isPast() && 
               $this->status !== 'completed';
    }

    public function getNextSessionAttribute()
    {
        return $this->sessions()
                   ->where('status', 'scheduled')
                   ->orderBy('scheduled_date')
                   ->first();
    }

    public function getLastSessionAttribute()
    {
        return $this->sessions()
                   ->where('status', 'completed')
                   ->orderBy('completed_date', 'desc')
                   ->first();
    }

    public function getNextInstallmentAttribute()
    {
        return $this->installments()
                   ->where('status', 'pending')
                   ->orderBy('due_date')
                   ->first();
    }

    // Helper methods
    public function updateProgress()
    {
        $completedSessions = $this->sessions()->where('status', 'completed')->count();
        $this->update(['completed_sessions' => $completedSessions]);
        
        // Auto-complete treatment if all sessions are done
        if ($completedSessions >= $this->total_sessions && $this->status !== 'completed') {
            $this->update([
                'status' => 'completed',
                'actual_end_date' => now()->toDateString()
            ]);
        }
        
        return $this;
    }

    public function updatePaymentAmount()
    {
        $paidAmount = $this->installments()->where('status', 'paid')->sum('paid_amount');
        $remainingAmount = $this->total_cost - $paidAmount;
        
        $this->update([
            'paid_amount' => $paidAmount,
            'remaining_amount' => max(0, $remainingAmount)
        ]);
        
        return $this;
    }

    public function generateInstallments()
    {
        if ($this->payment_type !== 'installments' || !$this->installment_months) {
            return false;
        }

        // Delete existing installments
        $this->installments()->delete();

        $monthlyAmount = $this->monthly_installment ?? ($this->total_cost / $this->installment_months);
        $startDate = Carbon::parse($this->start_date);

        for ($i = 1; $i <= $this->installment_months; $i++) {
            // Adjust last installment to cover any rounding differences
            $amount = ($i == $this->installment_months) 
                ? $this->total_cost - (($this->installment_months - 1) * $monthlyAmount)
                : $monthlyAmount;

            DentalInstallment::create([
                'installment_number' => $this->treatment_number . '-I' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'dental_treatment_id' => $this->id,
                'installment_order' => $i,
                'amount' => $amount,
                'due_date' => $startDate->copy()->addMonths($i - 1)->toDateString(),
                'status' => 'pending'
            ]);
        }

        return true;
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['planned', 'in_progress', 'on_hold']);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['planned', 'in_progress', 'on_hold']);
    }

    public function canBeCompleted()
    {
        return $this->status === 'in_progress' && $this->completed_sessions >= $this->total_sessions;
    }

    // Static methods
    public static function generateTreatmentNumber()
    {
        $lastTreatment = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastTreatment ? (int)substr($lastTreatment->treatment_number, 2) + 1 : 1;
        return 'DT' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public static function getTreatmentTypes()
    {
        return [
            'orthodontics' => 'تقويم الأسنان',
            'implants' => 'زراعة الأسنان',
            'cosmetic' => 'تجميل الأسنان',
            'general' => 'علاج عام',
            'surgery' => 'جراحة الفم',
            'endodontics' => 'علاج الجذور',
            'periodontics' => 'علاج اللثة',
            'prosthodontics' => 'تركيبات الأسنان'
        ];
    }

    public static function getPriorities()
    {
        return [
            'low' => 'منخفضة',
            'normal' => 'عادية',
            'high' => 'عالية',
            'urgent' => 'عاجلة'
        ];
    }

    public static function getStatuses()
    {
        return [
            'planned' => 'مخطط',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'on_hold' => 'معلق'
        ];
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($treatment) {
            if (empty($treatment->treatment_number)) {
                $treatment->treatment_number = self::generateTreatmentNumber();
            }
            
            // Calculate remaining amount
            $treatment->remaining_amount = $treatment->total_cost - $treatment->paid_amount;
        });

        static::updating(function ($treatment) {
            // Recalculate remaining amount
            $treatment->remaining_amount = $treatment->total_cost - $treatment->paid_amount;
        });
    }
}