<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'parent_appointment_id',
        'appointment_date',
        'appointment_time',
        'duration',
        'type',
        'status',
        'notes',
        'reminder_sent_at'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Parent appointment relationship (for follow-up appointments)
     */
    public function parentAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'parent_appointment_id');
    }

    /**
     * Follow-up appointments relationship
     */
    public function followUpAppointments()
    {
        return $this->hasMany(Appointment::class, 'parent_appointment_id');
    }

    /**
     * Medical record relationship
     */
    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', today());
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('appointment_date', [$startDate, $endDate]);
    }

    // Accessors & Mutators
    public function getFullDateTimeAttribute()
    {
        return Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->appointment_time);
    }

    public function getEndTimeAttribute()
    {
        return $this->getFullDateTimeAttribute()->addMinutes($this->duration);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'scheduled' => 'primary',
            'confirmed' => 'success',
            'in_progress' => 'warning',
            'completed' => 'info',
            'cancelled' => 'danger',
            'no_show' => 'secondary',
            default => 'primary'
        };
    }

    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'consultation' => 'استشارة',
            'follow_up' => 'متابعة',
            'emergency' => 'طوارئ',
            'surgery' => 'جراحة',
            default => 'استشارة'
        };
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'scheduled' => 'مجدول',
            'confirmed' => 'مؤكد',
            'in_progress' => 'جاري',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'no_show' => 'لم يحضر',
            default => 'مجدول'
        };
    }

    // Helper methods
    public function isToday(): bool
    {
        return $this->appointment_date->isToday();
    }

    public function isPast(): bool
    {
        return $this->getFullDateTimeAttribute()->isPast();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']) && !$this->isPast();
    }

    public function canBeRescheduled(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']);
    }

    // Static methods for conflict checking
    public static function hasConflict($doctorId, $date, $time, $duration = 30, $excludeId = null)
    {
        $startTime = Carbon::parse($date . ' ' . $time);
        $endTime = $startTime->copy()->addMinutes($duration);

        $query = self::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get()->filter(function ($appointment) use ($startTime, $endTime) {
            $appointmentStart = $appointment->getFullDateTimeAttribute();
            $appointmentEnd = $appointment->getEndTimeAttribute();

            // Check for overlap
            return $startTime->lt($appointmentEnd) && $endTime->gt($appointmentStart);
        })->isNotEmpty();
    }

    public static function getAvailableSlots($doctorId, $date, $duration = 30)
    {
        // Define working hours (can be made configurable later)
        $workStart = Carbon::parse($date . ' 08:00');
        $workEnd = Carbon::parse($date . ' 18:00');
        
        $slots = [];
        $current = $workStart->copy();

        while ($current->addMinutes($duration)->lte($workEnd)) {
            if (!self::hasConflict($doctorId, $date, $current->format('H:i'), $duration)) {
                $slots[] = $current->format('H:i');
            }
            $current->addMinutes($duration);
        }

        return $slots;
    }
}
