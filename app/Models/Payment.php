<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'patient_id',
        'payment_method',
        'status',
        'amount',
        'received_amount',
        'change_amount',
        'reference_number',
        'card_last_four',
        'card_type',
        'bank_name',
        'check_number',
        'insurance_company_id',
        'insurance_claim_number',
        'insurance_approval_date',
        'payment_date',
        'processed_at',
        'cleared_at',
        'notes',
        'payment_details',
        'audit_trail',
        'processed_by',
        'approved_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'processed_at' => 'datetime',
        'cleared_at' => 'datetime',
        'insurance_approval_date' => 'date',
        'payment_details' => 'array',
        'audit_trail' => 'array'
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
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

    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeCard($query)
    {
        return $query->where('payment_method', 'card');
    }

    public function scopeInsurance($query)
    {
        return $query->where('payment_method', 'insurance');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year);
    }

    // Accessors
    public function getPaymentMethodDisplayAttribute(): string
    {
        $methods = [
            'cash' => 'نقدي',
            'card' => 'بطاقة ائتمان',
            'bank_transfer' => 'تحويل بنكي',
            'check' => 'شيك',
            'insurance' => 'تأمين',
            'online' => 'دفع إلكتروني'
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'pending' => 'معلق',
            'completed' => 'مكتمل',
            'failed' => 'فاشل',
            'cancelled' => 'ملغي',
            'refunded' => 'مسترد'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsFailedAttribute(): bool
    {
        return $this->status === 'failed';
    }

    public function getIsClearedAttribute(): bool
    {
        return $this->cleared_at !== null;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ريال';
    }

    // Business Logic Methods
    public function complete(array $details = []): void
    {
        $this->status = 'completed';
        $this->processed_at = now();
        
        if (!empty($details)) {
            $this->payment_details = array_merge($this->payment_details ?? [], $details);
        }
        
        $this->addToAuditTrail('completed', 'Payment completed successfully');
        $this->save();
        
        // Update invoice payment status
        $this->invoice->updatePaymentStatus();
    }

    public function fail(string $reason = null): void
    {
        $this->status = 'failed';
        $this->addToAuditTrail('failed', $reason);
        $this->save();
    }

    public function cancel(string $reason = null): void
    {
        $this->status = 'cancelled';
        $this->addToAuditTrail('cancelled', $reason);
        $this->save();
        
        // Update invoice payment status
        $this->invoice->updatePaymentStatus();
    }

    public function refund(float $amount = null, string $reason = null): void
    {
        $refundAmount = $amount ?? $this->amount;
        
        $this->status = 'refunded';
        $this->addToAuditTrail('refunded', [
            'refund_amount' => $refundAmount,
            'reason' => $reason
        ]);
        $this->save();
        
        // Create a negative payment record for the refund
        $this->invoice->payments()->create([
            'payment_number' => $this->generateRefundNumber(),
            'patient_id' => $this->patient_id,
            'amount' => -$refundAmount,
            'payment_method' => $this->payment_method,
            'payment_date' => now(),
            'status' => 'completed',
            'reference_number' => 'REFUND-' . $this->payment_number,
            'notes' => "Refund for payment {$this->payment_number}: {$reason}",
            'processed_by' => auth()->id()
        ]);
        
        // Update invoice payment status
        $this->invoice->updatePaymentStatus();
    }

    public function approve(User $approver = null): void
    {
        $this->approved_by = $approver ? $approver->id : auth()->id();
        $this->addToAuditTrail('approved', 'Payment approved');
        $this->save();
    }

    public function clear(): void
    {
        $this->cleared_at = now();
        $this->addToAuditTrail('cleared', 'Payment cleared');
        $this->save();
    }

    public function addToAuditTrail(string $action, $details = null): void
    {
        $trail = $this->audit_trail ?? [];
        $trail[] = [
            'action' => $action,
            'details' => $details,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'System',
            'timestamp' => now()->toISOString()
        ];
        
        $this->audit_trail = $trail;
    }

    private function generateRefundNumber(): string
    {
        $prefix = 'REF';
        $year = date('Y');
        $month = date('m');
        
        $lastRefund = static::where('payment_number', 'like', "{$prefix}-{$year}{$month}-%")
                           ->orderBy('id', 'desc')
                           ->first();
        
        $sequence = $lastRefund ? (int)substr($lastRefund->payment_number, -4) + 1 : 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    // Static Methods
    public static function createCashPayment(Invoice $invoice, float $amount, float $receivedAmount = null): self
    {
        $receivedAmount = $receivedAmount ?? $amount;
        $changeAmount = max(0, $receivedAmount - $amount);
        
        return static::create([
            'payment_number' => static::generatePaymentNumber(),
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'payment_method' => 'cash',
            'amount' => $amount,
            'received_amount' => $receivedAmount,
            'change_amount' => $changeAmount,
            'payment_date' => now(),
            'status' => 'completed',
            'processed_by' => auth()->id()
        ]);
    }

    public static function createCardPayment(Invoice $invoice, float $amount, array $cardDetails): self
    {
        return static::create([
            'payment_number' => static::generatePaymentNumber(),
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'payment_method' => 'card',
            'amount' => $amount,
            'payment_date' => now(),
            'status' => 'pending',
            'card_last_four' => $cardDetails['last_four'] ?? null,
            'card_type' => $cardDetails['type'] ?? null,
            'reference_number' => $cardDetails['transaction_id'] ?? null,
            'payment_details' => $cardDetails,
            'processed_by' => auth()->id()
        ]);
    }

    public static function createInsurancePayment(Invoice $invoice, float $amount, InsuranceCompany $insurance, array $details = []): self
    {
        return static::create([
            'payment_number' => static::generatePaymentNumber(),
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'payment_method' => 'insurance',
            'amount' => $amount,
            'payment_date' => now(),
            'status' => 'pending',
            'insurance_company_id' => $insurance->id,
            'insurance_claim_number' => $details['claim_number'] ?? null,
            'insurance_approval_date' => $details['approval_date'] ?? null,
            'payment_details' => $details,
            'processed_by' => auth()->id()
        ]);
    }

    private static function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $year = date('Y');
        $month = date('m');
        
        $lastPayment = static::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->orderBy('id', 'desc')
                            ->first();
        
        $sequence = $lastPayment ? (int)substr($lastPayment->payment_number, -4) + 1 : 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            $payment->addToAuditTrail('created', 'Payment record created');
        });
    }
}