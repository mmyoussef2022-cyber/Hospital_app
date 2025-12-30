<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_number',
        'national_id',
        'license_number',
        'specialization',
        'sub_specializations',
        'degree',
        'university',
        'experience_years',
        'languages',
        'biography',
        'working_hours',
        'consultation_fee',
        'follow_up_fee',
        'room_number',
        'phone',
        'email',
        'social_media',
        'profile_photo',
        'is_available',
        'is_active',
        'rating',
        'total_reviews'
    ];

    protected $casts = [
        'sub_specializations' => 'array',
        'languages' => 'array',
        'working_hours' => 'array',
        'social_media' => 'array',
        'consultation_fee' => 'decimal:2',
        'follow_up_fee' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_available' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(DoctorCertificate::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(DoctorService::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PatientReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(PatientReview::class)->approved();
    }

    public function featuredReviews(): HasMany
    {
        return $this->hasMany(PatientReview::class)->featured();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    // Financial Relationships
    public function financialAccount(): HasOne
    {
        return $this->hasOne(DoctorFinancialAccount::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(DoctorTransaction::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(DoctorCommission::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeBySpecialization($query, $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    public function scopeHighRated($query, $minRating = 4.0)
    {
        return $query->where('rating', '>=', $minRating);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return 'د. ' . $this->user->name;
    }

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return Storage::url($this->profile_photo);
        }
        return asset('images/default-doctor.png');
    }

    public function getExperienceDisplayAttribute()
    {
        return $this->experience_years . ' ' . ($this->experience_years == 1 ? 'سنة' : 'سنوات') . ' خبرة';
    }

    public function getRatingDisplayAttribute()
    {
        return number_format($this->rating, 1) . ' ★';
    }

    public function getLanguagesDisplayAttribute()
    {
        if (!$this->languages) return 'غير محدد';
        return implode(', ', $this->languages);
    }

    public function getAverageRatingAttribute()
    {
        return $this->rating ?? 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->total_reviews ?? 0;
    }

    public function getPhotoAttribute()
    {
        return $this->profile_photo;
    }

    public function getWorkingHoursDisplayAttribute()
    {
        if (!$this->working_hours) return 'غير محدد';
        
        $display = [];
        foreach ($this->working_hours as $day => $hours) {
            if ($hours['is_working']) {
                $dayName = $this->getDayName($day);
                $display[] = $dayName . ': ' . $hours['start'] . ' - ' . $hours['end'];
            }
        }
        
        return implode('<br>', $display);
    }

    // Helper methods
    public function isWorkingToday()
    {
        $today = strtolower(now()->format('l'));
        return isset($this->working_hours[$today]) && $this->working_hours[$today]['is_working'];
    }

    public function getTodayWorkingHours()
    {
        $today = strtolower(now()->format('l'));
        if ($this->isWorkingToday()) {
            return $this->working_hours[$today];
        }
        return null;
    }

    public function hasValidCertificates()
    {
        return $this->certificates()->where('is_verified', true)->exists();
    }

    public function getActiveServices()
    {
        return $this->services()->where('is_active', true)->orderBy('sort_order')->get();
    }

    public function getTodayAppointments()
    {
        return $this->appointments()->whereDate('appointment_date', today())->orderBy('appointment_time')->get();
    }

    public function getUpcomingAppointments($limit = 5)
    {
        return $this->appointments()
            ->where('appointment_date', '>=', today())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit($limit)
            ->get();
    }

    public function updateAverageRating()
    {
        $averageRating = $this->approvedReviews()->avg('rating') ?? 0;
        $totalReviews = $this->approvedReviews()->count();
        
        $this->update([
            'rating' => round($averageRating, 2),
            'total_reviews' => $totalReviews
        ]);
        
        return $this;
    }

    public function getRatingDistribution()
    {
        return PatientReview::getRatingDistributionForDoctor($this->id);
    }

    public function getAspectRatings()
    {
        return PatientReview::getAspectRatingsForDoctor($this->id);
    }

    public function getRecentReviews($limit = 5)
    {
        return PatientReview::getRecentReviewsForDoctor($this->id, $limit);
    }

    private function getDayName($day)
    {
        $days = [
            'sunday' => 'الأحد',
            'monday' => 'الاثنين',
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
            'friday' => 'الجمعة',
            'saturday' => 'السبت'
        ];
        
        return $days[$day] ?? $day;
    }

    // Static methods
    public static function generateDoctorNumber()
    {
        $lastDoctor = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastDoctor ? (int)substr($lastDoctor->doctor_number, 2) + 1 : 1;
        return 'DR' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public static function getSpecializations()
    {
        return [
            'internal_medicine' => 'الطب الباطني',
            'cardiology' => 'أمراض القلب',
            'neurology' => 'الأعصاب',
            'orthopedics' => 'العظام',
            'pediatrics' => 'الأطفال',
            'gynecology' => 'النساء والتوليد',
            'dermatology' => 'الجلدية',
            'ophthalmology' => 'العيون',
            'ent' => 'الأنف والأذن والحنجرة',
            'psychiatry' => 'الطب النفسي',
            'surgery' => 'الجراحة العامة',
            'urology' => 'المسالك البولية',
            'radiology' => 'الأشعة',
            'anesthesia' => 'التخدير',
            'emergency' => 'الطوارئ',
            'family_medicine' => 'طب الأسرة'
        ];
    }

    public static function getDegrees()
    {
        return [
            'bachelor' => 'بكالوريوس',
            'master' => 'ماجستير',
            'phd' => 'دكتوراه',
            'fellowship' => 'زمالة',
            'board' => 'بورد'
        ];
    }

    // Financial Methods
    public function getTotalEarningsAttribute()
    {
        return $this->transactions()
            ->where('type', 'earning')
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function getPendingWithdrawalsAttribute()
    {
        return $this->transactions()
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->sum('amount');
    }

    public function getAvailableBalanceAttribute()
    {
        return $this->financialAccount ? $this->financialAccount->available_balance : 0;
    }

    public function getTotalCommissionsAttribute()
    {
        return $this->commissions()
            ->where('status', 'active')
            ->sum('commission_amount');
    }

    public function getMonthlyEarningsAttribute()
    {
        return $this->transactions()
            ->where('type', 'earning')
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
    }

    public function createFinancialAccount()
    {
        if (!$this->financialAccount) {
            return $this->financialAccount()->create([
                'account_number' => 'FA' . str_pad($this->id, 8, '0', STR_PAD_LEFT),
                'balance' => 0,
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_earnings' => 0,
                'total_withdrawals' => 0,
                'commission_rate' => 15.00, // Default 15%
                'status' => 'active'
            ]);
        }
        return $this->financialAccount;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($doctor) {
            if (empty($doctor->doctor_number)) {
                $doctor->doctor_number = self::generateDoctorNumber();
            }
        });
    }
}
