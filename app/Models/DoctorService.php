<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorService extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'service_name',
        'service_name_en',
        'description',
        'category',
        'price',
        'duration_minutes',
        'requirements',
        'preparation_instructions',
        'requires_appointment',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'requirements' => 'array',
        'preparation_instructions' => 'array',
        'requires_appointment' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('service_name');
    }

    public function scopeRequiresAppointment($query)
    {
        return $query->where('requires_appointment', true);
    }

    // Accessors
    public function getPriceFormattedAttribute()
    {
        return number_format($this->price, 2) . ' ريال';
    }

    public function getDurationFormattedAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        $duration = '';
        if ($hours > 0) {
            $duration .= $hours . ' ساعة';
            if ($minutes > 0) {
                $duration .= ' و ';
            }
        }
        if ($minutes > 0) {
            $duration .= $minutes . ' دقيقة';
        }
        
        return $duration ?: 'غير محدد';
    }

    public function getCategoryDisplayAttribute()
    {
        $categories = [
            'consultation' => 'استشارة',
            'surgery' => 'جراحة',
            'procedure' => 'إجراء طبي',
            'examination' => 'فحص',
            'treatment' => 'علاج',
            'follow_up' => 'متابعة',
            'emergency' => 'طوارئ',
            'other' => 'أخرى'
        ];
        
        return $categories[$this->category] ?? $this->category;
    }

    public function getRequirementsDisplayAttribute()
    {
        if (!$this->requirements || empty($this->requirements)) {
            return 'لا توجد متطلبات خاصة';
        }
        
        return '<ul><li>' . implode('</li><li>', $this->requirements) . '</li></ul>';
    }

    public function getPreparationInstructionsDisplayAttribute()
    {
        if (!$this->preparation_instructions || empty($this->preparation_instructions)) {
            return 'لا توجد تعليمات خاصة';
        }
        
        return '<ul><li>' . implode('</li><li>', $this->preparation_instructions) . '</li></ul>';
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->is_active) {
            return '<span class="badge bg-success">نشط</span>';
        } else {
            return '<span class="badge bg-secondary">غير نشط</span>';
        }
    }

    public function getAppointmentBadgeAttribute()
    {
        if ($this->requires_appointment) {
            return '<span class="badge bg-info">يحتاج موعد</span>';
        } else {
            return '<span class="badge bg-warning">بدون موعد</span>';
        }
    }

    // Helper methods
    public function canBeBooked()
    {
        return $this->is_active && $this->doctor->is_available && $this->doctor->is_active;
    }

    public function getEstimatedEndTime($startTime)
    {
        return \Carbon\Carbon::parse($startTime)->addMinutes($this->duration_minutes);
    }

    // Static methods
    public static function getCategories()
    {
        return [
            'consultation' => 'استشارة',
            'surgery' => 'جراحة',
            'procedure' => 'إجراء طبي',
            'examination' => 'فحص',
            'treatment' => 'علاج',
            'follow_up' => 'متابعة',
            'emergency' => 'طوارئ',
            'other' => 'أخرى'
        ];
    }

    public static function getNextSortOrder($doctorId)
    {
        $lastService = self::where('doctor_id', $doctorId)->orderBy('sort_order', 'desc')->first();
        return $lastService ? $lastService->sort_order + 1 : 1;
    }
}
