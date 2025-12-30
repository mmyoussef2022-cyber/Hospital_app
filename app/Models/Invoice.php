<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'type',
        'status',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'insurance_company_id',
        'insurance_policy_number',
        'insurance_coverage_percentage',
        'insurance_approved_amount',
        'payment_term_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'late_fee_amount',
        'discount_amount_applied',
        'invoice_date',
        'due_date',
        'paid_at',
        'last_reminder_sent',
        'reminder_count',
        'collection_status',
        'escalated_at',
        'notes',
        'payment_terms',
        'collection_notes',
        'audit_trail',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'last_reminder_sent' => 'datetime',
        'escalated_at' => 'datetime',
        'audit_trail' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'discount_amount_applied' => 'decimal:2',
        'insurance_coverage_percentage' => 'decimal:2',
        'insurance_approved_amount' => 'decimal:2',
        'reminder_count' => 'integer'
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function paymentReminders(): HasMany
    {
        return $this->hasMany(PaymentReminder::class);
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
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
        return $query->where('status', 'overdue')
                    ->orWhere(function($q) {
                        $q->where('status', 'pending')
                          ->where('due_date', '<', now());
                    });
    }

    public function scopeCash($query)
    {
        return $query->where('type', 'cash');
    }

    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeInsurance($query)
    {
        return $query->where('type', 'insurance');
    }

    // Accessors & Mutators
    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'draft' => 'مسودة',
            'pending' => 'معلقة',
            'paid' => 'مدفوعة',
            'partially_paid' => 'مدفوعة جزئياً',
            'overdue' => 'متأخرة',
            'cancelled' => 'ملغية'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getTypeDisplayAttribute(): string
    {
        $types = [
            'cash' => 'نقدي',
            'credit' => 'آجل',
            'insurance' => 'تأمين'
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function getCollectionStatusDisplayAttribute(): string
    {
        $statuses = [
            'none' => 'لا يوجد',
            'gentle' => 'تذكير لطيف',
            'standard' => 'تذكير عادي',
            'urgent' => 'تذكير عاجل',
            'final_notice' => 'إشعار نهائي',
            'collection' => 'تحصيل'
        ];

        return $statuses[$this->collection_status] ?? $this->collection_status;
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->due_date || !$this->is_overdue) {
            return 0;
        }

        return $this->due_date->diffInDays(now());
    }

    public function getCanSendReminderAttribute(): bool
    {
        return $this->type === 'credit' && 
               in_array($this->status, ['pending', 'partially_paid']) &&
               $this->remaining_amount > 0;
    }

    public function getNextReminderDateAttribute(): ?Carbon
    {
        if (!$this->can_send_reminder) {
            return null;
        }

        $lastReminder = $this->paymentReminders()->latest('sent_date')->first();
        
        if (!$lastReminder) {
            return $this->due_date->addDays(3); // First reminder 3 days after due
        }

        $daysSinceLastReminder = $lastReminder->sent_date->diffInDays(now());
        
        if ($daysSinceLastReminder >= 7) { // Weekly reminders
            return now();
        }

        return $lastReminder->sent_date->addDays(7);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'overdue' || 
               ($this->status === 'pending' && $this->due_date && $this->due_date->isPast());
    }

    public function getIsPartiallyPaidAttribute(): bool
    {
        return $this->paid_amount > 0 && $this->paid_amount < $this->total_amount;
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    public function getPaymentProgressAttribute(): float
    {
        if ($this->total_amount == 0) return 0;
        return ($this->paid_amount / $this->total_amount) * 100;
    }

    // Business Logic Methods
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        $lastInvoice = static::whereYear('created_at', $year)
                           ->whereMonth('created_at', $month)
                           ->orderBy('id', 'desc')
                           ->first();
        
        $sequence = $lastInvoice ? (int)substr($lastInvoice->invoice_number, -4) + 1 : 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total_amount');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->discount_amount = $this->items->sum('discount_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;
    }

    public function addPayment(float $amount, string $method = 'cash', array $details = []): Payment
    {
        $payment = $this->payments()->create([
            'payment_number' => $this->generatePaymentNumber(),
            'patient_id' => $this->patient_id,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'status' => 'completed',
            'processed_by' => auth()->id(),
            'payment_details' => $details
        ]);

        $this->updatePaymentStatus();
        
        return $payment;
    }

    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->payments()->where('status', 'completed')->sum('amount');
        $this->paid_amount = $totalPaid;
        $this->remaining_amount = $this->total_amount - $totalPaid;

        if ($totalPaid >= $this->total_amount) {
            $this->status = 'paid';
            $this->paid_at = now();
        } elseif ($totalPaid > 0) {
            $this->status = 'partially_paid';
        } else {
            $this->status = 'pending';
            $this->paid_at = null;
        }

        $this->save();
    }

    public function markAsOverdue(): void
    {
        if ($this->status === 'pending' && $this->due_date && $this->due_date->isPast()) {
            $this->status = 'overdue';
            $this->save();
        }
    }

    public function calculateLateFees(): float
    {
        if (!$this->paymentTerm || !$this->is_overdue) {
            return 0;
        }

        $lateFee = $this->paymentTerm->calculateLateFee(
            $this->remaining_amount,
            $this->due_date,
            now()
        );

        $this->update(['late_fee_amount' => $lateFee]);

        return $lateFee;
    }

    public function applyEarlyPaymentDiscount(float $paymentAmount): float
    {
        if (!$this->paymentTerm) {
            return 0;
        }

        $discount = $this->paymentTerm->calculateDiscountAmount(
            $paymentAmount,
            now(),
            $this->invoice_date
        );

        if ($discount > 0) {
            $this->increment('discount_amount_applied', $discount);
        }

        return $discount;
    }

    public function sendPaymentReminder(string $type = 'standard', string $method = 'sms'): PaymentReminder
    {
        $reminder = $this->paymentReminders()->create([
            'patient_id' => $this->patient_id,
            'reminder_type' => $type,
            'reminder_method' => $method,
            'scheduled_date' => now(),
            'status' => 'pending',
            'message' => $this->generateReminderMessage($type),
            'escalation_level' => $this->reminder_count + 1,
            'created_by' => auth()->id()
        ]);

        $this->update([
            'last_reminder_sent' => now(),
            'reminder_count' => $this->reminder_count + 1,
            'collection_status' => $type
        ]);

        return $reminder;
    }

    public function escalateCollection(): void
    {
        $escalationLevels = ['none', 'gentle', 'standard', 'urgent', 'final_notice', 'collection'];
        $currentIndex = array_search($this->collection_status, $escalationLevels);
        
        if ($currentIndex !== false && $currentIndex < count($escalationLevels) - 1) {
            $nextLevel = $escalationLevels[$currentIndex + 1];
            
            $this->update([
                'collection_status' => $nextLevel,
                'escalated_at' => now()
            ]);

            // Create automatic reminder for next level
            $this->sendPaymentReminder($nextLevel);
        }
    }

    public function processAdvancedPayment(float $amount, string $method = 'cash', array $details = []): Payment
    {
        // Apply early payment discount if eligible
        $discount = $this->applyEarlyPaymentDiscount($amount);
        $effectiveAmount = $amount + $discount;

        // Create payment record
        $payment = $this->addPayment($effectiveAmount, $method, array_merge($details, [
            'early_payment_discount' => $discount,
            'original_amount' => $amount
        ]));

        // Reset collection status if fully paid
        if ($this->status === 'paid') {
            $this->update([
                'collection_status' => 'none',
                'escalated_at' => null
            ]);
        }

        return $payment;
    }

    private function generateReminderMessage(string $type): string
    {
        $patient = $this->patient;
        
        $messages = [
            'gentle' => "عزيزي/عزيزتي {$patient->name}، نذكركم بأن الفاتورة رقم {$this->invoice_number} بمبلغ {$this->remaining_amount} ريال مستحقة الدفع.",
            
            'standard' => "السيد/السيدة {$patient->name}، الفاتورة رقم {$this->invoice_number} بمبلغ {$this->remaining_amount} ريال متأخرة عن موعد الاستحقاق.",
            
            'urgent' => "تنبيه عاجل: الفاتورة رقم {$this->invoice_number} بمبلغ {$this->remaining_amount} ريال متأخرة بشكل كبير.",
            
            'final_notice' => "إشعار نهائي: الفاتورة رقم {$this->invoice_number} بمبلغ {$this->remaining_amount} ريال. آخر فرصة للسداد قبل التحصيل القانوني.",
            
            'collection' => "تم تحويل الفاتورة رقم {$this->invoice_number} بمبلغ {$this->remaining_amount} ريال لشركة التحصيل."
        ];

        return $messages[$type] ?? $messages['standard'];
    }

    // Static Methods for Advanced Billing
    public static function processOverdueInvoices(): array
    {
        $results = [
            'marked_overdue' => 0,
            'escalated' => 0,
            'reminders_sent' => 0
        ];

        // Mark pending invoices as overdue
        $overdueInvoices = static::where('status', 'pending')
                                ->where('due_date', '<', now())
                                ->get();

        foreach ($overdueInvoices as $invoice) {
            $invoice->markAsOverdue();
            $results['marked_overdue']++;
        }

        // Process automatic escalations
        $escalationCandidates = static::where('status', 'overdue')
                                    ->where('collection_status', '!=', 'collection')
                                    ->where(function($q) {
                                        $q->whereNull('last_reminder_sent')
                                          ->orWhere('last_reminder_sent', '<=', now()->subDays(7));
                                    })
                                    ->get();

        foreach ($escalationCandidates as $invoice) {
            $invoice->escalateCollection();
            $results['escalated']++;
        }

        // Process pending reminders
        $results['reminders_sent'] = PaymentReminder::processPendingReminders();

        return $results;
    }

    public static function generateCashFlowForecast(int $days = 30): array
    {
        $forecast = [];
        $startDate = now();

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            $expectedPayments = static::where('status', 'pending')
                                    ->where('due_date', $date->toDateString())
                                    ->sum('remaining_amount');

            $forecast[] = [
                'date' => $date->format('Y-m-d'),
                'expected_amount' => $expectedPayments,
                'probability' => static::calculateCollectionProbability($date)
            ];
        }

        return $forecast;
    }

    private static function calculateCollectionProbability(Carbon $date): float
    {
        // Simple probability model based on days overdue
        $daysFromNow = now()->diffInDays($date, false);
        
        if ($daysFromNow <= 0) {
            return 0.95; // 95% for current/future due dates
        } elseif ($daysFromNow <= 30) {
            return 0.80; // 80% for 1-30 days overdue
        } elseif ($daysFromNow <= 60) {
            return 0.60; // 60% for 31-60 days overdue
        } else {
            return 0.30; // 30% for over 60 days overdue
        }
    }

    public function cancel(string $reason = null): void
    {
        $this->status = 'cancelled';
        $this->addToAuditTrail('cancelled', $reason);
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

    private function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $year = date('Y');
        $month = date('m');
        
        $lastPayment = Payment::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->orderBy('id', 'desc')
                            ->first();
        
        $sequence = $lastPayment ? (int)substr($lastPayment->payment_number, -4) + 1 : 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    // Static Methods
    public static function createFromAppointment(Appointment $appointment, array $services = []): self
    {
        $invoice = new static([
            'invoice_number' => (new static)->generateInvoiceNumber(),
            'type' => 'cash',
            'status' => 'draft',
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'invoice_date' => now()->toDateString(),
            'created_by' => auth()->id()
        ]);

        $invoice->save();

        // Add services as invoice items
        foreach ($services as $service) {
            $invoice->items()->create([
                'item_type' => 'service',
                'item_name' => $service['name'],
                'item_description' => $service['description'] ?? null,
                'unit_price' => $service['price'],
                'quantity' => $service['quantity'] ?? 1,
                'total_amount' => $service['price'] * ($service['quantity'] ?? 1)
            ]);
        }

        $invoice->calculateTotals();
        $invoice->save();

        return $invoice;
    }
}