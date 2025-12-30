<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'financial_account_id',
        'transaction_number',
        'type',
        'category',
        'amount',
        'commission_amount',
        'net_amount',
        'currency',
        'appointment_id',
        'service_id',
        'dental_treatment_id',
        'dental_installment_id',
        'status',
        'processed_at',
        'completed_at',
        'payment_method',
        'payment_reference',
        'external_transaction_id',
        'description',
        'notes',
        'metadata',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(DoctorFinancialAccount::class, 'financial_account_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(DoctorService::class, 'service_id');
    }

    public function dentalTreatment(): BelongsTo
    {
        return $this->belongsTo(DentalTreatment::class);
    }

    public function dentalInstallment(): BelongsTo
    {
        return $this->belongsTo(DentalInstallment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebit($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        $sign = $this->type === 'credit' ? '+' : '-';
        return $sign . number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getFormattedNetAmountAttribute()
    {
        return number_format($this->net_amount, 2) . ' ' . $this->currency;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            'refunded' => 'info',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getTypeBadgeAttribute()
    {
        return $this->type === 'credit' ? 'success' : 'danger';
    }

    public function getCategoryNameAttribute()
    {
        $categories = [
            'service_payment' => __('app.service_payment'),
            'commission' => __('app.commission'),
            'bonus' => __('app.bonus'),
            'penalty' => __('app.penalty'),
            'withdrawal' => __('app.withdrawal'),
            'adjustment' => __('app.adjustment'),
            'refund' => __('app.refund'),
            'installment' => __('app.installment'),
        ];

        return $categories[$this->category] ?? $this->category;
    }

    public function getPaymentMethodNameAttribute()
    {
        $methods = [
            'cash' => __('app.cash'),
            'card' => __('app.card'),
            'bank_transfer' => __('app.bank_transfer'),
            'insurance' => __('app.insurance'),
            'installment' => __('app.installment'),
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    // Methods
    public function approve($approvedBy = null)
    {
        $this->update([
            'status' => 'completed',
            'approved_by' => $approvedBy ?? auth()->id(),
            'approved_at' => now(),
            'completed_at' => now(),
        ]);

        // Update financial account balance
        $this->financialAccount->updateBalance($this->net_amount, $this->type);

        return $this;
    }

    public function reject($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);

        return $this;
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);

        return $this;
    }

    public function refund($reason = null)
    {
        if ($this->status !== 'completed') {
            throw new \Exception('Only completed transactions can be refunded');
        }

        // Create reverse transaction
        $refundTransaction = $this->financialAccount->addTransaction(
            $this->type === 'credit' ? 'debit' : 'credit',
            'refund',
            $this->net_amount,
            'Refund for transaction: ' . $this->transaction_number,
            ['original_transaction_id' => $this->id, 'reason' => $reason]
        );

        $this->update(['status' => 'refunded']);
        $refundTransaction->approve();

        return $refundTransaction;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = static::generateTransactionNumber();
            }
        });
    }

    protected static function generateTransactionNumber()
    {
        $prefix = 'TXN';
        $date = now()->format('Ymd');
        $sequence = str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
        
        return $prefix . $date . $sequence;
    }
}