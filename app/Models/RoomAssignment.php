<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'room_id',
        'bed_id',
        'assigned_by',
        'assigned_at',
        'expected_discharge_at',
        'actual_discharge_at',
        'assignment_type',
        'status',
        'assignment_notes',
        'discharge_notes',
        'total_charges'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expected_discharge_at' => 'datetime',
        'actual_discharge_at' => 'datetime',
        'total_charges' => 'decimal:2'
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByAssignmentType($query, $type)
    {
        return $query->where('assignment_type', $type);
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'active' => 'نشط',
            'discharged' => 'تم الخروج',
            'transferred' => 'تم النقل',
            'cancelled' => 'ملغي',
            default => $this->status
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'discharged' => 'primary',
            'transferred' => 'info',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getAssignmentTypeDisplayAttribute(): string
    {
        return match($this->assignment_type) {
            'admission' => 'دخول',
            'transfer' => 'نقل',
            'emergency' => 'طوارئ',
            default => $this->assignment_type
        };
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->assigned_at) return null;
        
        $endDate = $this->actual_discharge_at ?? now();
        return $this->assigned_at->diffInDays($endDate);
    }

    public function getDurationDisplayAttribute(): string
    {
        $duration = $this->getDurationAttribute();
        
        if ($duration === null) return 'غير محدد';
        if ($duration == 0) return 'أقل من يوم';
        if ($duration == 1) return 'يوم واحد';
        if ($duration <= 10) return $duration . ' أيام';
        
        return $duration . ' يوماً';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->expected_discharge_at && 
               $this->expected_discharge_at->lt(now()) && 
               $this->status === 'active';
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canBeTransferred(): bool
    {
        return $this->status === 'active';
    }

    public function canBeDischarged(): bool
    {
        return $this->status === 'active';
    }

    public function calculateCharges(): float
    {
        if (!$this->room || !$this->assigned_at) return 0;
        
        $days = $this->getDurationAttribute() ?? 0;
        $dailyRate = $this->room->daily_rate ?? 0;
        
        return $days * $dailyRate;
    }

    public function updateCharges(): void
    {
        $this->update(['total_charges' => $this->calculateCharges()]);
    }

    public function discharge(User $dischargedBy, $notes = null): bool
    {
        if (!$this->canBeDischarged()) {
            return false;
        }

        // Update assignment
        $this->update([
            'status' => 'discharged',
            'actual_discharge_at' => now(),
            'discharge_notes' => $notes
        ]);

        // Update charges
        $this->updateCharges();

        // Update bed status
        if ($this->bed) {
            $this->bed->update(['status' => 'cleaning']);
        }

        // Update room status if no longer fully occupied
        if ($this->room && $this->room->status === 'occupied' && $this->room->hasAvailableBeds()) {
            $this->room->update(['status' => 'available']);
        }

        return true;
    }

    public function transfer(Room $newRoom, Bed $newBed = null, User $transferredBy, $notes = null): ?RoomAssignment
    {
        if (!$this->canBeTransferred()) {
            return null;
        }

        // Check if new room/bed is available
        if (!$newRoom->canBeAssigned()) {
            throw new \Exception('Target room is not available');
        }

        if ($newBed && !$newBed->canBeAssigned()) {
            throw new \Exception('Target bed is not available');
        }

        // If no specific bed, auto-assign
        if (!$newBed) {
            $newBed = $newRoom->beds()->where('status', 'available')->first();
            if (!$newBed) {
                throw new \Exception('No available beds in target room');
            }
        }

        // Close current assignment
        $this->update([
            'status' => 'transferred',
            'actual_discharge_at' => now(),
            'discharge_notes' => $notes
        ]);

        // Update charges for current assignment
        $this->updateCharges();

        // Free current bed
        if ($this->bed) {
            $this->bed->update(['status' => 'cleaning']);
        }

        // Create new assignment
        $newAssignment = static::create([
            'patient_id' => $this->patient_id,
            'room_id' => $newRoom->id,
            'bed_id' => $newBed->id,
            'assigned_by' => $transferredBy->id,
            'assigned_at' => now(),
            'assignment_type' => 'transfer',
            'assignment_notes' => $notes,
            'status' => 'active'
        ]);

        // Update new bed status
        $newBed->update([
            'status' => 'occupied',
            'last_occupied_at' => now()
        ]);

        // Update room statuses
        if ($this->room && $this->room->hasAvailableBeds()) {
            $this->room->update(['status' => 'available']);
        }

        if (!$newRoom->hasAvailableBeds()) {
            $newRoom->update(['status' => 'occupied']);
        }

        return $newAssignment;
    }

    // Static methods
    public static function getActiveAssignments()
    {
        return static::active()
                    ->with(['patient', 'room', 'bed', 'assignedBy'])
                    ->orderBy('assigned_at', 'desc')
                    ->get();
    }

    public static function getOverdueAssignments()
    {
        return static::active()
                    ->whereNotNull('expected_discharge_at')
                    ->where('expected_discharge_at', '<', now())
                    ->with(['patient', 'room', 'bed'])
                    ->get();
    }

    public static function getAssignmentStatistics()
    {
        $total = static::count();
        $active = static::active()->count();
        $discharged = static::where('status', 'discharged')->count();
        $transferred = static::where('status', 'transferred')->count();
        $overdue = static::getOverdueAssignments()->count();
        
        return [
            'total' => $total,
            'active' => $active,
            'discharged' => $discharged,
            'transferred' => $transferred,
            'overdue' => $overdue,
            'average_stay' => static::where('status', 'discharged')
                                  ->whereNotNull('actual_discharge_at')
                                  ->get()
                                  ->avg(function($assignment) {
                                      return $assignment->getDurationAttribute();
                                  }) ?? 0
        ];
    }
}