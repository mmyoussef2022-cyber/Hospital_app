<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'commercial_registration',
        'tax_number',
        'phone',
        'email',
        'website',
        'address_ar',
        'address_en',
        'default_coverage_percentage',
        'max_coverage_amount',
        'deductible_amount',
        'covered_services',
        'excluded_services',
        'payment_terms_days',
        'discount_percentage',
        'payment_method',
        'bank_name',
        'bank_account_number',
        'iban',
        'swift_code',
        'contract_start_date',
        'contract_end_date',
        'contract_status',
        'notes',
        'settings',
        'is_active'
    ];

    protected $casts = [
        'default_coverage_percentage' => 'decimal:2',
        'max_coverage_amount' => 'decimal:2',
        'deductible_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'covered_services' => 'array',
        'excluded_services' => 'array',
        'settings' => 'array',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function policies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class);
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
        return $query->where('is_active', true)
                    ->where('contract_status', 'active');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('contract_end_date', '<=', now()->addDays($days))
                    ->where('contract_status', 'active');
    }

    // Accessors
    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getAddressAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->address_ar : $this->address_en;
    }

    public function getContractStatusDisplayAttribute(): string
    {
        $statuses = [
            'active' => 'نشط',
            'suspended' => 'معلق',
            'terminated' => 'منتهي'
        ];

        return $statuses[$this->contract_status] ?? $this->contract_status;
    }

    public function getPaymentMethodDisplayAttribute(): string
    {
        $methods = [
            'bank_transfer' => 'تحويل بنكي',
            'check' => 'شيك',
            'online' => 'دفع إلكتروني'
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    public function getIsContractActiveAttribute(): bool
    {
        return $this->contract_status === 'active' && 
               $this->is_active && 
               (!$this->contract_end_date || $this->contract_end_date->isFuture());
    }

    public function getIsContractExpiringSoonAttribute(): bool
    {
        return $this->contract_end_date && 
               $this->contract_end_date->diffInDays(now()) <= 30 &&
               $this->contract_end_date->isFuture();
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        return $this->contract_end_date ? 
               $this->contract_end_date->diffInDays(now()) : null;
    }

    // Business Logic Methods
    public function calculateCoverage(float $amount, string $serviceType = null): array
    {
        // Check if service is covered
        if ($serviceType && $this->excluded_services && in_array($serviceType, $this->excluded_services)) {
            return [
                'covered_amount' => 0,
                'patient_responsibility' => $amount,
                'coverage_percentage' => 0,
                'reason' => 'Service not covered by insurance'
            ];
        }

        // Apply deductible
        $amountAfterDeductible = max(0, $amount - $this->deductible_amount);
        
        // Calculate coverage
        $coveragePercentage = $this->default_coverage_percentage;
        $coveredAmount = ($amountAfterDeductible * $coveragePercentage) / 100;
        
        // Apply maximum coverage limit
        if ($this->max_coverage_amount && $coveredAmount > $this->max_coverage_amount) {
            $coveredAmount = $this->max_coverage_amount;
        }
        
        $patientResponsibility = $amount - $coveredAmount;
        
        return [
            'covered_amount' => $coveredAmount,
            'patient_responsibility' => $patientResponsibility,
            'coverage_percentage' => $coveragePercentage,
            'deductible_applied' => $this->deductible_amount,
            'max_coverage_applied' => $this->max_coverage_amount && $coveredAmount >= $this->max_coverage_amount
        ];
    }

    public function isServiceCovered(string $serviceType): bool
    {
        // If no covered services specified, assume all are covered
        if (!$this->covered_services) {
            return !$this->excluded_services || !in_array($serviceType, $this->excluded_services);
        }
        
        // Check if service is in covered list and not in excluded list
        return in_array($serviceType, $this->covered_services) && 
               (!$this->excluded_services || !in_array($serviceType, $this->excluded_services));
    }

    public function suspend(string $reason = null): void
    {
        $this->contract_status = 'suspended';
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                      "Suspended on " . now()->format('Y-m-d') . 
                      ($reason ? ": {$reason}" : '');
        $this->save();
    }

    public function activate(): void
    {
        $this->contract_status = 'active';
        $this->is_active = true;
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                      "Activated on " . now()->format('Y-m-d');
        $this->save();
    }

    public function terminate(string $reason = null): void
    {
        $this->contract_status = 'terminated';
        $this->is_active = false;
        $this->contract_end_date = now()->toDateString();
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                      "Terminated on " . now()->format('Y-m-d') . 
                      ($reason ? ": {$reason}" : '');
        $this->save();
    }

    public function renewContract(string $endDate, array $terms = []): void
    {
        $this->contract_end_date = $endDate;
        $this->contract_status = 'active';
        $this->is_active = true;
        
        // Update terms if provided
        if (!empty($terms)) {
            foreach ($terms as $key => $value) {
                if (in_array($key, $this->fillable)) {
                    $this->$key = $value;
                }
            }
        }
        
        $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                      "Contract renewed on " . now()->format('Y-m-d') . 
                      " until {$endDate}";
        $this->save();
    }

    // Statistics Methods
    public function getTotalInvoicesAmount(): float
    {
        return $this->invoices()->sum('total_amount');
    }

    public function getTotalPaidAmount(): float
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getPendingAmount(): float
    {
        return $this->invoices()->where('type', 'insurance')
                                ->where('status', '!=', 'paid')
                                ->sum('remaining_amount');
    }

    public function getMonthlyStatistics(int $year = null, int $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        
        $invoices = $this->invoices()
                        ->whereYear('invoice_date', $year)
                        ->whereMonth('invoice_date', $month);
        
        $payments = $this->payments()
                        ->whereYear('payment_date', $year)
                        ->whereMonth('payment_date', $month)
                        ->where('status', 'completed');
        
        return [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'total_payments' => $payments->count(),
            'total_paid' => $payments->sum('amount'),
            'pending_amount' => $invoices->where('status', '!=', 'paid')->sum('remaining_amount')
        ];
    }

    // Static Methods
    public static function getActiveCompanies()
    {
        return static::active()->orderBy('name_ar')->get();
    }

    public static function getExpiringSoon(int $days = 30)
    {
        return static::expiringSoon($days)->get();
    }
}