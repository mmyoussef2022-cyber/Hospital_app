<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DentalSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_number',
        'dental_treatment_id',
        'appointment_id',
        'session_order',
        'session_title',
        'session_description',
        'procedures_performed',
        'materials_used',
        'session_cost',
        'session_payment',
        'scheduled_date',
        'completed_date',
        'duration',
        'status',
        'session_notes',
        'session_photos',
        'next_session_plan',
        'complications',
        'pain_level_before',
        'pain_level_after',
        'follow_up_required',
        'follow_up_date'
    ];

    protected $casts = [
        'procedures_performed' => 'array',
        'materials_used' => 'array',
        'session_photos' => 'array',
        'complications' => 'array',
        'session_cost' => 'decimal:2',
        'session_payment' => 'decimal:2',
        'pain_level_before' => 'decimal:1',
        'pain_level_after' => 'decimal:1',
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'follow_up_date' => 'date',
        'follow_up_required' => 'boolean'
    ];

    // Relationships
    public function dentalTreatment(): BelongsTo
    {
        return $this->belongsTo(DentalTreatment::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>=', today())
                    ->where('status', 'scheduled');
    }

    public function scopeByTreatment($query, $treatmentId)
    {
        return $query->where('dental_treatment_id', $treatmentId);
    }

    public function scopeRequiringFollowUp($query)
    {
        return $query->where('follow_up_required', true)
                    ->whereNull('follow_up_date');
    }

    // Accessors
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'scheduled' => 'مجدولة',
            'in_progress' => 'جارية',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغية',
            'no_show' => 'لم يحضر',
            default => 'غير محدد'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'scheduled' => 'primary',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            'no_show' => 'secondary',
            default => 'primary'
        };
    }

    public function getDurationInMinutesAttribute()
    {
        if (!$this->duration) return 0;
        
        $time = Carbon::parse($this->duration);
        return ($time->hour * 60) + $time->minute;
    }

    public function getDurationDisplayAttribute()
    {
        if (!$this->duration) return 'غير محدد';
        
        $minutes = $this->duration_in_minutes;
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0) {
            return $hours . ' ساعة' . ($mins > 0 ? ' و ' . $mins . ' دقيقة' : '');
        }
        
        return $mins . ' دقيقة';
    }

    public function getPainImprovementAttribute()
    {
        if (!$this->pain_level_before || !$this->pain_level_after) {
            return null;
        }
        
        return $this->pain_level_before - $this->pain_level_after;
    }

    public function getPainImprovementDisplayAttribute()
    {
        $improvement = $this->pain_improvement;
        
        if ($improvement === null) return 'غير محدد';
        if ($improvement > 0) return 'تحسن بمقدار ' . $improvement . ' درجات';
        if ($improvement < 0) return 'ازداد بمقدار ' . abs($improvement) . ' درجات';
        
        return 'لا تغيير';
    }

    public function getIsOverdueAttribute()
    {
        return $this->scheduled_date && 
               $this->scheduled_date->isPast() && 
               $this->status === 'scheduled';
    }

    public function getIsUpcomingAttribute()
    {
        return $this->scheduled_date && 
               $this->scheduled_date->isFuture() && 
               $this->status === 'scheduled';
    }

    public function getIsTodayAttribute()
    {
        return $this->scheduled_date && 
               $this->scheduled_date->isToday() && 
               $this->status === 'scheduled';
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->session_cost == 0) return 'مجاني';
        if ($this->session_payment >= $this->session_cost) return 'مدفوع';
        if ($this->session_payment > 0) return 'مدفوع جزئياً';
        return 'غير مدفوع';
    }

    public function getPaymentStatusColorAttribute()
    {
        return match($this->payment_status) {
            'مدفوع' => 'success',
            'مدفوع جزئياً' => 'warning',
            'غير مدفوع' => 'danger',
            'مجاني' => 'info',
            default => 'secondary'
        };
    }

    // Helper methods
    public function markAsCompleted($data = [])
    {
        $updateData = array_merge([
            'status' => 'completed',
            'completed_date' => now()->toDateString()
        ], $data);

        $this->update($updateData);
        
        // Update treatment progress
        $this->dentalTreatment->updateProgress();
        
        return $this;
    }

    public function canBeCompleted()
    {
        return in_array($this->status, ['scheduled', 'in_progress']);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['scheduled']);
    }

    public function canBeRescheduled()
    {
        return in_array($this->status, ['scheduled']);
    }

    public function reschedule($newDate)
    {
        if (!$this->canBeRescheduled()) {
            return false;
        }

        $this->update([
            'scheduled_date' => $newDate,
            'status' => 'scheduled'
        ]);

        return true;
    }

    public function addComplication($complication)
    {
        $complications = $this->complications ?? [];
        $complications[] = [
            'description' => $complication,
            'timestamp' => now()->toISOString(),
            'severity' => 'normal'
        ];
        
        $this->update(['complications' => $complications]);
        
        return $this;
    }

    public function addPhoto($photoPath, $description = null)
    {
        $photos = $this->session_photos ?? [];
        $photos[] = [
            'path' => $photoPath,
            'description' => $description,
            'timestamp' => now()->toISOString()
        ];
        
        $this->update(['session_photos' => $photos]);
        
        return $this;
    }

    // Static methods
    public static function generateSessionNumber($treatmentNumber, $sessionOrder)
    {
        return $treatmentNumber . '-S' . str_pad($sessionOrder, 2, '0', STR_PAD_LEFT);
    }

    public static function getStatuses()
    {
        return [
            'scheduled' => 'مجدولة',
            'in_progress' => 'جارية',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغية',
            'no_show' => 'لم يحضر'
        ];
    }

    public static function getCommonProcedures()
    {
        return [
            'cleaning' => 'تنظيف الأسنان',
            'filling' => 'حشو الأسنان',
            'extraction' => 'خلع الأسنان',
            'root_canal' => 'علاج الجذور',
            'crown_prep' => 'تحضير التاج',
            'crown_placement' => 'تركيب التاج',
            'implant_placement' => 'زراعة الأسنان',
            'orthodontic_adjustment' => 'تعديل التقويم',
            'scaling' => 'تنظيف الجير',
            'whitening' => 'تبييض الأسنان'
        ];
    }

    public static function getCommonMaterials()
    {
        return [
            'composite' => 'حشو كومبوزيت',
            'amalgam' => 'حشو أملغم',
            'ceramic' => 'سيراميك',
            'titanium' => 'تيتانيوم',
            'gold' => 'ذهب',
            'porcelain' => 'بورسلين',
            'resin' => 'راتنج',
            'glass_ionomer' => 'أيونومر زجاجي'
        ];
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->session_number)) {
                $treatment = DentalTreatment::find($session->dental_treatment_id);
                if ($treatment) {
                    $session->session_number = self::generateSessionNumber(
                        $treatment->treatment_number, 
                        $session->session_order
                    );
                }
            }
        });

        static::updated(function ($session) {
            // Update treatment progress when session status changes
            if ($session->wasChanged('status')) {
                $session->dentalTreatment->updateProgress();
            }
        });
    }
}