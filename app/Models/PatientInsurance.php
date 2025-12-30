<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientInsurance extends Model
{
    use HasFactory;

    protected $table = 'patient_insurance';

    protected $fillable = [
        'patient_id',
        'insurance_company_id',
        'insurance_policy_id',
        'member_id',
        'policy_holder_name',
        'policy_holder_relation',
        'card_number',
        'coverage_start_date',
        'coverage_end_date',
        'status',
        'annual_limit_used',
        'annual_limit_remaining',
        'family_members',
        'is_primary',
        'priority_order',
        'notes'
    ];

    protected $casts = [
        'coverage_start_date' => 'date',
        'coverage_end_date' => 'date',
        'annual_limit_used' => 'decimal:2',
        'annual_limit_remaining' => 'decimal:2',
        'family_members' => 'array',
        'is_primary' => 'boolean'
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    public function insurancePolicy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('coverage_start_date', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('coverage_end_date')
                          ->orWhere('coverage_end_date', '>', now());
                    });
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeSecondary($query)
    {
        return $query->where('is_primary', false);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('coverage_end_date', '<=', now()->addDays($days))
                    ->where('coverage_end_date', '>', now())
                    ->where('status', 'active');
    }

    // Accessors
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

    public function getRelationDisplayAttribute(): string
    {
        $relations = [
            'self' => 'المؤمن عليه',
            'spouse' => 'الزوج/الزوجة',
            'child' => 'الطفل',
            'parent' => 'الوالد/الوالدة',
            'sibling' => 'الأخ/الأخت',
            'other' => 'أخرى'
        ];

        return $relations[$this->policy_holder_relation] ?? $this->policy_holder_relation;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && 
               $this->coverage_start_date <= now() &&
               (!$this->coverage_end_date || $this->coverage_end_date > now());
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->coverage_end_date && $this->coverage_end_date <= now();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->coverage_end_date && 
               $this->coverage_end_date->diffInDays(now()) <= 30 &&
               $this->coverage_end_date->isFuture();
    }

    public function getCoverageRemainingDaysAttribute(): ?int
    {
        return $this->coverage_end_date ? 
               $this->coverage_end_date->diffInDays(now()) : null;
    }

    public function getAnnualLimitUsagePercentageAttribute(): float
    {
        if (!$this->insurancePolicy->max_coverage_per_year) {
            return 0;
        }
        
        return ($this->annual_limit_used / $this->insurancePolicy->max_coverage_per_year) * 100;
    }

    // Business Logic Methods
    public function calculateCoverage(float $amount, string $serviceType = null): array
    {
        if (!$this->is_active) {
            return [
                'covered_amount' => 0,
                'patient_responsibility' => $amount,
                'coverage_percentage' => 0,
                'reason' => 'Insurance coverage is not active'
            ];
        }

        // Use the policy's coverage calculation with annual limit context
        $options = [
            'coverage_start_date' => $this->coverage_start_date,
            'annual_limit_used' => $this->annual_limit_used
        ];

        return $this->insurancePolicy->calculateCoverage($amount, $serviceType, $options);
    }

    public function updateAnnualLimitUsage(float $amount): void
    {
        $this->annual_limit_used += $amount;
        
        // Update remaining limit if policy has annual limit
        if ($this->insurancePolicy->max_coverage_per_year) {
            $this->annual_limit_remaining = max(0, 
                $this->insurancePolicy->max_coverage_per_year - $this->annual_limit_used
            );
        }
        
        $this->save();
    }

    public function resetAnnualLimit(): void
    {
        $this->annual_limit_used = 0;
        
        if ($this->insurancePolicy->max_coverage_per_year) {
            $this->annual_limit_remaining = $this->insurancePolicy->max_coverage_per_year;
        }
        
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

    public function activate(): void
    {
        $this->status = 'active';
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

    public function renewCoverage(string $endDate, array $options = []): void
    {
        $this->coverage_end_date = $endDate;
        $this->status = 'active';
        
        // Reset annual limit for new coverage period
        $this->resetAnnualLimit();
        
        // Update other options if provided
        foreach ($options as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->$key = $value;
            }
        }
        
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                      "Coverage renewed on " . now()->format('Y-m-d') . 
                      " until {$endDate}";
        
        $this->save();
    }

    // Family Insurance Methods
    public function addFamilyMember(array $memberData): void
    {
        $familyMembers = $this->family_members ?: [];
        $familyMembers[] = array_merge($memberData, [
            'added_date' => now()->toDateString()
        ]);
        
        $this->family_members = $familyMembers;
        $this->save();
    }

    public function removeFamilyMember(string $memberId): void
    {
        $familyMembers = $this->family_members ?: [];
        
        $this->family_members = array_filter($familyMembers, function ($member) use ($memberId) {
            return $member['id'] !== $memberId;
        });
        
        $this->save();
    }

    public function getFamilyMemberById(string $memberId): ?array
    {
        $familyMembers = $this->family_members ?: [];
        
        foreach ($familyMembers as $member) {
            if ($member['id'] === $memberId) {
                return $member;
            }
        }
        
        return null;
    }

    public function isFamilyPolicy(): bool
    {
        return $this->insurancePolicy->policy_type === 'family';
    }

    public function isPolicyHolder(): bool
    {
        return $this->policy_holder_relation === 'self' || !$this->policy_holder_relation;
    }

    // Statistics
    public function getTotalClaimsAmount(): float
    {
        return $this->patient->insuranceClaims()
                           ->where('insurance_policy_id', $this->insurance_policy_id)
                           ->sum('total_amount');
    }

    public function getApprovedClaimsAmount(): float
    {
        return $this->patient->insuranceClaims()
                           ->where('insurance_policy_id', $this->insurance_policy_id)
                           ->whereIn('status', ['approved', 'paid'])
                           ->sum('approved_amount');
    }

    public function getPaidClaimsAmount(): float
    {
        return $this->patient->insuranceClaims()
                           ->where('insurance_policy_id', $this->insurance_policy_id)
                           ->where('status', 'paid')
                           ->sum('paid_amount');
    }
}