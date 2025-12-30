<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StaffProductivity extends Model
{
    use HasFactory;

    protected $table = 'staff_productivity';

    protected $fillable = [
        'user_id',
        'shift_id',
        'department_id',
        'productivity_date',
        'shift_start',
        'shift_end',
        'total_working_minutes',
        'break_minutes',
        'productive_minutes',
        'appointments_handled',
        'patients_registered',
        'patients_checked_in',
        'services_provided',
        'prescriptions_issued',
        'lab_orders_processed',
        'radiology_orders_processed',
        'invoices_generated',
        'payments_processed',
        'revenue_generated',
        'collections_made',
        'phone_calls_handled',
        'emails_processed',
        'documents_processed',
        'efficiency_score',
        'quality_score',
        'customer_satisfaction_score',
        'errors_made',
        'corrections_needed',
        'overtime_minutes',
        'achievements',
        'challenges_faced',
        'improvement_suggestions',
        'supervisor_notes',
        'hourly_breakdown',
        'task_breakdown',
        'performance_metrics',
        'performance_rating',
        'evaluated_by',
        'evaluated_at',
        'audit_trail'
    ];

    protected $casts = [
        'productivity_date' => 'date',
        'shift_start' => 'datetime:H:i',
        'shift_end' => 'datetime:H:i',
        'revenue_generated' => 'decimal:2',
        'collections_made' => 'decimal:2',
        'efficiency_score' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'customer_satisfaction_score' => 'decimal:2',
        'hourly_breakdown' => 'array',
        'task_breakdown' => 'array',
        'performance_metrics' => 'array',
        'evaluated_at' => 'datetime',
        'audit_trail' => 'array'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('productivity_date', [$startDate, $endDate]);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('performance_rating', $rating);
    }

    // Accessors
    public function getPerformanceRatingDisplayAttribute(): string
    {
        $ratings = [
            'excellent' => 'ممتاز',
            'good' => 'جيد',
            'satisfactory' => 'مرضي',
            'needs_improvement' => 'يحتاج تحسين',
            'unsatisfactory' => 'غير مرضي'
        ];

        return $ratings[$this->performance_rating] ?? $this->performance_rating;
    }

    public function getProductivityPercentageAttribute(): float
    {
        return $this->total_working_minutes > 0 ? 
               ($this->productive_minutes / $this->total_working_minutes) * 100 : 0;
    }

    public function getRevenuePerHourAttribute(): float
    {
        $hours = $this->total_working_minutes / 60;
        return $hours > 0 ? $this->revenue_generated / $hours : 0;
    }

    public function getTasksPerHourAttribute(): float
    {
        $hours = $this->total_working_minutes / 60;
        $totalTasks = $this->appointments_handled + $this->patients_registered + 
                     $this->services_provided + $this->payments_processed;
        return $hours > 0 ? $totalTasks / $hours : 0;
    }

    public function getOverallScoreAttribute(): float
    {
        $scores = array_filter([
            $this->efficiency_score,
            $this->quality_score,
            $this->customer_satisfaction_score
        ]);
        
        return count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    }

    // Business Logic Methods
    public function calculateFromShift(Shift $shift): void
    {
        // Basic shift information
        $this->shift_start = $shift->scheduled_start;
        $this->shift_end = $shift->scheduled_end;
        $this->total_working_minutes = $shift->actual_duration ?? $shift->scheduled_duration;
        $this->overtime_minutes = $shift->overtime_minutes;
        
        // Revenue metrics
        $this->revenue_generated = $shift->total_revenue;
        $this->collections_made = $shift->total_collections;
        $this->payments_processed = $shift->total_transactions;
        $this->patients_checked_in = $shift->patients_served;
        
        // Calculate productivity metrics
        $this->calculateProductivityMetrics();
        
        $this->save();
    }

    public function evaluate(
        float $efficiencyScore,
        float $qualityScore,
        float $satisfactionScore,
        string $rating,
        User $evaluator = null,
        string $notes = null
    ): void {
        $this->efficiency_score = $efficiencyScore;
        $this->quality_score = $qualityScore;
        $this->customer_satisfaction_score = $satisfactionScore;
        $this->performance_rating = $rating;
        $this->evaluated_by = $evaluator ? $evaluator->id : auth()->id();
        $this->evaluated_at = now();
        
        if ($notes) {
            $this->supervisor_notes = $notes;
        }

        $this->addToAuditTrail('evaluated', [
            'evaluated_by' => $this->evaluated_by,
            'efficiency_score' => $efficiencyScore,
            'quality_score' => $qualityScore,
            'satisfaction_score' => $satisfactionScore,
            'rating' => $rating
        ]);

        $this->save();
    }

    private function calculateProductivityMetrics(): void
    {
        // Calculate efficiency score based on tasks completed vs time
        $expectedTasksPerHour = 8; // Configurable benchmark
        $actualTasksPerHour = $this->tasks_per_hour;
        $this->efficiency_score = min(100, ($actualTasksPerHour / $expectedTasksPerHour) * 100);
        
        // Calculate break time (assume 10% of shift is break time)
        $this->break_minutes = $this->total_working_minutes * 0.1;
        $this->productive_minutes = $this->total_working_minutes - $this->break_minutes;
    }

    public function addToAuditTrail(string $action, array $details = []): void
    {
        $trail = $this->audit_trail ?? [];
        $trail[] = [
            'action' => $action,
            'details' => $details,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ];
        $this->audit_trail = $trail;
    }

    // Static Methods
    public static function getUserProductivitySummary($userId, $startDate, $endDate): array
    {
        $records = static::where('user_id', $userId)
                        ->whereBetween('productivity_date', [$startDate, $endDate])
                        ->get();

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_shifts' => $records->count(),
            'total_working_hours' => $records->sum('total_working_minutes') / 60,
            'total_revenue' => $records->sum('revenue_generated'),
            'total_patients_served' => $records->sum('patients_checked_in'),
            'average_efficiency' => $records->avg('efficiency_score'),
            'average_quality' => $records->avg('quality_score'),
            'average_satisfaction' => $records->avg('customer_satisfaction_score'),
            'performance_distribution' => $records->groupBy('performance_rating')->map->count()
        ];
    }
}