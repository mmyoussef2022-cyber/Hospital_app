<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurgicalProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'category',
        'specialty',
        'complexity',
        'urgency_level',
        'estimated_duration',
        'min_duration',
        'max_duration',
        'base_cost',
        'surgeon_fee',
        'anesthesia_fee',
        'facility_fee',
        'required_equipment',
        'required_team_roles',
        'pre_operative_requirements',
        'post_operative_care',
        'requires_icu',
        'requires_blood_bank',
        'requires_anesthesia',
        'is_outpatient',
        'is_active',
        'contraindications',
        'complications',
        'recovery_notes'
    ];

    protected $casts = [
        'required_equipment' => 'array',
        'required_team_roles' => 'array',
        'pre_operative_requirements' => 'array',
        'post_operative_care' => 'array',
        'base_cost' => 'decimal:2',
        'surgeon_fee' => 'decimal:2',
        'anesthesia_fee' => 'decimal:2',
        'facility_fee' => 'decimal:2',
        'requires_icu' => 'boolean',
        'requires_blood_bank' => 'boolean',
        'requires_anesthesia' => 'boolean',
        'is_outpatient' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function surgeries(): HasMany
    {
        return $this->hasMany(Surgery::class);
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

    public function scopeBySpecialty($query, $specialty)
    {
        return $query->where('specialty', $specialty);
    }

    public function scopeByComplexity($query, $complexity)
    {
        return $query->where('complexity', $complexity);
    }

    public function scopeByUrgencyLevel($query, $urgencyLevel)
    {
        return $query->where('urgency_level', $urgencyLevel);
    }

    public function scopeOutpatient($query)
    {
        return $query->where('is_outpatient', true);
    }

    public function scopeInpatient($query)
    {
        return $query->where('is_outpatient', false);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }

    public function getDisplayDescriptionAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' && $this->description_ar ? $this->description_ar : $this->description;
    }

    public function getComplexityDisplayAttribute(): string
    {
        return match($this->complexity) {
            'minor' => 'بسيطة',
            'moderate' => 'متوسطة',
            'major' => 'كبيرة',
            'complex' => 'معقدة',
            default => $this->complexity
        };
    }

    public function getUrgencyLevelDisplayAttribute(): string
    {
        return match($this->urgency_level) {
            'elective' => 'اختيارية',
            'urgent' => 'عاجلة',
            'emergency' => 'طارئة',
            default => $this->urgency_level
        };
    }

    public function getComplexityColorAttribute(): string
    {
        return match($this->complexity) {
            'minor' => 'success',
            'moderate' => 'info',
            'major' => 'warning',
            'complex' => 'danger',
            default => 'secondary'
        };
    }

    public function getTotalCostAttribute(): float
    {
        return $this->base_cost + $this->surgeon_fee + $this->anesthesia_fee + $this->facility_fee;
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours = intval($this->estimated_duration / 60);
        $minutes = $this->estimated_duration % 60;
        
        if ($hours > 0) {
            return $hours . ' ساعة' . ($minutes > 0 ? ' و ' . $minutes . ' دقيقة' : '');
        }
        
        return $minutes . ' دقيقة';
    }

    // Business Logic Methods
    public function canBePerformedBy(User $surgeon): bool
    {
        // Check if surgeon has the required specialty
        $doctor = $surgeon->doctor;
        if (!$doctor) {
            return false;
        }

        return $doctor->specialization === $this->specialty || 
               in_array($this->specialty, $doctor->sub_specializations ?? []);
    }

    public function getRequiredTeamRoles(): array
    {
        $defaultRoles = ['primary_surgeon'];
        
        if ($this->requires_anesthesia) {
            $defaultRoles[] = 'anesthesiologist';
        }
        
        // Add scrub nurse for all procedures
        $defaultRoles[] = 'scrub_nurse';
        
        // Add circulating nurse for complex procedures
        if (in_array($this->complexity, ['major', 'complex'])) {
            $defaultRoles[] = 'circulating_nurse';
        }
        
        // Merge with procedure-specific requirements
        return array_unique(array_merge($defaultRoles, $this->required_team_roles ?? []));
    }

    public function getEstimatedTimeSlot(): array
    {
        // Add buffer time based on complexity
        $bufferTime = match($this->complexity) {
            'minor' => 15,
            'moderate' => 30,
            'major' => 45,
            'complex' => 60,
            default => 30
        };

        return [
            'setup_time' => 30, // Standard setup time
            'procedure_time' => $this->estimated_duration,
            'cleanup_time' => 30, // Standard cleanup time
            'buffer_time' => $bufferTime,
            'total_time' => 60 + $this->estimated_duration + $bufferTime
        ];
    }

    public function validatePreOperativeRequirements(Patient $patient): array
    {
        $missing = [];
        $requirements = $this->pre_operative_requirements ?? [];
        
        foreach ($requirements as $requirement) {
            // This would check against patient's medical records
            // For now, we'll return the requirement as potentially missing
            $missing[] = $requirement;
        }
        
        return $missing;
    }

    // Static Methods
    public static function getByCategory(): array
    {
        return static::active()
                    ->select('category')
                    ->distinct()
                    ->orderBy('category')
                    ->pluck('category')
                    ->toArray();
    }

    public static function getBySpecialty(): array
    {
        return static::active()
                    ->select('specialty')
                    ->distinct()
                    ->orderBy('specialty')
                    ->pluck('specialty')
                    ->toArray();
    }

    public static function getMostPerformed(int $limit = 10)
    {
        return static::withCount('surgeries')
                    ->active()
                    ->orderBy('surgeries_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    public static function getEmergencyProcedures()
    {
        return static::where('urgency_level', 'emergency')
                    ->active()
                    ->orderBy('name')
                    ->get();
    }
}
