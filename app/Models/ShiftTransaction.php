<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ShiftTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'shift_id',
        'cash_register_id',
        'patient_id',
        'invoice_id',
        'payment_id',
        'transaction_type',
        'payment_method',
        'amount',
        'received_amount',
        'change_amount',
        'reference_number',
        'card_last_four',
        'card_type',
        'bank_name',
        'check_number',
        'description',
        'notes',
        'status',
        'transaction_date',
        'processed_by',
        'approved_by',
        'transaction_details',
        'audit_trail'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'transaction_details' => 'array',
        'audit_trail' => 'array'
    ];

    // Relationships
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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

    public function scopePayments($query)
    {
        return $query->where('transaction_type', 'payment');
    }

    public function scopeRefunds($query)
    {
        return $query->where('transaction_type', 'refund');
    }

    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeCard($query)
    {
        return $query->where('payment_method', 'card');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    public function scopeThisShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    // Accessors
    public function getTransactionTypeDisplayAttribute(): string
    {
        $types = [
            'payment' => 'دفعة',
            'refund' => 'استرداد',
            'adjustment' => 'تعديل',
            'opening_balance' => 'رصيد افتتاحي',
            'closing_balance' => 'رصيد ختامي',
            'expense' => 'مصروف',
            'deposit' => 'إيداع'
        ];

        return $types[$this->transaction_type] ?? $this->transaction_type;
    }

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

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ريال';
    }

    public function getFormattedReceivedAmountAttribute(): string
    {
        return $this->received_amount ? number_format($this->received_amount, 2) . ' ريال' : '-';
    }

    public function getFormattedChangeAmountAttribute(): string
    {
        return $this->change_amount > 0 ? number_format($this->change_amount, 2) . ' ريال' : '-';
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

    public function getIsCashTransactionAttribute(): bool
    {
        return $this->payment_method === 'cash';
    }

    public function getIsCardTransactionAttribute(): bool
    {
        return $this->payment_method === 'card';
    }

    public function getIsPaymentAttribute(): bool
    {
        return $this->transaction_type === 'payment';
    }

    public function getIsRefundAttribute(): bool
    {
        return $this->transaction_type === 'refund';
    }

    public function getNetAmountAttribute(): float
    {
        return $this->is_payment ? $this->amount : -$this->amount;
    }

    // Business Logic Methods
    public function complete(array $details = []): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('يمكن إكمال المعاملات المعلقة فقط');
        }

        $this->status = 'completed';
        
        if (!empty($details)) {
            $this->transaction_details = array_merge($this->transaction_details ?? [], $details);
        }

        $this->addToAuditTrail('completed', 'Transaction completed successfully');
        $this->save();

        // Update shift statistics
        $this->shift->addTransaction($this);

        // Update cash register balance
        if ($this->cashRegister) {
            $this->cashRegister->addTransaction(
                $this->net_amount,
                $this->transaction_type,
                [
                    'transaction_id' => $this->id,
                    'payment_method' => $this->payment_method,
                    'reference' => $this->reference_number
                ]
            );
        }
    }

    public function fail(string $reason = null): void
    {
        $this->status = 'failed';
        $this->addToAuditTrail('failed', $reason);
        $this->save();
    }

    public function cancel(string $reason = null): void
    {
        if (!in_array($this->status, ['pending', 'completed'])) {
            throw new \Exception('لا يمكن إلغاء هذه المعاملة');
        }

        $oldStatus = $this->status;
        $this->status = 'cancelled';
        $this->addToAuditTrail('cancelled', [
            'reason' => $reason,
            'previous_status' => $oldStatus
        ]);
        $this->save();

        // Reverse cash register transaction if it was completed
        if ($oldStatus === 'completed' && $this->cashRegister) {
            $this->cashRegister->addTransaction(
                -$this->net_amount,
                'adjustment',
                [
                    'reason' => 'Transaction cancellation',
                    'original_transaction_id' => $this->id
                ]
            );
        }
    }

    public function refund(float $refundAmount = null, string $reason = null): self
    {
        if ($this->status !== 'completed' || !$this->is_payment) {
            throw new \Exception('يمكن استرداد المدفوعات المكتملة فقط');
        }

        $refundAmount = $refundAmount ?? $this->amount;
        
        if ($refundAmount > $this->amount) {
            throw new \Exception('مبلغ الاسترداد لا يمكن أن يكون أكبر من مبلغ المعاملة الأصلية');
        }

        // Create refund transaction
        $refund = static::create([
            'transaction_number' => static::generateTransactionNumber(),
            'shift_id' => $this->shift_id,
            'cash_register_id' => $this->cash_register_id,
            'patient_id' => $this->patient_id,
            'invoice_id' => $this->invoice_id,
            'transaction_type' => 'refund',
            'payment_method' => $this->payment_method,
            'amount' => $refundAmount,
            'reference_number' => 'REF-' . $this->transaction_number,
            'description' => "Refund for transaction {$this->transaction_number}",
            'notes' => $reason,
            'status' => 'completed',
            'transaction_date' => now(),
            'processed_by' => auth()->id(),
            'transaction_details' => [
                'original_transaction_id' => $this->id,
                'original_amount' => $this->amount,
                'refund_reason' => $reason
            ]
        ]);

        // Update original transaction status if fully refunded
        if ($refundAmount >= $this->amount) {
            $this->status = 'refunded';
            $this->save();
        }

        $this->addToAuditTrail('refunded', [
            'refund_amount' => $refundAmount,
            'refund_transaction_id' => $refund->id,
            'reason' => $reason
        ]);

        return $refund;
    }

    public function approve(User $approver = null): void
    {
        $this->approved_by = $approver ? $approver->id : auth()->id();
        $this->addToAuditTrail('approved', 'Transaction approved');
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

    // Static Methods
    public static function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        $lastTransaction = static::whereDate('created_at', today())
                                ->orderBy('id', 'desc')
                                ->first();
        
        $sequence = $lastTransaction ? (int)substr($lastTransaction->transaction_number, -4) + 1 : 1;
        
        return sprintf('%s-%s%s%s-%04d', $prefix, $year, $month, $day, $sequence);
    }

    public static function createFromPayment(Payment $payment, Shift $shift): self
    {
        return static::create([
            'transaction_number' => static::generateTransactionNumber(),
            'shift_id' => $shift->id,
            'cash_register_id' => $shift->cash_register_id,
            'patient_id' => $payment->patient_id,
            'invoice_id' => $payment->invoice_id,
            'payment_id' => $payment->id,
            'transaction_type' => 'payment',
            'payment_method' => $payment->payment_method,
            'amount' => $payment->amount,
            'received_amount' => $payment->received_amount,
            'change_amount' => $payment->change_amount,
            'reference_number' => $payment->reference_number,
            'card_last_four' => $payment->card_last_four,
            'card_type' => $payment->card_type,
            'bank_name' => $payment->bank_name,
            'check_number' => $payment->check_number,
            'description' => "Payment for invoice {$payment->invoice->invoice_number}",
            'status' => $payment->status === 'completed' ? 'completed' : 'pending',
            'transaction_date' => $payment->payment_date,
            'processed_by' => $payment->processed_by,
            'approved_by' => $payment->approved_by,
            'transaction_details' => $payment->payment_details
        ]);
    }

    public static function createCashTransaction(
        Shift $shift,
        float $amount,
        string $type = 'payment',
        array $details = []
    ): self {
        return static::create(array_merge([
            'transaction_number' => static::generateTransactionNumber(),
            'shift_id' => $shift->id,
            'cash_register_id' => $shift->cash_register_id,
            'transaction_type' => $type,
            'payment_method' => 'cash',
            'amount' => $amount,
            'status' => 'completed',
            'transaction_date' => now(),
            'processed_by' => auth()->id()
        ], $details));
    }

    public static function getShiftSummary($shiftId): array
    {
        $transactions = static::where('shift_id', $shiftId)
                             ->where('status', 'completed')
                             ->get();

        $payments = $transactions->where('transaction_type', 'payment');
        $refunds = $transactions->where('transaction_type', 'refund');

        return [
            'total_transactions' => $transactions->count(),
            'total_payments' => $payments->count(),
            'total_refunds' => $refunds->count(),
            'total_revenue' => $payments->sum('amount'),
            'total_refunded' => $refunds->sum('amount'),
            'net_revenue' => $payments->sum('amount') - $refunds->sum('amount'),
            'cash_transactions' => $transactions->where('payment_method', 'cash')->count(),
            'card_transactions' => $transactions->where('payment_method', 'card')->count(),
            'cash_amount' => $transactions->where('payment_method', 'cash')->sum('net_amount'),
            'card_amount' => $transactions->where('payment_method', 'card')->sum('net_amount'),
            'average_transaction' => $payments->count() > 0 ? $payments->avg('amount') : 0,
            'largest_transaction' => $payments->max('amount') ?? 0,
            'smallest_transaction' => $payments->min('amount') ?? 0,
            'payment_methods' => $transactions->groupBy('payment_method')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('net_amount')
                ];
            }),
            'hourly_breakdown' => $transactions->groupBy(function($transaction) {
                return $transaction->transaction_date->format('H');
            })->map(function($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('net_amount')
                ];
            })
        ];
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->transaction_number) {
                $transaction->transaction_number = static::generateTransactionNumber();
            }
        });

        static::created(function ($transaction) {
            $transaction->addToAuditTrail('created', [
                'transaction_number' => $transaction->transaction_number,
                'shift_id' => $transaction->shift_id,
                'amount' => $transaction->amount,
                'type' => $transaction->transaction_type
            ]);
        });
    }
}