<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MedicalRecordAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_id',
        'uploaded_by',
        'file_name',
        'file_name_ar',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'description',
        'description_ar',
        'category',
        'is_confidential',
        'metadata'
    ];

    protected $casts = [
        'is_confidential' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the medical record that owns the attachment
     */
    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Get the user who uploaded the attachment
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get localized file name
     */
    public function getFileNameLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->file_name_ar 
            ? $this->file_name_ar 
            : $this->file_name;
    }

    /**
     * Get localized description
     */
    public function getDescriptionLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->description_ar 
            ? $this->description_ar 
            : $this->description;
    }

    /**
     * Get localized category name
     */
    public function getCategoryLocalizedAttribute()
    {
        $categories = [
            'lab_result' => [
                'en' => 'Lab Result',
                'ar' => 'نتيجة مختبر'
            ],
            'xray' => [
                'en' => 'X-Ray',
                'ar' => 'أشعة سينية'
            ],
            'scan' => [
                'en' => 'Scan',
                'ar' => 'مسح ضوئي'
            ],
            'report' => [
                'en' => 'Report',
                'ar' => 'تقرير'
            ],
            'image' => [
                'en' => 'Image',
                'ar' => 'صورة'
            ],
            'document' => [
                'en' => 'Document',
                'ar' => 'مستند'
            ],
            'other' => [
                'en' => 'Other',
                'ar' => 'أخرى'
            ]
        ];

        $locale = app()->getLocale();
        return $categories[$this->category][$locale] ?? $this->category;
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get full file URL
     */
    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Check if file is an image
     */
    public function getIsImageAttribute()
    {
        return in_array($this->file_type, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
    }

    /**
     * Check if file is a PDF
     */
    public function getIsPdfAttribute()
    {
        return $this->file_type === 'pdf';
    }

    /**
     * Scope for specific category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for confidential files
     */
    public function scopeConfidential($query)
    {
        return $query->where('is_confidential', true);
    }

    /**
     * Scope for public files
     */
    public function scopePublic($query)
    {
        return $query->where('is_confidential', false);
    }
}
