<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Surgery extends Model
{
    use HasFactory;

    protected $fillable = [
        'surgery_number',
        'patient_id',
        'primary_surgeon_id',
        'surgical_procedure_id',
        'operating_room_id',
        'appointment_id',
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_start_time',
        'actual_end_time',
        'priority',
        'status',
        'type',
        'pre_operative_notes',
        'operative_notes',
        'post_operative_notes',
        'complications',
        'cancellation_reason',
        'anesthesia_details',
        'equipment_used',
        'medications_given',
        'blood_loss',
        'estimated_cost',
        'actual_cost',
        'estimated_duration',
        'actual_duration',
        'is_emergency',
        'requires_icu',
        'requires_blood_bank',
        'is_completed',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'anesthesia_details' => 'array',
        'equipment_used' => 'array',
        'medications_given' => 'array',
        'blood_loss' => 'array',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'is_emergency' => 'boolean',
        'requires_icu' => 'boolean',
        'requires_blood_bank' => 'boolean',
        'is_completed' => 'boolean'
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function primarySurgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_surgeon_id');
    }

    public function surgicalProcedure(): BelongsTo
    {
        return $this->belongsTo(SurgicalProcedure::class);
    }

    public function operatingRoom(): BelongsTo
    {
        return $this->belongsTo(OperatingRoom::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function surgicalTeam(): HasMany
    {
        return $this->hasMany(SurgicalTeam::class);
    }

    public function preOperativeAssessment(): HasOne
    {
        return $this->hasOne(PreOperativeAssessment::class);
    }

    public function postOperativeCare(): HasOne
    {
        return $this->hasOne(PostOperativeCare::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_start_time', today());
    }

    public function scopeTomorrow($query)
    {
        return $query->whereDate('scheduled_start_time', today()->addDay());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_start_time', '>=', now());
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeEmergency($query)
    {
        return $query->where('is_emergency', true);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySurgeon($query, $surgeonId)
    {
        return $query->where('primary_surgeon_id', $surgeonId);
    }

    public function scopeByOperatingRoom($query, $roomId)
    {
        return $query->where('operating_room_id', $roomId);
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'مجدولة',
            'pre_op' => 'ما قبل العملية',
            'in_progress' => 'جارية',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغية',
            'postponed' => 'مؤجلة',
            default => $this->status
        };
    }

    public function getPriorityDisplayAttribute(): string
    {
        return match($this->priority) {
            'routine' => 'روتينية',
            'urgent' => 'عاجلة',
            'emergency' => 'طارئة',
            'elective' => 'اختيارية',
            default => $this->priority
        };
    }

    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'inpatient' => 'مريض داخلي',
            'outpatient' => 'مريض خارجي',
            'day_surgery' => 'جراحة يومية',
            'emergency' => 'طوارئ',
            default => $this->type
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'primary',
            'pre_op' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'postponed' => 'secondary',
            default => 'light'
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'routine' => 'success',
            'urgent' => 'warning',
            'emergency' => 'danger',
            'elective' => 'info',
            default => 'light'
        };
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->actual_start_time && $this->actual_end_time) {
            return $this->actual_start_time->diffInMinutes($this->actual_end_time);
        }
        return null;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->scheduled_end_time < now() && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function getIsDelayedAttribute(): bool
    {
        return $this->scheduled_start_time < now() && $this->status === 'scheduled';
    }

    // Business Logic Methods
    public function canBeStarted(): bool
    {
        return $this->status === 'scheduled' && 
               $this->preOperativeAssessment?->is_cleared_for_surgery === true &&
               $this->operatingRoom?->status === 'available';
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled', 'in_progress']);
    }

    public function canBePostponed(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled', 'in_progress']);
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress';
    }

    public function startSurgery(): bool
    {
        if (!$this->canBeStarted()) {
            return false;
        }

        $this->update([
            'status' => 'in_progress',
            'actual_start_time' => now()
        ]);

        // Update operating room status
        $this->operatingRoom?->update(['status' => 'occupied']);

        return true;
    }

    public function completeSurgery(array $data = []): bool
    {
        if (!$this->canBeCompleted()) {
            return false;
        }

        $updateData = array_merge([
            'status' => 'completed',
            'actual_end_time' => now(),
            'is_completed' => true
        ], $data);

        $this->update($updateData);

        // Update operating room status
        $this->operatingRoom?->update(['status' => 'cleaning']);

        return true;
    }

    public function cancelSurgery(string $reason): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason
        ]);

        // Free up operating room
        $this->operatingRoom?->update(['status' => 'available']);

        return true;
    }

    public function postponeSurgery(Carbon $newStartTime, Carbon $newEndTime, string $reason = null): bool
    {
        if (!$this->canBePostponed()) {
            return false;
        }

        $this->update([
            'status' => 'postponed',
            'scheduled_start_time' => $newStartTime,
            'scheduled_end_time' => $newEndTime,
            'cancellation_reason' => $reason
        ]);

        return true;
    }

    public function assignTeamMember(User $user, string $role, array $data = []): SurgicalTeam
    {
        return $this->surgicalTeam()->create(array_merge([
            'user_id' => $user->id,
            'role' => $role,
            'assigned_at' => now()
        ], $data));
    }

    public function removeTeamMember(User $user, string $role = null): bool
    {
        $query = $this->surgicalTeam()->where('user_id', $user->id);
        
        if ($role) {
            $query->where('role', $role);
        }

        return $query->delete() > 0;
    }

    public function getTeamByRole(string $role)
    {
        return $this->surgicalTeam()->where('role', $role)->with('user')->get();
    }

    public function hasConflictWith(Carbon $startTime, Carbon $endTime, int $operatingRoomId = null): bool
    {
        $query = static::where('id', '!=', $this->id)
                      ->where(function($q) use ($startTime, $endTime) {
                          $q->whereBetween('scheduled_start_time', [$startTime, $endTime])
                            ->orWhereBetween('scheduled_end_time', [$startTime, $endTime])
                            ->orWhere(function($q2) use ($startTime, $endTime) {
                                $q2->where('scheduled_start_time', '<=', $startTime)
                                   ->where('scheduled_end_time', '>=', $endTime);
                            });
                      })
                      ->whereNotIn('status', ['completed', 'cancelled']);

        if ($operatingRoomId) {
            $query->where('operating_room_id', $operatingRoomId);
        }

        return $query->exists();
    }

    // Static Methods
    public static function generateSurgeryNumber(): string
    {
        $prefix = 'SUR';
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        
        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public static function getEmergencyQueue()
    {
        return static::where('is_emergency', true)
                    ->whereIn('status', ['scheduled', 'pre_op'])
                    ->orderBy('priority', 'desc')
                    ->orderBy('scheduled_start_time')
                    ->get();
    }

    public static function getTodaysSurgeries()
    {
        return static::today()
                    ->with(['patient', 'primarySurgeon', 'operatingRoom', 'surgicalProcedure'])
                    ->orderBy('scheduled_start_time')
                    ->get();
    }

    public static function getOperatingRoomSchedule(int $roomId, Carbon $date = null)
    {
        $date = $date ?? today();
        
        return static::where('operating_room_id', $roomId)
                    ->whereDate('scheduled_start_time', $date)
                    ->with(['patient', 'primarySurgeon', 'surgicalProcedure'])
                    ->orderBy('scheduled_start_time')
                    ->get();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($surgery) {
            if (empty($surgery->surgery_number)) {
                $surgery->surgery_number = static::generateSurgeryNumber();
            }
        });
    }
}
