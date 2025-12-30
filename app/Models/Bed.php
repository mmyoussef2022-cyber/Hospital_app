<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'bed_number',
        'bed_type',
        'status',
        'features',
        'is_active',
        'last_occupied_at',
        'last_cleaned_at'
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'last_occupied_at' => 'datetime',
        'last_cleaned_at' => 'datetime'
    ];

    // Relationships
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RoomAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(RoomAssignment::class)->where('status', 'active');
    }

    public function currentPatient()
    {
        return $this->currentAssignment?->patient;
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('bed_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'available' => 'متاح',
            'occupied' => 'مشغول',
            'maintenance' => 'صيانة',
            'cleaning' => 'تنظيف',
            'reserved' => 'محجوز',
            default => $this->status
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'available' => 'success',
            'occupied' => 'danger',
            'maintenance' => 'warning',
            'cleaning' => 'info',
            'reserved' => 'secondary',
            default => 'secondary'
        };
    }

    public function getBedTypeDisplayAttribute(): string
    {
        return match($this->bed_type) {
            'standard' => 'عادي',
            'icu' => 'عناية مركزة',
            'pediatric' => 'أطفال',
            'bariatric' => 'بدانة',
            default => $this->bed_type
        };
    }

    public function getFullBedNumberAttribute(): string
    {
        return $this->room->room_number . '-' . $this->bed_number;
    }

    // Helper methods
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    public function canBeAssigned(): bool
    {
        return $this->isAvailable();
    }

    public function assignPatient(Patient $patient, User $assignedBy, $notes = null): RoomAssignment
    {
        if (!$this->canBeAssigned()) {
            throw new \Exception('Bed is not available for assignment');
        }

        // Create assignment
        $assignment = $this->assignments()->create([
            'patient_id' => $patient->id,
            'room_id' => $this->room_id,
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
            'assignment_notes' => $notes,
            'status' => 'active'
        ]);

        // Update bed status
        $this->update([
            'status' => 'occupied',
            'last_occupied_at' => now()
        ]);

        return $assignment;
    }

    public function dischargePatient(User $dischargedBy, $notes = null): bool
    {
        $assignment = $this->currentAssignment;

        if (!$assignment) {
            return false;
        }

        // Update assignment
        $assignment->update([
            'status' => 'discharged',
            'actual_discharge_at' => now(),
            'discharge_notes' => $notes
        ]);

        // Update bed status
        $this->update(['status' => 'cleaning']);

        return true;
    }

    public function needsCleaning(): bool
    {
        return !$this->last_cleaned_at || 
               $this->last_cleaned_at->lt(now()->subHours(12));
    }

    public function markCleaned(): void
    {
        $this->update([
            'status' => 'available',
            'last_cleaned_at' => now()
        ]);
    }

    public function markMaintenance(): void
    {
        $this->update(['status' => 'maintenance']);
    }

    public function completeMaintenance(): void
    {
        $this->update(['status' => 'available']);
    }

    // Static methods
    public static function getAvailableBeds($roomType = null, $bedType = null)
    {
        $query = static::available()->with('room');
        
        if ($roomType) {
            $query->whereHas('room', function($q) use ($roomType) {
                $q->where('room_type', $roomType);
            });
        }
        
        if ($bedType) {
            $query->byType($bedType);
        }
        
        return $query->get();
    }
}