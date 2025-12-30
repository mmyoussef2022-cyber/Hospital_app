<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PatientReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'rating',
        'review_text',
        'rating_aspects',
        'is_anonymous',
        'is_approved',
        'is_featured',
        'approved_at',
        'approved_by',
        'admin_notes',
        'status'
    ];

    protected $casts = [
        'rating_aspects' => 'array',
        'is_anonymous' => 'boolean',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime'
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true)->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->approved();
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    // Accessors
    public function getRatingStarsAttribute()
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $stars .= '<i class="bi bi-star-fill text-warning"></i>';
            } else {
                $stars .= '<i class="bi bi-star text-muted"></i>';
            }
        }
        return $stars;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">في انتظار المراجعة</span>',
            'approved' => '<span class="badge bg-success">معتمد</span>',
            'rejected' => '<span class="badge bg-danger">مرفوض</span>',
            'hidden' => '<span class="badge bg-secondary">مخفي</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-light">غير محدد</span>';
    }

    public function getPatientNameAttribute()
    {
        if ($this->is_anonymous) {
            return 'مريض مجهول';
        }
        
        return $this->patient->user->name ?? 'غير محدد';
    }

    public function getReviewSummaryAttribute()
    {
        if (!$this->review_text) {
            return 'لا يوجد تعليق';
        }
        
        return \Str::limit($this->review_text, 100);
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getRatingAspectsDisplayAttribute()
    {
        if (!$this->rating_aspects) {
            return [];
        }

        $aspectNames = [
            'professionalism' => 'الاحترافية',
            'communication' => 'التواصل',
            'punctuality' => 'الالتزام بالمواعيد',
            'cleanliness' => 'النظافة',
            'effectiveness' => 'فعالية العلاج',
            'staff_behavior' => 'تعامل الطاقم',
            'waiting_time' => 'وقت الانتظار',
            'value_for_money' => 'القيمة مقابل المال'
        ];

        $display = [];
        foreach ($this->rating_aspects as $aspect => $rating) {
            $display[$aspectNames[$aspect] ?? $aspect] = $rating;
        }

        return $display;
    }

    // Helper methods
    public function approve($userId = null)
    {
        $this->update([
            'is_approved' => true,
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $userId ?? auth()->id()
        ]);

        // Update doctor's average rating
        $this->doctor->updateAverageRating();
    }

    public function reject($adminNotes = null, $userId = null)
    {
        $this->update([
            'is_approved' => false,
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
            'approved_by' => $userId ?? auth()->id()
        ]);
    }

    public function hide($adminNotes = null, $userId = null)
    {
        $this->update([
            'status' => 'hidden',
            'admin_notes' => $adminNotes,
            'approved_by' => $userId ?? auth()->id()
        ]);

        // Update doctor's average rating
        $this->doctor->updateAverageRating();
    }

    public function feature()
    {
        $this->update(['is_featured' => true]);
    }

    public function unfeature()
    {
        $this->update(['is_featured' => false]);
    }

    public function canBeEditedBy($user)
    {
        // Patients can edit their own reviews within 24 hours if not approved
        if ($user->id === $this->patient->user_id) {
            return !$this->is_approved && $this->created_at->gt(now()->subDay());
        }

        // Admins can always edit
        return $user->can('reviews.edit');
    }

    public function canBeDeletedBy($user)
    {
        // Patients can delete their own reviews within 1 hour if not approved
        if ($user->id === $this->patient->user_id) {
            return !$this->is_approved && $this->created_at->gt(now()->subHour());
        }

        // Admins can always delete
        return $user->can('reviews.delete');
    }

    // Static methods
    public static function getAverageRatingForDoctor($doctorId)
    {
        return self::forDoctor($doctorId)->approved()->avg('rating') ?? 0;
    }

    public static function getReviewCountForDoctor($doctorId)
    {
        return self::forDoctor($doctorId)->approved()->count();
    }

    public static function getRatingDistributionForDoctor($doctorId)
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = self::forDoctor($doctorId)->approved()->byRating($i)->count();
        }
        return $distribution;
    }

    public static function getAspectRatingsForDoctor($doctorId)
    {
        $reviews = self::forDoctor($doctorId)->approved()
            ->whereNotNull('rating_aspects')
            ->get();

        $aspects = [];
        foreach ($reviews as $review) {
            if ($review->rating_aspects) {
                foreach ($review->rating_aspects as $aspect => $rating) {
                    if (!isset($aspects[$aspect])) {
                        $aspects[$aspect] = [];
                    }
                    $aspects[$aspect][] = $rating;
                }
            }
        }

        // Calculate averages
        $averages = [];
        foreach ($aspects as $aspect => $ratings) {
            $averages[$aspect] = array_sum($ratings) / count($ratings);
        }

        return $averages;
    }

    public static function getRecentReviewsForDoctor($doctorId, $limit = 5)
    {
        return self::forDoctor($doctorId)->approved()
            ->with(['patient.user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        // Update doctor rating when review is created, updated, or deleted
        static::created(function ($review) {
            if ($review->is_approved) {
                $review->doctor->updateAverageRating();
            }
        });

        static::updated(function ($review) {
            $review->doctor->updateAverageRating();
        });

        static::deleted(function ($review) {
            $review->doctor->updateAverageRating();
        });
    }
}