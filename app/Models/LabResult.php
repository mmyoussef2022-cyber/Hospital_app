<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_id',
        'parameter_name',
        'value',
        'unit',
        'reference_range',
        'flag',
        'notes',
        'verified_by',
        'verified_at',
        'is_critical',
        'critical_notified_at'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'critical_notified_at' => 'datetime',
        'is_critical' => 'boolean'
    ];

    // Relationships
    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }

    public function scopeByFlag($query, $flag)
    {
        return $query->where('flag', $flag);
    }

    // Accessors
    public function getFlagDisplayAttribute(): string
    {
        return match($this->flag) {
            'normal' => 'طبيعي',
            'high' => 'مرتفع',
            'low' => 'منخفض',
            'critical_high' => 'مرتفع جداً',
            'critical_low' => 'منخفض جداً',
            'abnormal' => 'غير طبيعي',
            default => $this->flag
        };
    }

    public function getFlagColorAttribute(): string
    {
        return match($this->flag) {
            'normal' => 'success',
            'high' => 'warning',
            'low' => 'warning',
            'critical_high' => 'danger',
            'critical_low' => 'danger',
            'abnormal' => 'info',
            default => 'secondary'
        };
    }

    public function getFormattedValueAttribute(): string
    {
        $formatted = $this->value;
        
        if ($this->unit) {
            $formatted .= ' ' . $this->unit;
        }
        
        return $formatted;
    }

    // Helper methods
    public function isAbnormal(): bool
    {
        return !in_array($this->flag, ['normal']);
    }

    public function requiresAttention(): bool
    {
        return in_array($this->flag, ['critical_high', 'critical_low']) || $this->is_critical;
    }

    public function canBeVerified(): bool
    {
        return is_null($this->verified_at);
    }

    public function verify(User $user): bool
    {
        if (!$this->canBeVerified()) {
            return false;
        }

        $this->update([
            'verified_by' => $user->id,
            'verified_at' => now()
        ]);

        return true;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($result) {
            // Auto-detect critical values based on lab test configuration
            if ($result->labOrder && $result->labOrder->labTest) {
                $labTest = $result->labOrder->labTest;
                
                if (is_numeric($result->value) && $labTest->isCriticalValue($result->value, $result->parameter_name)) {
                    $result->is_critical = true;
                }
            }
        });

        static::created(function ($result) {
            // Send critical value notifications
            if ($result->is_critical) {
                // TODO: Implement critical value notification system
                // This could send notifications to doctors, lab supervisors, etc.
            }
        });
    }
}
