<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'payment_type',
        'payment_type_ar',
        'amount',
        'patient_id',
        'invoice_id',
        'cash_register_id',
        'department_id',
        'description',
        'description_ar',
        'payment_date',
        'status',
        'payment_details',
        'received_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'payment_details' => 'array'
    ];

    // العلاقات
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(FinancialRefund::class, 'original_payment_id');
    }

    // النطاقات
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByPaymentType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    public function scopeCash($query)
    {
        return $query->where('payment_type', 'cash');
    }

    public function scopeCard($query)
    {
        return $query->where('payment_type', 'card');
    }

    // الخصائص المحسوبة
    public function getDescriptionAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->attributes['description'];
    }

    public function getPaymentTypeDisplayAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->payment_type_ar : $this->payment_type;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ريال';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'secondary'
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'pending' => app()->getLocale() === 'ar' ? 'في الانتظار' : 'Pending',
            'completed' => app()->getLocale() === 'ar' ? 'مكتمل' : 'Completed',
            'failed' => app()->getLocale() === 'ar' ? 'فاشل' : 'Failed',
            'refunded' => app()->getLocale() === 'ar' ? 'مسترد' : 'Refunded'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getTotalRefundedAttribute(): float
    {
        return $this->refunds()->where('status', 'completed')->sum('refund_amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->total_refunded;
    }

    public function getCanBeRefundedAttribute(): bool
    {
        return $this->status === 'completed' && $this->remaining_amount > 0;
    }

    // الدوال المساعدة
    public function complete(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'completed';
        $this->save();

        // إنشاء قيد محاسبي
        JournalEntry::createForPayment($this);

        // تحديث الفاتورة إذا كانت موجودة
        if ($this->invoice) {
            $this->invoice->addPayment($this->amount);
        }

        // تحديث الصندوق النقدي
        if ($this->cash_register && $this->payment_type === 'cash') {
            $this->cash_register->addTransaction($this->amount, 'payment', $this->payment_number);
        }

        return true;
    }

    public function fail(string $reason = null): bool
    {
        if ($this->status === 'completed') {
            return false; // لا يمكن تغيير حالة دفعة مكتملة
        }

        $this->status = 'failed';
        if ($reason) {
            $details = $this->payment_details ?? [];
            $details['failure_reason'] = $reason;
            $this->payment_details = $details;
        }
        $this->save();

        return true;
    }

    public function createRefund(float $amount, string $reason, string $method = null): FinancialRefund
    {
        if ($amount > $this->remaining_amount) {
            throw new \Exception('Refund amount cannot exceed remaining payment amount');
        }

        return $this->refunds()->create([
            'refund_number' => $this->generateRefundNumber(),
            'refund_amount' => $amount,
            'refund_reason' => $reason,
            'refund_reason_ar' => $reason,
            'refund_date' => now()->toDateString(),
            'refund_method' => $method ?? $this->payment_type,
            'refund_method_ar' => $this->payment_type_ar,
            'status' => 'pending',
            'requested_by' => auth()->id()
        ]);
    }

    private function generateRefundNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $lastRefund = FinancialRefund::whereYear('refund_date', $year)
                                   ->whereMonth('refund_date', $month)
                                   ->orderBy('refund_number', 'desc')
                                   ->first();

        if ($lastRefund) {
            $lastNumber = (int) substr($lastRefund->refund_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "RF{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function generatePaymentNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $lastPayment = static::whereYear('payment_date', $year)
                            ->whereMonth('payment_date', $month)
                            ->orderBy('payment_number', 'desc')
                            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "PAY{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // إنشاء دفعة جديدة
    public static function createPayment(array $data): self
    {
        $payment = static::create([
            'payment_number' => static::generatePaymentNumber(),
            'payment_type' => $data['payment_type'],
            'payment_type_ar' => $data['payment_type_ar'] ?? $data['payment_type'],
            'amount' => $data['amount'],
            'patient_id' => $data['patient_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'cash_register_id' => $data['cash_register_id'] ?? null,
            'department_id' => $data['department_id'],
            'description' => $data['description'] ?? null,
            'description_ar' => $data['description_ar'] ?? $data['description'],
            'payment_date' => $data['payment_date'] ?? now()->toDateString(),
            'status' => 'pending',
            'payment_details' => $data['payment_details'] ?? null,
            'received_by' => auth()->id()
        ]);

        return $payment;
    }

    // إحصائيات الدفعات
    public static function getDailyStats($date = null): array
    {
        $date = $date ?? today();

        return [
            'total_amount' => static::whereDate('payment_date', $date)->completed()->sum('amount'),
            'total_count' => static::whereDate('payment_date', $date)->completed()->count(),
            'cash_amount' => static::whereDate('payment_date', $date)->completed()->cash()->sum('amount'),
            'card_amount' => static::whereDate('payment_date', $date)->completed()->card()->sum('amount'),
            'pending_count' => static::whereDate('payment_date', $date)->pending()->count(),
            'failed_count' => static::whereDate('payment_date', $date)->failed()->count()
        ];
    }
}