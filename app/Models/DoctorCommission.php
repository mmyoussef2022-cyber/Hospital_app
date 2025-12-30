<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'name',
        'description',
        'type',
        'calculation_method',
        'rate',
        'minimum_amount',
        'maximum_amount',
        'tier_structure',
        'conditions',
        'minimum_service_amount',
        'minimum_appointments',
        'period_type',
        'effective_from',
        'effective_until',
        'status',
        'priority',
        'auto_calculate',
        'auto_pay',
        'payment_trigger',
        'applicable_services',
        'applicable_departments',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'minimum_service_amount' => 'decimal:2',
        'tier_structure' => 'array',
        'conditions' => 'array',
        'applicable_services' => 'array',
        'applicable_departments' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'auto_calculate' => 'boolean',
        'auto_pay' => 'boolean',
    ];

    // Relationships
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('effective_from', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('effective_until')
                          ->orWhere('effective_until', '>=', now());
                    });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function scopeAutoCalculate($query)
    {
        return $query->where('auto_calculate', true);
    }

    public function scopeForService($query, $serviceId)
    {
        return $query->where(function ($q) use ($serviceId) {
            $q->whereNull('applicable_services')
              ->orWhereJsonContains('applicable_services', $serviceId);
        });
    }

    public function scopeForDepartment($query, $department)
    {
        return $query->where(function ($q) use ($department) {
            $q->whereNull('applicable_departments')
              ->orWhereJsonContains('applicable_departments', $department);
        });
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => 'success',
            'inactive' => 'warning',
            'expired' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getTypeNameAttribute()
    {
        $types = [
            'service' => __('app.service_commission'),
            'appointment' => __('app.appointment_commission'),
            'revenue' => __('app.revenue_commission'),
            'performance' => __('app.performance_commission'),
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function getCalculationMethodNameAttribute()
    {
        $methods = [
            'percentage' => __('app.percentage'),
            'fixed' => __('app.fixed_amount'),
            'tiered' => __('app.tiered'),
            'hybrid' => __('app.hybrid'),
        ];

        return $methods[$this->calculation_method] ?? $this->calculation_method;
    }

    public function getFormattedRateAttribute()
    {
        if ($this->calculation_method === 'percentage') {
            return $this->rate . '%';
        }
        return number_format($this->rate, 2) . ' ' . __('app.currency_sar');
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active' &&
               $this->effective_from <= now() &&
               ($this->effective_until === null || $this->effective_until >= now());
    }

    // Methods
    public function calculateCommission($amount, $context = [])
    {
        if (!$this->is_active) {
            return 0;
        }

        // Check minimum service amount
        if ($amount < $this->minimum_service_amount) {
            return 0;
        }

        // Check conditions
        if (!$this->meetsConditions($context)) {
            return 0;
        }

        $commission = 0;

        switch ($this->calculation_method) {
            case 'percentage':
                $commission = ($amount * $this->rate) / 100;
                break;

            case 'fixed':
                $commission = $this->rate;
                break;

            case 'tiered':
                $commission = $this->calculateTieredCommission($amount);
                break;

            case 'hybrid':
                $percentageCommission = ($amount * $this->rate) / 100;
                $commission = $percentageCommission + ($this->minimum_amount ?? 0);
                break;
        }

        // Apply minimum and maximum limits
        if ($this->minimum_amount && $commission < $this->minimum_amount) {
            $commission = $this->minimum_amount;
        }

        if ($this->maximum_amount && $commission > $this->maximum_amount) {
            $commission = $this->maximum_amount;
        }

        return round($commission, 2);
    }

    protected function calculateTieredCommission($amount)
    {
        if (!$this->tier_structure) {
            return 0;
        }

        $commission = 0;
        $remainingAmount = $amount;

        foreach ($this->tier_structure as $tier) {
            $tierMin = $tier['min'] ?? 0;
            $tierMax = $tier['max'] ?? PHP_INT_MAX;
            $tierRate = $tier['rate'] ?? 0;

            if ($remainingAmount <= 0) {
                break;
            }

            $tierAmount = min($remainingAmount, $tierMax - $tierMin);
            
            if ($tierAmount > 0) {
                if (isset($tier['type']) && $tier['type'] === 'fixed') {
                    $commission += $tierRate;
                } else {
                    $commission += ($tierAmount * $tierRate) / 100;
                }
                
                $remainingAmount -= $tierAmount;
            }
        }

        return $commission;
    }

    protected function meetsConditions($context)
    {
        if (!$this->conditions) {
            return true;
        }

        // Check service-specific conditions
        if (isset($this->conditions['services']) && isset($context['service_id'])) {
            if (!in_array($context['service_id'], $this->conditions['services'])) {
                return false;
            }
        }

        // Check department-specific conditions
        if (isset($this->conditions['departments']) && isset($context['department'])) {
            if (!in_array($context['department'], $this->conditions['departments'])) {
                return false;
            }
        }

        // Check time-based conditions
        if (isset($this->conditions['time_restrictions'])) {
            $timeRestrictions = $this->conditions['time_restrictions'];
            $now = now();

            if (isset($timeRestrictions['days_of_week'])) {
                if (!in_array($now->dayOfWeek, $timeRestrictions['days_of_week'])) {
                    return false;
                }
            }

            if (isset($timeRestrictions['hours'])) {
                $currentHour = $now->hour;
                if ($currentHour < $timeRestrictions['hours']['start'] || 
                    $currentHour > $timeRestrictions['hours']['end']) {
                    return false;
                }
            }
        }

        // Check minimum appointments condition
        if ($this->minimum_appointments > 0 && isset($context['appointment_count'])) {
            if ($context['appointment_count'] < $this->minimum_appointments) {
                return false;
            }
        }

        return true;
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
        return $this;
    }

    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
        return $this;
    }

    public function expire()
    {
        $this->update([
            'status' => 'expired',
            'effective_until' => now(),
        ]);
        return $this;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($commission) {
            if ($commission->priority === null) {
                $maxPriority = static::where('doctor_id', $commission->doctor_id)->max('priority') ?? 0;
                $commission->priority = $maxPriority + 1;
            }
        });
    }
}