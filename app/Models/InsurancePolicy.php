<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsurancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'insurance_company_id',
        'policy_number',
        'policy_name_ar',
        'policy_name_en',
        'policy_type',
        'coverage_percentage',
        'deductible_amount',
        'max_coverage_per_year',
        'max_coverage_per_visit',
        'covered_services',
        'excluded_services',
        'coverage_rules',
        'requires_pre_approval',
        'pre_approval_days',
        'co_payment_amount',
        'co_payment_percentage',
        'waiting_period_days',
        'effective_date',
        'expiry_date',
        'status',
        'terms_and_conditions',
        'notes'
    ];

    protected $casts = [
        'coverage_percentage' => 'decimal:2',
        'deductible_amount' => 'decimal:2',
        'max_coverage_per_year' => 'decimal:2',
        'max_coverage_per_visit' => 'decimal:2',
        'co_payment_amount' => 'decimal:2',
        'co_payment_percentage' => 'decimal:2',
        'covered_services' => 'array',
        'excluded_services' => 'array',
        'coverage_rules' => 'array',
        'requires_pre_approval' => 'boolean',
        'effective_date' => 'date',
        'expiry_date' => 'date'
    ];

    // Relationships
    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class);
    }

    public function patientInsurances(): HasMany
    {
        return $this->hasMany(PatientInsurance::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('effective_date', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>', now());
                    });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('policy_type', $type);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now())
                    ->where('status', 'active');
    }

    // Accessors
    public function getPolicyNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->policy_name_ar : $this->policy_name_en;
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'active' => 'نشط',
            'suspended' => 'معلق',
            'expired' => 'منتهي الصلاحية',
            'cancelled' => 'ملغي'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getPolicyTypeDisplayAttribute(): string
    {
        $types = [
            'individual' => 'فردي',
            'family' => 'عائلي',
            'group' => 'جماعي',
            'corporate' => 'شركات'
        ];

        return $types[$this->policy_type] ?? $this->policy_type;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && 
               $this->effective_date <= now() &&
               (!$this->expiry_date || $this->expiry_date > now());
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date <= now();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date && 
               $this->expiry_date->diffInDays(now()) <= 30 &&
               $this->expiry_date->isFuture();
    }

    // Business Logic Methods
    public function calculateCoverage(float $amount, string $serviceType = null, array $options = []): array
    {
        // Check if policy is active
        if (!$this->is_active) {
            return [
                'covered_amount' => 0,
                'patient_responsibility' => $amount,
                'coverage_percentage' => 0,
                'reason' => 'Policy is not active'
            ];
        }

        // Check if service is covered
        if ($serviceType && !$this->isServiceCovered($serviceType)) {
            return [
                'covered_amount' => 0,
                'patient_responsibility' => $amount,
                'coverage_percentage' => 0,
                'reason' => 'Service not covered by this policy'
            ];
        }

        // Check waiting period
        if ($this->waiting_period_days > 0 && isset($options['coverage_start_date'])) {
            $coverageStart = \Carbon\Carbon::parse($options['coverage_start_date']);
            if ($coverageStart->addDays($this->waiting_period_days)->isFuture()) {
                return [
                    'covered_amount' => 0,
                    'patient_responsibility' => $amount,
                    'coverage_percentage' => 0,
                    'reason' => 'Still in waiting period'
                ];
            }
        }

        // Apply deductible
        $amountAfterDeductible = max(0, $amount - $this->deductible_amount);
        
        // Calculate coverage
        $coveragePercentage = $this->coverage_percentage;
        $coveredAmount = ($amountAfterDeductible * $coveragePercentage) / 100;
        
        // Apply co-payment
        if ($this->co_payment_amount > 0) {
            $coveredAmount = max(0, $coveredAmount - $this->co_payment_amount);
        }
        
        if ($this->co_payment_percentage > 0) {
            $coPayment = ($amount * $this->co_payment_percentage) / 100;
            $coveredAmount = max(0, $coveredAmount - $coPayment);
        }
        
        // Apply maximum coverage per visit
        if ($this->max_coverage_per_visit && $coveredAmount > $this->max_coverage_per_visit) {
            $coveredAmount = $this->max_coverage_per_visit;
        }
        
        // Check annual limit (if provided)
        if (isset($options['annual_limit_used']) && $this->max_coverage_per_year) {
            $remainingAnnualLimit = $this->max_coverage_per_year - $options['annual_limit_used'];
            if ($coveredAmount > $remainingAnnualLimit) {
                $coveredAmount = max(0, $remainingAnnualLimit);
            }
        }
        
        $patientResponsibility = $amount - $coveredAmount;
        
        return [
            'covered_amount' => round($coveredAmount, 2),
            'patient_responsibility' => round($patientResponsibility, 2),
            'coverage_percentage' => $coveragePercentage,
            'deductible_applied' => $this->deductible_amount,
            'co_payment_amount' => $this->co_payment_amount,
            'co_payment_percentage' => $this->co_payment_percentage,
            'max_coverage_applied' => $this->max_coverage_per_visit && $coveredAmount >= $this->max_coverage_per_visit,
            'requires_pre_approval' => $this->requires_pre_approval
        ];
    }

    public function isServiceCovered(string $serviceType): bool
    {
        // If excluded services list exists and service is in it, not covered
        if ($this->excluded_services && in_array($serviceType, $this->excluded_services)) {
            return false;
        }
        
        // If covered services list exists, service must be in it
        if ($this->covered_services && !empty($this->covered_services)) {
            return in_array($serviceType, $this->covered_services);
        }
        
        // If no specific lists, assume covered (unless excluded)
        return true;
    }

    public function requiresPreApproval(string $serviceType = null, float $amount = null): bool
    {
        if (!$this->requires_pre_approval) {
            return false;
        }

        // Check coverage rules for specific requirements
        if ($this->coverage_rules) {
            foreach ($this->coverage_rules as $rule) {
                if (isset($rule['service_type']) && $rule['service_type'] === $serviceType) {
                    return $rule['requires_pre_approval'] ?? $this->requires_pre_approval;
                }
                
                if (isset($rule['min_amount']) && $amount && $amount >= $rule['min_amount']) {
                    return $rule['requires_pre_approval'] ?? $this->requires_pre_approval;
                }
            }
        }

        return $this->requires_pre_approval;
    }

    // Status Management
    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    public function suspend(string $reason = null): void
    {
        $this->status = 'suspended';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          "Suspended on " . now()->format('Y-m-d') . ": {$reason}";
        }
        $this->save();
    }

    public function cancel(string $reason = null): void
    {
        $this->status = 'cancelled';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          "Cancelled on " . now()->format('Y-m-d') . ": {$reason}";
        }
        $this->save();
    }

    public function expire(): void
    {
        $this->status = 'expired';
        $this->save();
    }

    // Statistics
    public function getTotalClaimsAmount(): float
    {
        return $this->claims()->sum('total_amount');
    }

    public function getApprovedClaimsAmount(): float
    {
        return $this->claims()->whereIn('status', ['approved', 'paid'])->sum('approved_amount');
    }

    public function getPaidClaimsAmount(): float
    {
        return $this->claims()->where('status', 'paid')->sum('paid_amount');
    }

    public function getClaimsCount(): int
    {
        return $this->claims()->count();
    }

    public function getActivePatientCount(): int
    {
        return $this->patientInsurances()->where('status', 'active')->count();
    }
}