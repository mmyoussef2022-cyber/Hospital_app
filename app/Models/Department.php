<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'description_ar',
        'description_en',
        'location',
        'phone',
        'extension',
        'is_active',
        'capacity',
        'working_hours'
    ];

    protected $casts = [
        'working_hours' => 'array',
        'is_active' => 'boolean'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }

    /**
     * Scope للحصول على الأقسام النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
