<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DoctorCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'title',
        'type',
        'institution',
        'country',
        'issue_date',
        'expiry_date',
        'certificate_number',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'is_verified',
        'verified_at',
        'verified_by'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime'
    ];

    // Relationships
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return Storage::url($this->file_path);
        }
        return null;
    }

    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) return 'غير محدد';
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getTypeDisplayAttribute()
    {
        $types = [
            'degree' => 'شهادة جامعية',
            'certificate' => 'شهادة تدريب',
            'course' => 'دورة تدريبية',
            'license' => 'ترخيص مهني',
            'fellowship' => 'زمالة',
            'board' => 'بورد',
            'other' => 'أخرى'
        ];
        
        return $types[$this->type] ?? $this->type;
    }

    public function getStatusDisplayAttribute()
    {
        if ($this->is_verified) {
            return '<span class="badge bg-success">مُتحقق منها</span>';
        } else {
            return '<span class="badge bg-warning">في انتظار التحقق</span>';
        }
    }

    public function getExpiryStatusAttribute()
    {
        if (!$this->expiry_date) {
            return 'لا تنتهي';
        }
        
        $now = now();
        $expiry = $this->expiry_date;
        
        if ($expiry->isPast()) {
            return '<span class="badge bg-danger">منتهية الصلاحية</span>';
        } elseif ($expiry->diffInDays($now) <= 30) {
            return '<span class="badge bg-warning">تنتهي قريباً</span>';
        } else {
            return '<span class="badge bg-success">سارية</span>';
        }
    }

    // Helper methods
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function canDownload()
    {
        return $this->file_path && Storage::exists($this->file_path);
    }

    public function verify($userId = null)
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $userId ?? auth()->id()
        ]);
    }

    public function unverify()
    {
        $this->update([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null
        ]);
    }

    // Static methods
    public static function getTypes()
    {
        return [
            'degree' => 'شهادة جامعية',
            'certificate' => 'شهادة تدريب',
            'course' => 'دورة تدريبية',
            'license' => 'ترخيص مهني',
            'fellowship' => 'زمالة',
            'board' => 'بورد',
            'other' => 'أخرى'
        ];
    }
}
