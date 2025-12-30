<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'patient_id',
        'doctor_id',
        'medication_name',
        'medication_name_ar',
        'dosage',
        'frequency',
        'frequency_ar',
        'duration_days',
        'instructions',
        'instructions_ar',
        'warnings',
        'warnings_ar',
        'status',
        'start_date',
        'end_date',
        'is_controlled_substance',
        'pharmacy_notes',
        'pharmacy_notes_ar'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_controlled_substance' => 'boolean',
    ];

    /**
     * Get the medical record that owns the prescription
     */
    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Get the patient that owns the prescription
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor that prescribed the medication
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get localized medication name
     */
    public function getMedicationNameLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->medication_name_ar 
            ? $this->medication_name_ar 
            : $this->medication_name;
    }

    /**
     * Get localized frequency
     */
    public function getFrequencyLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->frequency_ar 
            ? $this->frequency_ar 
            : $this->frequency;
    }

    /**
     * Get localized instructions
     */
    public function getInstructionsLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->instructions_ar 
            ? $this->instructions_ar 
            : $this->instructions;
    }

    /**
     * Get localized warnings
     */
    public function getWarningsLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->warnings_ar 
            ? $this->warnings_ar 
            : $this->warnings;
    }

    /**
     * Get localized pharmacy notes
     */
    public function getPharmacyNotesLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->pharmacy_notes_ar 
            ? $this->pharmacy_notes_ar 
            : $this->pharmacy_notes;
    }

    /**
     * Scope for active prescriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('end_date', '>=', now()->toDateString());
    }

    /**
     * Scope for expired prescriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now()->toDateString())
                    ->where('status', '!=', 'completed');
    }

    /**
     * Scope for controlled substances
     */
    public function scopeControlledSubstances($query)
    {
        return $query->where('is_controlled_substance', true);
    }

    /**
     * Check if prescription is expired
     */
    public function getIsExpiredAttribute()
    {
        return $this->end_date < now()->toDateString() && $this->status !== 'completed';
    }

    /**
     * Get remaining days
     */
    public function getRemainingDaysAttribute()
    {
        return max(0, now()->diffInDays($this->end_date, false));
    }
}
