<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Radiology extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'medical_record_id',
        'study_type',
        'priority',
        'clinical_indication',
        'contrast_required',
        'preparation_instructions',
        'scheduled_date',
        'order_date',
        'completed_at',
        'status',
        'report',
        'is_critical',
        'reviewed_at',
        'reviewed_by',
        'review_notes'
    ];

    protected $casts = [
        'contrast_required' => 'boolean',
        'is_critical' => 'boolean',
        'scheduled_date' => 'datetime',
        'order_date' => 'datetime',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime'
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'reviewed_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'ordered');
    }

    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    public function scopeUnreviewed($query)
    {
        return $query->whereNull('reviewed_at');
    }
}