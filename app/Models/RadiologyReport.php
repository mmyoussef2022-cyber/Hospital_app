<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'radiology_order_id',
        'technique',
        'findings',
        'impression',
        'recommendations',
        'urgency_level',
        'has_urgent_findings',
        'urgent_findings',
        'radiologist_id',
        'verified_by',
        'dictated_at',
        'transcribed_at',
        'verified_at',
        'finalized_at',
        'dicom_files',
        'image_attachments',
        'addendum'
    ];

    protected $casts = [
        'dictated_at' => 'datetime',
        'transcribed_at' => 'datetime',
        'verified_at' => 'datetime',
        'finalized_at' => 'datetime',
        'has_urgent_findings' => 'boolean',
        'dicom_files' => 'array',
        'image_attachments' => 'array'
    ];

    // Relationships
    public function radiologyOrder(): BelongsTo
    {
        return $this->belongsTo(RadiologyOrder::class);
    }

    public function radiologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'radiologist_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeUrgent($query)
    {
        return $query->where('has_urgent_findings', true);
    }

    public function scopeFinalized($query)
    {
        return $query->whereNotNull('finalized_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('finalized_at');
    }

    public function scopeByRadiologist($query, $radiologistId)
    {
        return $query->where('radiologist_id', $radiologistId);
    }

    public function scopeByUrgencyLevel($query, $level)
    {
        return $query->where('urgency_level', $level);
    }

    // Accessors
    public function getUrgencyLevelDisplayAttribute(): string
    {
        return match($this->urgency_level) {
            'routine' => 'عادي',
            'urgent' => 'عاجل',
            'critical' => 'حرج',
            default => $this->urgency_level
        };
    }

    public function getUrgencyColorAttribute(): string
    {
        return match($this->urgency_level) {
            'routine' => 'secondary',
            'urgent' => 'warning',
            'critical' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        if ($this->finalized_at) {
            return 'نهائي';
        } elseif ($this->verified_at) {
            return 'تم التحقق';
        } elseif ($this->transcribed_at) {
            return 'تم النسخ';
        } elseif ($this->dictated_at) {
            return 'تم الإملاء';
        } else {
            return 'مسودة';
        }
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->finalized_at) {
            return 'success';
        } elseif ($this->verified_at) {
            return 'info';
        } elseif ($this->transcribed_at) {
            return 'primary';
        } elseif ($this->dictated_at) {
            return 'warning';
        } else {
            return 'secondary';
        }
    }

    // Helper methods
    public function isFinalized(): bool
    {
        return $this->finalized_at !== null;
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function canBeVerified(): bool
    {
        return $this->transcribed_at && !$this->verified_at;
    }

    public function canBeFinalized(): bool
    {
        return $this->verified_at && !$this->finalized_at;
    }

    public function requiresUrgentNotification(): bool
    {
        return $this->has_urgent_findings && !$this->radiologyOrder->urgent_notified_at;
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

    public function finalize(): bool
    {
        if (!$this->canBeFinalized()) {
            return false;
        }

        $this->update([
            'finalized_at' => now()
        ]);

        // Update the order status to reported
        $this->radiologyOrder->update([
            'status' => 'reported',
            'reported_at' => now()
        ]);

        return true;
    }

    public function addAddendum(string $addendum, User $user): bool
    {
        if (!$this->isFinalized()) {
            return false;
        }

        $currentAddendum = $this->addendum ?? '';
        $newAddendum = $currentAddendum . "\n\n--- إضافة بتاريخ " . now()->format('Y-m-d H:i') . " بواسطة " . $user->name . " ---\n" . $addendum;

        $this->update(['addendum' => $newAddendum]);

        return true;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (!$report->dictated_at) {
                $report->dictated_at = now();
            }
        });

        static::created(function ($report) {
            // Send urgent notifications if needed
            if ($report->has_urgent_findings && $report->urgent_findings) {
                // TODO: Implement urgent finding notification system
                // This could send notifications to referring doctors, emergency contacts, etc.
            }
        });

        static::updated(function ($report) {
            // Handle urgent findings notifications
            if ($report->isDirty('has_urgent_findings') && $report->has_urgent_findings) {
                $report->radiologyOrder->update([
                    'has_urgent_findings' => true,
                    'urgent_notified_at' => now()
                ]);
            }
        });
    }
}
