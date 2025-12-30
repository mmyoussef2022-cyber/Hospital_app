<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DentalInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'installment_number',
        'dental_treatment_id',
        'installment_order',
        'amount',
        'due_date',
        'paid_date',
        'paid_amount',
        'late_fee',
        'status',
        'payment_method',
        'payment_reference',
        'payment_notes',
        'days_overdue',
        'reminder_sent',
        'reminder_sent_at',
        'payment_history'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'payment_history' => 'array'
    ];

    // Relationships
    public function dentalTreatment(): BelongsTo
    {
        return $this->belongsTo(DentalTreatment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->whereBetween('due_date', [today(), today()->addDays($days)]);
    }

    public function scopeByTreatment($query, $treatmentId)
    {
        return $query->where('dental_treatment_id', $treatmentId);
    }

    public function scopeNeedingReminder($query)
    {
        return $query->where('status', 'pending')
                    ->where('due_date', '<=', today()->addDays(3))
                    ->where('reminder_sent', false);
    }

    // Accessors
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'معلق',
            'paid' => 'مدفوع',
            'overdue' => 'متأخر',
            'partial' => 'مدفوع جزئياً',
            'cancelled' => 'ملغي',
            default => 'غير محدد'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'paid' => 'success',
            'overdue' => 'danger',
            'partial' => 'info',
            'cancelled' => 'secondary',
            default => 'primary'
        };
    }

    public function getPaymentMethodDisplayAttribute()
    {
        return match($this->payment_method) {
            'cash' => 'نقدي',
            'card' => 'بطاقة',
            'bank_transfer' => 'تحويل بنكي',
            'check' => 'شيك',
            default => 'غير محدد'
        };
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    public function getTotalAmountDueAttribute()
    {
        return $this->amount + $this->late_fee;
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date->isPast() && 
               in_array($this->status, ['pending', 'partial']);
    }

    public function getIsDueTodayAttribute()
    {
        return $this->due_date->isToday() && 
               in_array($this->status, ['pending', 'partial']);
    }

    public function getIsDueSoonAttribute()
    {
        return $this->due_date->between(today(), today()->addDays(7)) && 
               in_array($this->status, ['pending', 'partial']);
    }

    public function getDaysUntilDueAttribute()
    {
        if ($this->due_date->isPast()) {
            return -$this->due_date->diffInDays(today());
        }
        return today()->diffInDays($this->due_date);
    }

    public function getPaymentProgressPercentageAttribute()
    {
        if ($this->amount == 0) return 100;
        return round(($this->paid_amount / $this->amount) * 100, 1);
    }

    // Helper methods
    public function markAsPaid($amount = null, $paymentMethod = null, $reference = null, $notes = null)
    {
        $paymentAmount = $amount ?? $this->remaining_amount;
        $newPaidAmount = $this->paid_amount + $paymentAmount;
        
        // Add to payment history
        $history = $this->payment_history ?? [];
        $history[] = [
            'amount' => $paymentAmount,
            'date' => now()->toDateString(),
            'method' => $paymentMethod,
            'reference' => $reference,
            'notes' => $notes,
            'timestamp' => now()->toISOString()
        ];

        $updateData = [
            'paid_amount' => $newPaidAmount,
            'paid_date' => now()->toDateString(),
            'payment_history' => $history
        ];

        if ($paymentMethod) $updateData['payment_method'] = $paymentMethod;
        if ($reference) $updateData['payment_reference'] = $reference;
        if ($notes) $updateData['payment_notes'] = $notes;

        // Determine new status
        if ($newPaidAmount >= $this->amount) {
            $updateData['status'] = 'paid';
        } else {
            $updateData['status'] = 'partial';
        }

        $this->update($updateData);
        
        // Update treatment payment amount
        $this->dentalTreatment->updatePaymentAmount();
        
        return $this;
    }

    public function addLateFee($fee = null)
    {
        if (!$this->is_overdue) return $this;
        
        $lateFee = $fee ?? ($this->amount * 0.05); // 5% late fee
        $this->update([
            'late_fee' => $this->late_fee + $lateFee,
            'status' => 'overdue'
        ]);
        
        return $this;
    }

    public function calculateLateFee()
    {
        if (!$this->is_overdue) return 0;
        
        $daysOverdue = abs($this->days_until_due);
        $this->update(['days_overdue' => $daysOverdue]);
        
        // Calculate late fee: 1% per week overdue, max 10%
        $weeksOverdue = ceil($daysOverdue / 7);
        $lateFeePercentage = min($weeksOverdue * 0.01, 0.10);
        
        return $this->amount * $lateFeePercentage;
    }

    public function sendReminder()
    {
        if ($this->reminder_sent) return false;
        
        $this->update([
            'reminder_sent' => true,
            'reminder_sent_at' => now()
        ]);
        
        // TODO: Send actual reminder (SMS, Email, WhatsApp)
        
        return true;
    }

    public function canBePaid()
    {
        return in_array($this->status, ['pending', 'partial', 'overdue']);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'partial']);
    }

    public function cancel($reason = null)
    {
        if (!$this->canBeCancelled()) return false;
        
        $this->update([
            'status' => 'cancelled',
            'payment_notes' => $reason ? "ملغي: $reason" : 'ملغي'
        ]);
        
        return true;
    }

    public function reschedule($newDueDate)
    {
        if (!in_array($this->status, ['pending', 'partial'])) return false;
        
        $this->update([
            'due_date' => $newDueDate,
            'reminder_sent' => false,
            'reminder_sent_at' => null
        ]);
        
        return true;
    }

    // Static methods
    public static function generateInstallmentNumber($treatmentNumber, $installmentOrder)
    {
        return $treatmentNumber . '-I' . str_pad($installmentOrder, 2, '0', STR_PAD_LEFT);
    }

    public static function getStatuses()
    {
        return [
            'pending' => 'معلق',
            'paid' => 'مدفوع',
            'overdue' => 'متأخر',
            'partial' => 'مدفوع جزئياً',
            'cancelled' => 'ملغي'
        ];
    }

    public static function getPaymentMethods()
    {
        return [
            'cash' => 'نقدي',
            'card' => 'بطاقة',
            'bank_transfer' => 'تحويل بنكي',
            'check' => 'شيك'
        ];
    }

    public static function updateOverdueStatuses()
    {
        $overdueInstallments = self::where('status', 'pending')
                                  ->where('due_date', '<', today())
                                  ->get();

        foreach ($overdueInstallments as $installment) {
            $installment->update([
                'status' => 'overdue',
                'days_overdue' => $installment->due_date->diffInDays(today())
            ]);
        }

        return $overdueInstallments->count();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($installment) {
            if (empty($installment->installment_number)) {
                $treatment = DentalTreatment::find($installment->dental_treatment_id);
                if ($treatment) {
                    $installment->installment_number = self::generateInstallmentNumber(
                        $treatment->treatment_number, 
                        $installment->installment_order
                    );
                }
            }
        });

        static::updated(function ($installment) {
            // Update treatment payment amount when installment status changes
            if ($installment->wasChanged(['status', 'paid_amount'])) {
                $installment->dentalTreatment->updatePaymentAmount();
            }
        });
    }
}