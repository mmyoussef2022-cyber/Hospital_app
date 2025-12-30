<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_number',
        'insurance_company_id',
        'insurance_policy_id',
        'patient_id',
        'invoice_id',
        'doctor_id',
        'service_date',
        'claim_date',
        'total_amount',
        'covered_amount',
        'deductible_amount',
        'co_payment_amount',
        'patient_responsibility',
        'approved_amount',
        'paid_amount',
        'status',
        'priority',
        'diagnosis_code',
        'diagnosis_description',
        'services_provided',
        'supporting_documents',
        'rejection_reason',
        'notes',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'paid_at',
        'reviewed_by',
        'approved_by'
    ];

    protected $casts = [
        'service_date' => 'date',
        'claim_date' => 'date',
        'total_amount' => 'decimal:2',
        'covered_amount' => 'decimal:2',
        'deductible_amount' => 'decimal:2',
        'co_payment_amount' => 'decimal:2',
        'patient_responsibility' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'services_provided' => 'array',
        'supporting_documents' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime'
    ];

    // Relationships
    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function insurancePolicy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted', 'under_review']);
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved', 'partially_approved']);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('service_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'draft' => 'مسودة',
            'submitted' => 'مقدم',
            'under_review' => 'قيد المراجعة',
            'approved' => 'موافق عليه',
            'partially_approved' => 'موافق عليه جزئياً',
            'rejected' => 'مرفوض',
            'paid' => 'مدفوع',
            'cancelled' => 'ملغي'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getPriorityDisplayAttribute(): string
    {
        $priorities = [
            'normal' => 'عادي',
            'urgent' => 'عاجل',
            'emergency' => 'طارئ'
        ];

        return $priorities[$this->priority] ?? $this->priority;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'under_review' => 'warning',
            'approved' => 'success',
            'partially_approved' => 'primary',
            'rejected' => 'danger',
            'paid' => 'success',
            'cancelled' => 'dark'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getPriorityColorAttribute(): string
    {
        $colors = [
            'normal' => 'secondary',
            'urgent' => 'warning',
            'emergency' => 'danger'
        ];

        return $colors[$this->priority] ?? 'secondary';
    }

    public function getCanBeEditedAttribute(): bool
    {
        return in_array($this->status, ['draft', 'submitted']);
    }

    public function getCanBeSubmittedAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanBeReviewedAttribute(): bool
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }

    public function getCanBeApprovedAttribute(): bool
    {
        return $this->status === 'under_review';
    }

    public function getCanBePaidAttribute(): bool
    {
        return in_array($this->status, ['approved', 'partially_approved']);
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return !in_array($this->status, ['paid', 'cancelled']);
    }

    public function getRemainingAmountAttribute(): float
    {
        if ($this->approved_amount) {
            return max(0, $this->approved_amount - $this->paid_amount);
        }
        
        return max(0, $this->covered_amount - $this->paid_amount);
    }

    public function getPaymentPercentageAttribute(): float
    {
        $totalAmount = $this->approved_amount ?: $this->covered_amount;
        
        if ($totalAmount <= 0) {
            return 0;
        }
        
        return ($this->paid_amount / $totalAmount) * 100;
    }

    // Business Logic Methods
    public function generateClaimNumber(): string
    {
        $prefix = 'CLM';
        $year = now()->year;
        $month = now()->format('m');
        
        // Get the last claim number for this month
        $lastClaim = static::where('claim_number', 'like', "{$prefix}-{$year}{$month}-%")
                          ->orderBy('claim_number', 'desc')
                          ->first();
        
        if ($lastClaim) {
            $lastNumber = (int) substr($lastClaim->claim_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $newNumber);
    }

    public function submit(): bool
    {
        if (!$this->can_be_submitted) {
            return false;
        }

        $this->status = 'submitted';
        $this->submitted_at = now();
        
        // Auto-generate claim number if not set
        if (!$this->claim_number) {
            $this->claim_number = $this->generateClaimNumber();
        }
        
        return $this->save();
    }

    public function startReview(User $reviewer): bool
    {
        if (!$this->can_be_reviewed) {
            return false;
        }

        $this->status = 'under_review';
        $this->reviewed_at = now();
        $this->reviewed_by = $reviewer->id;
        
        return $this->save();
    }

    public function approve(User $approver, float $approvedAmount = null, string $notes = null): bool
    {
        if (!$this->can_be_approved) {
            return false;
        }

        $this->approved_amount = $approvedAmount ?: $this->covered_amount;
        
        // Determine approval status
        if ($this->approved_amount >= $this->covered_amount) {
            $this->status = 'approved';
        } else {
            $this->status = 'partially_approved';
        }
        
        $this->approved_at = now();
        $this->approved_by = $approver->id;
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          "Approved on " . now()->format('Y-m-d') . ": {$notes}";
        }
        
        return $this->save();
    }

    public function reject(User $reviewer, string $reason): bool
    {
        if (!$this->can_be_reviewed) {
            return false;
        }

        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->reviewed_at = now();
        $this->reviewed_by = $reviewer->id;
        
        return $this->save();
    }

    public function recordPayment(float $amount, string $paymentReference = null): bool
    {
        if (!$this->can_be_paid) {
            return false;
        }

        $maxPayable = $this->approved_amount ?: $this->covered_amount;
        $newPaidAmount = min($this->paid_amount + $amount, $maxPayable);
        
        $this->paid_amount = $newPaidAmount;
        
        // Update status if fully paid
        if ($this->paid_amount >= $maxPayable) {
            $this->status = 'paid';
            $this->paid_at = now();
        }
        
        if ($paymentReference) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          "Payment recorded on " . now()->format('Y-m-d') . 
                          " - Amount: {$amount}, Reference: {$paymentReference}";
        }
        
        return $this->save();
    }

    public function cancel(string $reason = null): bool
    {
        if (!$this->can_be_cancelled) {
            return false;
        }

        $this->status = 'cancelled';
        
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          "Cancelled on " . now()->format('Y-m-d') . ": {$reason}";
        }
        
        return $this->save();
    }

    // Static Methods
    public static function createFromInvoice(Invoice $invoice, InsurancePolicy $policy): ?self
    {
        // Calculate coverage based on policy
        $coverage = $policy->calculateCoverage($invoice->total_amount);
        
        if ($coverage['covered_amount'] <= 0) {
            return null; // No coverage available
        }

        return static::create([
            'insurance_company_id' => $policy->insurance_company_id,
            'insurance_policy_id' => $policy->id,
            'patient_id' => $invoice->patient_id,
            'invoice_id' => $invoice->id,
            'doctor_id' => $invoice->doctor_id,
            'service_date' => $invoice->invoice_date,
            'claim_date' => now()->toDateString(),
            'total_amount' => $invoice->total_amount,
            'covered_amount' => $coverage['covered_amount'],
            'deductible_amount' => $coverage['deductible_applied'] ?? 0,
            'co_payment_amount' => $coverage['co_payment_amount'] ?? 0,
            'patient_responsibility' => $coverage['patient_responsibility'],
            'priority' => 'normal',
            'services_provided' => $invoice->items->map(function ($item) {
                return [
                    'service_type' => $item->itemable_type,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_amount' => $item->total_amount
                ];
            })->toArray()
        ]);
    }
}