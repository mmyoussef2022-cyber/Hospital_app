<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class OperatingRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'or_number',
        'name',
        'name_ar',
        'or_type',
        'capabilities',
        'equipment',
        'monitoring_systems',
        'has_laminar_flow',
        'has_imaging',
        'has_robotic_system',
        'has_cardiac_bypass',
        'has_neuro_monitoring',
        'temperature_min',
        'temperature_max',
        'humidity_min',
        'humidity_max',
        'status',
        'last_cleaned_at',
        'last_maintenance_at',
        'next_maintenance_due',
        'cleaning_notes',
        'maintenance_notes',
        'setup_notes',
        'is_active',
        'is_emergency_ready'
    ];

    protected $casts = [
        'capabilities' => 'array',
        'equipment' => 'array',
        'monitoring_systems' => 'array',
        'has_laminar_flow' => 'boolean',
        'has_imaging' => 'boolean',
        'has_robotic_system' => 'boolean',
        'has_cardiac_bypass' => 'boolean',
        'has_neuro_monitoring' => 'boolean',
        'last_cleaned_at' => 'datetime',
        'last_maintenance_at' => 'datetime',
        'next_maintenance_due' => 'datetime',
        'is_active' => 'boolean',
        'is_emergency_ready' => 'boolean'
    ];

    // Relationships
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function surgeries(): HasMany
    {
        return $this->hasMany(Surgery::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeEmergencyReady($query)
    {
        return $query->where('is_emergency_ready', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('or_type', $type);
    }

    public function scopeWithCapability($query, $capability)
    {
        return $query->whereJsonContains('capabilities', $capability);
    }

    public function scopeWithEquipment($query, $equipment)
    {
        return $query->whereJsonContains('equipment', $equipment);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }

    public function getOrTypeDisplayAttribute(): string
    {
        return match($this->or_type) {
            'general' => 'عام',
            'cardiac' => 'قلب',
            'orthopedic' => 'عظام',
            'neurosurgery' => 'جراحة أعصاب',
            'ophthalmology' => 'عيون',
            'ent' => 'أنف وأذن وحنجرة',
            'gynecology' => 'نساء وولادة',
            'urology' => 'مسالك بولية',
            'plastic' => 'تجميل',
            'trauma' => 'إصابات',
            'pediatric' => 'أطفال',
            'hybrid' => 'مختلط',
            default => $this->or_type
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'available' => 'متاح',
            'occupied' => 'مشغول',
            'cleaning' => 'تنظيف',
            'maintenance' => 'صيانة',
            'setup' => 'إعداد',
            'turnover' => 'تبديل',
            'emergency_ready' => 'جاهز للطوارئ',
            default => $this->status
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'available' => 'success',
            'occupied' => 'danger',
            'cleaning' => 'info',
            'maintenance' => 'warning',
            'setup' => 'primary',
            'turnover' => 'secondary',
            'emergency_ready' => 'success',
            default => 'light'
        };
    }

    public function getCurrentSurgeryAttribute()
    {
        return $this->surgeries()
                   ->where('status', 'in_progress')
                   ->with(['patient', 'primarySurgeon', 'surgicalProcedure'])
                   ->first();
    }

    public function getNextSurgeryAttribute()
    {
        return $this->surgeries()
                   ->where('status', 'scheduled')
                   ->where('scheduled_start_time', '>', now())
                   ->orderBy('scheduled_start_time')
                   ->with(['patient', 'primarySurgeon', 'surgicalProcedure'])
                   ->first();
    }

    public function getNeedsMaintenanceAttribute(): bool
    {
        return $this->next_maintenance_due && $this->next_maintenance_due <= now();
    }

    public function getNeedsCleaningAttribute(): bool
    {
        if (!$this->last_cleaned_at) {
            return true;
        }
        
        // Needs cleaning if last cleaned more than 24 hours ago
        return $this->last_cleaned_at->addHours(24) <= now();
    }

    // Business Logic Methods
    public function isAvailableAt(Carbon $startTime, Carbon $endTime): bool
    {
        if (!$this->is_active || $this->status !== 'available') {
            return false;
        }

        // Check for conflicting surgeries
        return !$this->surgeries()
                    ->where(function($query) use ($startTime, $endTime) {
                        $query->whereBetween('scheduled_start_time', [$startTime, $endTime])
                              ->orWhereBetween('scheduled_end_time', [$startTime, $endTime])
                              ->orWhere(function($q) use ($startTime, $endTime) {
                                  $q->where('scheduled_start_time', '<=', $startTime)
                                    ->where('scheduled_end_time', '>=', $endTime);
                              });
                    })
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->exists();
    }

    public function canAccommodateProcedure(SurgicalProcedure $procedure): bool
    {
        // Check if OR type is compatible
        if ($this->or_type !== 'general' && $this->or_type !== $procedure->specialty) {
            return false;
        }

        // Check required equipment
        if ($procedure->required_equipment) {
            foreach ($procedure->required_equipment as $equipment) {
                if (!in_array($equipment, $this->equipment ?? [])) {
                    return false;
                }
            }
        }

        // Check special requirements
        if ($procedure->requires_icu && !$this->has_cardiac_bypass) {
            // This is a simplified check - in reality, you'd check for ICU proximity
        }

        return true;
    }

    public function markCleaned(string $notes = null): bool
    {
        $this->update([
            'status' => 'available',
            'last_cleaned_at' => now(),
            'cleaning_notes' => $notes
        ]);

        return true;
    }

    public function markMaintenance(string $notes = null): bool
    {
        if ($this->status === 'occupied') {
            return false;
        }

        $this->update([
            'status' => 'maintenance',
            'maintenance_notes' => $notes
        ]);

        return true;
    }

    public function completeMaintenance(string $notes = null): bool
    {
        $this->update([
            'status' => 'available',
            'last_maintenance_at' => now(),
            'next_maintenance_due' => now()->addMonths(3), // Schedule next maintenance
            'maintenance_notes' => $notes
        ]);

        return true;
    }

    public function startSetup(string $notes = null): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        $this->update([
            'status' => 'setup',
            'setup_notes' => $notes
        ]);

        return true;
    }

    public function completeSetup(): bool
    {
        $this->update([
            'status' => 'available'
        ]);

        return true;
    }

    public function startTurnover(): bool
    {
        $this->update([
            'status' => 'turnover'
        ]);

        return true;
    }

    public function completeTurnover(): bool
    {
        $this->update([
            'status' => 'cleaning'
        ]);

        return true;
    }

    public function reserveForEmergency(): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        $this->update([
            'status' => 'emergency_ready',
            'is_emergency_ready' => true
        ]);

        return true;
    }

    public function releaseFromEmergency(): bool
    {
        $this->update([
            'status' => 'available',
            'is_emergency_ready' => false
        ]);

        return true;
    }

    public function getScheduleForDate(Carbon $date): \Illuminate\Database\Eloquent\Collection
    {
        return $this->surgeries()
                   ->whereDate('scheduled_start_time', $date)
                   ->with(['patient', 'primarySurgeon', 'surgicalProcedure'])
                   ->orderBy('scheduled_start_time')
                   ->get();
    }

    public function getUtilizationRate(Carbon $startDate = null, Carbon $endDate = null): float
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $totalMinutes = $startDate->diffInMinutes($endDate);
        
        $usedMinutes = $this->surgeries()
                           ->whereBetween('scheduled_start_time', [$startDate, $endDate])
                           ->whereNotIn('status', ['cancelled'])
                           ->sum('estimated_duration');

        return $totalMinutes > 0 ? ($usedMinutes / $totalMinutes) * 100 : 0;
    }

    // Static Methods
    public static function getAvailableForProcedure(SurgicalProcedure $procedure, Carbon $startTime, Carbon $endTime)
    {
        return static::active()
                    ->available()
                    ->get()
                    ->filter(function($room) use ($procedure, $startTime, $endTime) {
                        return $room->canAccommodateProcedure($procedure) && 
                               $room->isAvailableAt($startTime, $endTime);
                    });
    }

    public static function getEmergencyReady()
    {
        return static::emergencyReady()
                    ->active()
                    ->with(['room', 'currentSurgery'])
                    ->get();
    }

    public static function getUtilizationStatistics(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $rooms = static::active()->get();
        $totalUtilization = 0;
        $roomCount = $rooms->count();

        foreach ($rooms as $room) {
            $totalUtilization += $room->getUtilizationRate($startDate, $endDate);
        }

        return [
            'average_utilization' => $roomCount > 0 ? $totalUtilization / $roomCount : 0,
            'total_rooms' => $roomCount,
            'available_rooms' => static::available()->count(),
            'occupied_rooms' => static::occupied()->count(),
            'maintenance_rooms' => static::where('status', 'maintenance')->count(),
            'emergency_ready_rooms' => static::emergencyReady()->count()
        ];
    }
}
