<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'refund_number',
        'original_payment_id',
        'refund_amount',
        'refund_reason',
        'refund_reason_ar',
        'refund_date',
        'refund_method',
        'refund_method_ar',
        'status',
        'requested_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'refund_date' => 'date',
        'approved_at' => 'datetime'
    ];

    // العلاقات
    public function originalPayment(): BelongsTo
    {
        return $this->belongsTo(FinancialPayment::class, 'original_payment_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // النطاقات
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('refund_date', [$startDate, $endDate]);
    }

    // الخصائص المحسوبة
    public function getRefundReasonAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->refund_reason_ar : $this->attributes['refund_reason'];
    }

    public function getRefundMethodDisplayAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->refund_method_ar : $this->refund_method;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->refund_amount, 2) . ' ريال';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'completed' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'pending' => app()->getLocale() === 'ar' ? 'في الانتظار' : 'Pending',
            'approved' => app()->getLocale() === 'ar' ? 'معتمد' : 'Approved',
            'completed' => app()->getLocale() === 'ar' ? 'مكتمل' : 'Completed',
            'rejected' => app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    // الدوال المساعدة
    public function approve(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'approved';
        $this->approved_by = auth()->id();
        $this->approved_at = now();
        $this->save();

        return true;
    }

    public function reject(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'rejected';
        $this->save();

        return true;
    }

    public function complete(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $this->status = 'completed';
        $this->save();

        // إنشاء قيد محاسبي للاسترداد
        $this->createJournalEntry();

        // تحديث حالة الدفعة الأصلية إذا تم استرداد كامل المبلغ
        $originalPayment = $this->originalPayment;
        if ($originalPayment->remaining_amount <= 0) {
            $originalPayment->status = 'refunded';
            $originalPayment->save();
        }

        return true;
    }

    private function createJournalEntry(): void
    {
        $entry = JournalEntry::create([
            'entry_number' => JournalEntry::generateEntryNumber(),
            'entry_date' => $this->refund_date,
            'reference_type' => 'refund',
            'reference_id' => $this->id,
            'description' => 'Refund #' . $this->refund_number,
            'description_ar' => 'استرداد رقم ' . $this->refund_number,
            'total_debit' => $this->refund_amount,
            'total_credit' => $this->refund_amount,
            'status' => 'posted',
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // حساب المدينين (زيادة الدين على المريض)
        $receivableAccount = FinancialAccount::where('account_code', '1120')->first();
        $entry->addDetail($receivableAccount->id, $this->refund_amount, 0, 'Refund to patient');

        // حساب النقدية (تقليل النقدية)
        $cashAccount = FinancialAccount::where('account_code', '1110')->first();
        $entry->addDetail($cashAccount->id, 0, $this->refund_amount, 'Cash refunded');
    }

    // إحصائيات الاستردادات
    public static function getDailyStats($date = null): array
    {
        $date = $date ?? today();

        return [
            'total_amount' => static::whereDate('refund_date', $date)->completed()->sum('refund_amount'),
            'total_count' => static::whereDate('refund_date', $date)->completed()->count(),
            'pending_count' => static::whereDate('refund_date', $date)->pending()->count(),
            'approved_count' => static::whereDate('refund_date', $date)->approved()->count(),
            'rejected_count' => static::whereDate('refund_date', $date)->rejected()->count()
        ];
    }
}