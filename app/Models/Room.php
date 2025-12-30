<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'room_type',
        'department',
        'floor',
        'wing',
        'capacity',
        'daily_rate',
        'description',
        'amenities',
        'equipment',
        'status',
        'is_active',
        'last_cleaned_at',
        'last_maintenance_at'
    ];

    protected $casts = [
        'amenities' => 'array',
        'equipment' => 'array',
        'daily_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'last_cleaned_at' => 'datetime',
        'last_maintenance_at' => 'datetime'
    ];

    // Relationships
    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RoomAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(RoomAssignment::class)->where('status', 'active');
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(RoomAssignment::class)->where('status', 'active');
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
        return $query->where('room_type', $type);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByFloor($query, $floor)
    {
        return $query->where('floor', $floor);
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

    public function getRoomTypeDisplayAttribute(): string
    {
        return match($this->room_type) {
            'ward' => 'جناح عام',
            'icu' => 'العناية المركزة',
            'emergency' => 'طوارئ',
            'surgery' => 'جراحة',
            'private' => 'غرفة خاصة',
            'semi_private' => 'غرفة نصف خاصة',
            default => $this->room_type
        };
    }

    public function getOccupancyRateAttribute(): float
    {
        if ($this->capacity == 0) return 0;
        
        $occupiedBeds = $this->beds()->where('status', 'occupied')->count();
        return ($occupiedBeds / $this->capacity) * 100;
    }

    public function getAvailableBedsCountAttribute(): int
    {
        return $this->beds()->where('status', 'available')->count();
    }

    public function getOccupiedBedsCountAttribute(): int
    {
        return $this->beds()->where('status', 'occupied')->count();
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
        return $this->isAvailable() && $this->getAvailableBedsCountAttribute() > 0;
    }

    public function hasAvailableBeds(): bool
    {
        return $this->getAvailableBedsCountAttribute() > 0;
    }

    public function assignPatient(Patient $patient, User $assignedBy, $bedId = null, $notes = null): RoomAssignment
    {
        if (!$this->canBeAssigned()) {
            throw new \Exception('Room is not available for assignment');
        }

        // If specific bed requested, check availability
        if ($bedId) {
            $bed = $this->beds()->find($bedId);
            if (!$bed || !$bed->isAvailable()) {
                throw new \Exception('Requested bed is not available');
            }
        } else {
            // Auto-assign first available bed
            $bed = $this->beds()->where('status', 'available')->first();
            if (!$bed) {
                throw new \Exception('No available beds in this room');
            }
        }

        // Create assignment
        $assignment = $this->assignments()->create([
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
            'assignment_notes' => $notes,
            'status' => 'active'
        ]);

        // Update room and bed status
        $bed->update(['status' => 'occupied', 'last_occupied_at' => now()]);
        
        // Update room status if fully occupied
        if ($this->getAvailableBedsCountAttribute() == 0) {
            $this->update(['status' => 'occupied']);
        }

        return $assignment;
    }

    public function dischargePatient(Patient $patient, User $dischargedBy, $notes = null): bool
    {
        $assignment = $this->activeAssignments()
                          ->where('patient_id', $patient->id)
                          ->first();

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
        if ($assignment->bed) {
            $assignment->bed->update(['status' => 'cleaning']);
        }

        // Update room status if no longer fully occupied
        if ($this->status === 'occupied' && $this->hasAvailableBeds()) {
            $this->update(['status' => 'available']);
        }

        return true;
    }

    public function needsCleaning(): bool
    {
        return !$this->last_cleaned_at || 
               $this->last_cleaned_at->lt(now()->subHours(24));
    }

    public function needsMaintenance(): bool
    {
        return !$this->last_maintenance_at || 
               $this->last_maintenance_at->lt(now()->subDays(30));
    }

    public function markCleaned(): void
    {
        $this->update([
            'last_cleaned_at' => now(),
            'status' => $this->hasAvailableBeds() ? 'available' : 'occupied'
        ]);

        // Mark beds as available if they were cleaning
        $this->beds()->where('status', 'cleaning')->update(['status' => 'available']);
    }

    public function markMaintenance(): void
    {
        $this->update([
            'status' => 'maintenance',
            'last_maintenance_at' => now()
        ]);

        // Mark all beds as maintenance
        $this->beds()->update(['status' => 'maintenance']);
    }

    public function completeMaintenance(): void
    {
        $this->update([
            'status' => 'available',
            'last_maintenance_at' => now()
        ]);

        // Mark beds as available
        $this->beds()->update(['status' => 'available']);
    }

    // Static methods
    public static function getAvailableRooms($roomType = null, $department = null)
    {
        $query = static::available();
        
        if ($roomType) {
            $query->byType($roomType);
        }
        
        if ($department) {
            $query->byDepartment($department);
        }
        
        return $query->get();
    }

    public static function getOccupancyStatistics()
    {
        $total = static::active()->count();
        $available = static::available()->count();
        $occupied = static::occupied()->count();
        $maintenance = static::where('status', 'maintenance')->count();
        
        return [
            'total' => $total,
            'available' => $available,
            'occupied' => $occupied,
            'maintenance' => $maintenance,
            'occupancy_rate' => $total > 0 ? ($occupied / $total) * 100 : 0
        ];
    }
}