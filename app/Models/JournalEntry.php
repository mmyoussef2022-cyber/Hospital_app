<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'description_ar',
        'total_debit',
        'total_credit',
        'status',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'approved_at' => 'datetime'
    ];

    // العلاقات
    public function details(): HasMany
    {
        return $this->hasMany(JournalEntryDetail::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // النطاقات
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    // الخصائص المحسوبة
    public function getDescriptionAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->attributes['description'];
    }

    public function getIsBalancedAttribute(): bool
    {
        return $this->total_debit == $this->total_credit;
    }

    // الدوال المساعدة
    public function post(): bool
    {
        if (!$this->is_balanced) {
            return false;
        }

        $this->status = 'posted';
        $this->approved_by = auth()->id();
        $this->approved_at = now();
        
        return $this->save();
    }

    public function reverse(): bool
    {
        if ($this->status !== 'posted') {
            return false;
        }

        // إنشاء قيد عكسي
        $reversalEntry = static::create([
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => now()->toDateString(),
            'reference_type' => 'reversal',
            'reference_id' => $this->id,
            'description' => 'Reversal of entry: ' . $this->entry_number,
            'description_ar' => 'عكس القيد: ' . $this->entry_number,
            'total_debit' => $this->total_credit,
            'total_credit' => $this->total_debit,
            'status' => 'posted',
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // إنشاء تفاصيل القيد العكسي
        foreach ($this->details as $detail) {
            $reversalEntry->details()->create([
                'financial_account_id' => $detail->financial_account_id,
                'debit_amount' => $detail->credit_amount,
                'credit_amount' => $detail->debit_amount,
                'description' => 'Reversal: ' . $detail->description,
                'description_ar' => 'عكس: ' . $detail->description_ar
            ]);
        }

        // تحديث حالة القيد الأصلي
        $this->status = 'reversed';
        $this->save();

        return true;
    }

    public function addDetail(int $accountId, float $debitAmount = 0, float $creditAmount = 0, string $description = null): void
    {
        $this->details()->create([
            'financial_account_id' => $accountId,
            'debit_amount' => $debitAmount,
            'credit_amount' => $creditAmount,
            'description' => $description,
            'description_ar' => $description
        ]);

        $this->recalculateTotals();
    }

    public function recalculateTotals(): void
    {
        $this->total_debit = $this->details()->sum('debit_amount');
        $this->total_credit = $this->details()->sum('credit_amount');
        $this->save();
    }

    public static function generateEntryNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $lastEntry = static::whereYear('entry_date', $year)
                           ->whereMonth('entry_date', $month)
                           ->orderBy('entry_number', 'desc')
                           ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "JE{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // إنشاء قيد محاسبي للفاتورة
    public static function createForInvoice(Invoice $invoice): self
    {
        $entry = static::create([
            'entry_number' => static::generateEntryNumber(),
            'entry_date' => $invoice->invoice_date,
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'description' => 'Invoice #' . $invoice->invoice_number,
            'description_ar' => 'فاتورة رقم ' . $invoice->invoice_number,
            'total_debit' => $invoice->total_amount,
            'total_credit' => $invoice->total_amount,
            'status' => 'draft',
            'created_by' => auth()->id()
        ]);

        // حساب المدينين (المريض مدين للمستشفى)
        $receivableAccount = FinancialAccount::where('account_code', '1120')->first();
        $entry->addDetail($receivableAccount->id, $invoice->total_amount, 0, 'Patient receivable');

        // حساب الإيرادات (الخدمات الطبية دائنة)
        $revenueAccount = FinancialAccount::where('account_code', '4100')->first();
        $entry->addDetail($revenueAccount->id, 0, $invoice->total_amount, 'Medical services revenue');

        return $entry;
    }

    // إنشاء قيد محاسبي للدفع
    public static function createForPayment(FinancialPayment $payment): self
    {
        $entry = static::create([
            'entry_number' => static::generateEntryNumber(),
            'entry_date' => $payment->payment_date,
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
            'description' => 'Payment #' . $payment->payment_number,
            'description_ar' => 'دفعة رقم ' . $payment->payment_number,
            'total_debit' => $payment->amount,
            'total_credit' => $payment->amount,
            'status' => 'draft',
            'created_by' => auth()->id()
        ]);

        // حساب النقدية (النقدية مدينة)
        $cashAccount = FinancialAccount::where('account_code', '1110')->first();
        $entry->addDetail($cashAccount->id, $payment->amount, 0, 'Cash received');

        // حساب المدينين (المدينين دائنة - تقليل الدين)
        $receivableAccount = FinancialAccount::where('account_code', '1120')->first();
        $entry->addDetail($receivableAccount->id, 0, $payment->amount, 'Payment received from patient');

        return $entry;
    }
}