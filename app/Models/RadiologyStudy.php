<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadiologyStudy extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'name_en',
        'description',
        'category',
        'body_part',
        'price',
        'duration_minutes',
        'preparation_instructions',
        'contrast_instructions',
        'requires_contrast',
        'requires_fasting',
        'is_urgent_capable',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'requires_contrast' => 'boolean',
        'requires_fasting' => 'boolean',
        'is_urgent_capable' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function orders(): HasMany
    {
        return $this->hasMany(RadiologyOrder::class);
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

    public function scopeByBodyPart($query, $bodyPart)
    {
        return $query->where('body_part', $bodyPart);
    }

    public function scopeUrgentCapable($query)
    {
        return $query->where('is_urgent_capable', true);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name : ($this->name_en ?? $this->name);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' ريال';
    }

    public function getCategoryDisplayAttribute(): string
    {
        return match($this->category) {
            'x-ray' => 'أشعة سينية',
            'ct' => 'أشعة مقطعية',
            'mri' => 'رنين مغناطيسي',
            'ultrasound' => 'موجات فوق صوتية',
            'mammography' => 'تصوير الثدي',
            'fluoroscopy' => 'تنظير بالأشعة',
            'nuclear' => 'طب نووي',
            'pet' => 'مسح بالإشعاع الموضعي',
            default => $this->category
        };
    }

    public function getBodyPartDisplayAttribute(): string
    {
        return match($this->body_part) {
            'head' => 'الرأس',
            'neck' => 'الرقبة',
            'chest' => 'الصدر',
            'abdomen' => 'البطن',
            'pelvis' => 'الحوض',
            'spine' => 'العمود الفقري',
            'upper_extremity' => 'الطرف العلوي',
            'lower_extremity' => 'الطرف السفلي',
            'whole_body' => 'الجسم كاملاً',
            default => $this->body_part
        };
    }

    // Helper methods
    public function canBePerformedUrgently(): bool
    {
        return $this->is_urgent_capable;
    }

    public function requiresPreparation(): bool
    {
        return !empty($this->preparation_instructions) || $this->requires_fasting || $this->requires_contrast;
    }

    public function getPreparationSummary(): array
    {
        $preparation = [];

        if ($this->requires_fasting) {
            $preparation[] = 'صيام مطلوب';
        }

        if ($this->requires_contrast) {
            $preparation[] = 'صبغة مطلوبة';
        }

        if ($this->preparation_instructions) {
            $preparation[] = 'تعليمات خاصة';
        }

        return $preparation;
    }

    // Static methods
    public static function getCategories(): array
    {
        return [
            'x-ray' => 'أشعة سينية',
            'ct' => 'أشعة مقطعية',
            'mri' => 'رنين مغناطيسي',
            'ultrasound' => 'موجات فوق صوتية',
            'mammography' => 'تصوير الثدي',
            'fluoroscopy' => 'تنظير بالأشعة',
            'nuclear' => 'طب نووي',
            'pet' => 'مسح بالإشعاع الموضعي'
        ];
    }

    public static function getBodyParts(): array
    {
        return [
            'head' => 'الرأس',
            'neck' => 'الرقبة',
            'chest' => 'الصدر',
            'abdomen' => 'البطن',
            'pelvis' => 'الحوض',
            'spine' => 'العمود الفقري',
            'upper_extremity' => 'الطرف العلوي',
            'lower_extremity' => 'الطرف السفلي',
            'whole_body' => 'الجسم كاملاً'
        ];
    }
}
