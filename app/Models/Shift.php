<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_number',
        'user_id',
        'department_id',
        'cash_register_id',
        'supervisor_id',
        'shift_type',
        'shift_date',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'status',
        'opening_cash_balance',
        'closing_cash_balance',
        'expected_cash_balance',
        'cash_difference',
        'total_transactions',
        'total_revenue',
        'total_collections',
        'patients_served',
        'shift_notes',
        'handover_notes',
        'cash_verified',
        'cash_verified_at',
        'cash_verified_by',
        'audit_trail'
    ];

    protected $casts = [
        'shift_date' => 'date',
        'scheduled_start' => 'datetime:H:i',
        'scheduled_end' => 'datetime:H:i',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'opening_cash_balance' => 'decimal:2',
        'closing_cash_balance' => 'decimal:2',
        'expected_cash_balance' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'total_collections' => 'decimal:2',
        'cash_verified' => 'boolean',
        'cash_verified_at' => 'datetime',
        'audit_trail' => 'array'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function cashVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cash_verified_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ShiftTransaction::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(ShiftReport::class);
    }

    public function productivity(): HasOne
    {
        return $this->hasOne(StaffProductivity::class);
    }

    public function handoverFrom(): HasMany
    {
        return $this->hasMany(ShiftHandover::class, 'from_shift_id');
    }

    public function handoverTo(): HasMany
    {
        return $this->hasMany(ShiftHandover::class, 'to_shift_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('shift_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('shift_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('shift_date', now()->month)
                    ->whereYear('shift_date', now()->year);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByShiftType($query, $type)
    {
        return $query->where('shift_type', $type);
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'scheduled' => 'مجدولة',
            'active' => 'نشطة',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغية',
            'no_show' => 'غياب'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getShiftTypeDisplayAttribute(): string
    {
        $types = [
            'morning' => 'صباحية',
            'afternoon' => 'بعد الظهر',
            'evening' => 'مسائية',
            'night' => 'ليلية',
            'emergency' => 'طوارئ'
        ];

        return $types[$this->shift_type] ?? $this->shift_type;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'scheduled' => 'primary',
            'active' => 'success',
            'completed' => 'secondary',
            'cancelled' => 'danger',
            'no_show' => 'warning'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getOpeningBalanceAttribute(): float
    {
        return $this->opening_cash_balance ?? 0;
    }

    public function getClosingBalanceAttribute(): float
    {
        return $this->closing_cash_balance ?? 0;
    }

    public function getScheduledDurationAttribute(): int
    {
        if (!$this->scheduled_start || !$this->scheduled_end) {
            return 0;
        }

        $start = Carbon::parse($this->scheduled_start);
        $end = Carbon::parse($this->scheduled_end);
        
        return $start->diffInMinutes($end);
    }

    public function getActualDurationAttribute(): ?int
    {
        if (!$this->actual_start || !$this->actual_end) {
            return null;
        }

        return $this->actual_start->diffInMinutes($this->actual_end);
    }

    public function getCurrentDurationAttribute(): ?int
    {
        if (!$this->actual_start) {
            return null;
        }

        $end = $this->actual_end ?? now();
        return $this->actual_start->diffInMinutes($end);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status !== 'scheduled') {
            return false;
        }

        $scheduledStart = Carbon::parse($this->shift_date->format('Y-m-d') . ' ' . $this->scheduled_start);
        return now()->isAfter($scheduledStart->addMinutes(15)); // 15 minutes grace period
    }

    public function getIsLateAttribute(): bool
    {
        if (!$this->actual_start || $this->status !== 'active' && $this->status !== 'completed') {
            return false;
        }

        $scheduledStart = Carbon::parse($this->shift_date->format('Y-m-d') . ' ' . $this->scheduled_start);
        return $this->actual_start->isAfter($scheduledStart);
    }

    public function getLateMinutesAttribute(): int
    {
        if (!$this->is_late) {
            return 0;
        }

        $scheduledStart = Carbon::parse($this->shift_date->format('Y-m-d') . ' ' . $this->scheduled_start);
        return $scheduledStart->diffInMinutes($this->actual_start);
    }

    public function getOvertimeMinutesAttribute(): int
    {
        if (!$this->actual_end || $this->status !== 'completed') {
            return 0;
        }

        $scheduledEnd = Carbon::parse($this->shift_date->format('Y-m-d') . ' ' . $this->scheduled_end);
        return $this->actual_end->isAfter($scheduledEnd) ? 
               $scheduledEnd->diffInMinutes($this->actual_end) : 0;
    }

    public function getHasCashDiscrepancyAttribute(): bool
    {
        return abs($this->cash_difference) >= 0.01;
    }

    public function getFormattedCashDifferenceAttribute(): string
    {
        $diff = $this->cash_difference;
        $sign = $diff >= 0 ? '+' : '';
        return $sign . number_format($diff, 2) . ' ريال';
    }

    public function getAverageTransactionAmountAttribute(): float
    {
        return $this->total_transactions > 0 ? 
               $this->total_revenue / $this->total_transactions : 0;
    }

    public function getCanStartAttribute(): bool
    {
        return $this->status === 'scheduled' && 
               $this->shift_date->isToday() &&
               now()->isAfter(Carbon::parse($this->shift_date->format('Y-m-d') . ' ' . $this->scheduled_start)->subMinutes(15));
    }

    public function getCanEndAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getNeedsHandoverAttribute(): bool
    {
        return $this->status === 'completed' && 
               $this->handoverFrom()->where('status', '!=', 'completed')->doesntExist();
    }

    // Business Logic Methods
    public function start(float $openingBalance = null, User $user = null): void
    {
        if ($this->status !== 'scheduled') {
            throw new \Exception('يمكن بدء الورديات المجدولة فقط');
        }

        $this->status = 'active';
        $this->actual_start = now();
        
        if ($openingBalance !== null) {
            $this->opening_cash_balance = $openingBalance;
            $this->expected_cash_balance = $openingBalance;
        }

        // Open cash register if assigned
        if ($this->cashRegister && $openingBalance !== null) {
            $this->cashRegister->openRegister($openingBalance, $user);
        }

        $this->addToAuditTrail('started', [
            'started_by' => $user ? $user->id : auth()->id(),
            'opening_balance' => $openingBalance,
            'late_minutes' => $this->late_minutes
        ]);

        $this->save();
    }

    public function end(float $closingBalance = null, string $notes = null, User $user = null): void
    {
        if ($this->status !== 'active') {
            throw new \Exception('يمكن إنهاء الورديات النشطة فقط');
        }

        $this->status = 'completed';
        $this->actual_end = now();
        $this->shift_notes = $notes;
        
        if ($closingBalance !== null) {
            $this->closing_cash_balance = $closingBalance;
            $this->cash_difference = $closingBalance - $this->expected_cash_balance;
        }

        // Close cash register if assigned
        if ($this->cashRegister && $closingBalance !== null) {
            $this->cashRegister->closeRegister($closingBalance, $user);
        }

        // Calculate shift statistics
        $this->calculateShiftStatistics();

        $this->addToAuditTrail('ended', [
            'ended_by' => $user ? $user->id : auth()->id(),
            'closing_balance' => $closingBalance,
            'cash_difference' => $this->cash_difference,
            'overtime_minutes' => $this->overtime_minutes,
            'notes' => $notes
        ]);

        $this->save();

        // Generate shift report
        $this->generateShiftReport();
    }

    public function cancel(string $reason = null, User $user = null): void
    {
        if (!in_array($this->status, ['scheduled', 'active'])) {
            throw new \Exception('لا يمكن إلغاء هذه الوردية');
        }

        $oldStatus = $this->status;
        $this->status = 'cancelled';
        
        if ($oldStatus === 'active') {
            $this->actual_end = now();
        }

        $this->addToAuditTrail('cancelled', [
            'cancelled_by' => $user ? $user->id : auth()->id(),
            'reason' => $reason,
            'previous_status' => $oldStatus
        ]);

        $this->save();
    }

    public function markNoShow(string $reason = null, User $user = null): void
    {
        if ($this->status !== 'scheduled') {
            throw new \Exception('يمكن تسجيل الغياب للورديات المجدولة فقط');
        }

        $this->status = 'no_show';

        $this->addToAuditTrail('no_show', [
            'marked_by' => $user ? $user->id : auth()->id(),
            'reason' => $reason
        ]);

        $this->save();
    }

    public function addTransaction(ShiftTransaction $transaction): void
    {
        $this->total_transactions++;
        
        if ($transaction->transaction_type === 'payment') {
            $this->total_revenue += $transaction->amount;
            $this->total_collections += $transaction->amount;
            $this->expected_cash_balance += $transaction->amount;
        } elseif ($transaction->transaction_type === 'refund') {
            $this->total_revenue -= $transaction->amount;
            $this->expected_cash_balance -= $transaction->amount;
        }

        $this->save();
    }

    public function verifyCash(float $actualBalance, User $user = null): void
    {
        $this->closing_cash_balance = $actualBalance;
        $this->cash_difference = $actualBalance - $this->expected_cash_balance;
        $this->cash_verified = true;
        $this->cash_verified_at = now();
        $this->cash_verified_by = $user ? $user->id : auth()->id();

        $this->addToAuditTrail('cash_verified', [
            'verified_by' => $this->cash_verified_by,
            'actual_balance' => $actualBalance,
            'expected_balance' => $this->expected_cash_balance,
            'difference' => $this->cash_difference
        ]);

        $this->save();
    }

    public function calculateShiftStatistics(): void
    {
        $transactions = $this->transactions()->where('status', 'completed')->get();
        
        $this->total_transactions = $transactions->count();
        $this->total_revenue = $transactions->where('transaction_type', 'payment')->sum('amount');
        $this->total_collections = $this->total_revenue;
        $this->patients_served = $transactions->whereNotNull('patient_id')->unique('patient_id')->count();

        $this->save();
    }

    public function generateShiftReport(): ShiftReport
    {
        $report = $this->report()->firstOrCreate([
            'shift_id' => $this->id
        ], [
            'report_number' => ShiftReport::generateReportNumber(),
            'user_id' => $this->user_id,
            'department_id' => $this->department_id,
            'report_date' => $this->shift_date,
            'shift_start' => $this->scheduled_start,
            'shift_end' => $this->scheduled_end
        ]);

        $report->updateFromShift($this);
        
        return $report;
    }

    public function createHandover(Shift $toShift = null, array $handoverData = []): ShiftHandover
    {
        return ShiftHandover::create(array_merge([
            'handover_number' => ShiftHandover::generateHandoverNumber(),
            'from_shift_id' => $this->id,
            'to_shift_id' => $toShift?->id,
            'from_user_id' => $this->user_id,
            'to_user_id' => $toShift?->user_id,
            'department_id' => $this->department_id,
            'cash_register_id' => $this->cash_register_id,
            'handover_date' => now(),
            'cash_balance_handed_over' => $this->closing_cash_balance,
            'status' => 'pending'
        ], $handoverData));
    }

    public function addToAuditTrail(string $action, array $details = []): void
    {
        $trail = $this->audit_trail ?? [];
        $trail[] = [
            'action' => $action,
            'details' => $details,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'System',
            'timestamp' => now()->toISOString()
        ];
        
        $this->audit_trail = $trail;
    }

    // Static Methods
    public static function generateShiftNumber(): string
    {
        $prefix = 'SHF';
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        $lastShift = static::whereDate('created_at', today())
                          ->orderBy('id', 'desc')
                          ->first();
        
        $sequence = $lastShift ? (int)substr($lastShift->shift_number, -3) + 1 : 1;
        
        return sprintf('%s-%s%s%s-%03d', $prefix, $year, $month, $day, $sequence);
    }

    public static function createScheduledShift(array $data): self
    {
        return static::create(array_merge([
            'shift_number' => static::generateShiftNumber(),
            'status' => 'scheduled'
        ], $data));
    }

    public static function getTodayActiveShifts()
    {
        return static::active()->today()->with(['user', 'department', 'cashRegister'])->get();
    }

    public static function getUpcomingShifts($days = 7)
    {
        return static::scheduled()
                    ->whereBetween('shift_date', [today(), today()->addDays($days)])
                    ->with(['user', 'department'])
                    ->orderBy('shift_date')
                    ->orderBy('scheduled_start')
                    ->get();
    }

    public static function getOverdueShifts()
    {
        return static::scheduled()
                    ->where('shift_date', '<=', today())
                    ->where(function($q) {
                        $q->where('shift_date', '<', today())
                          ->orWhere(function($sq) {
                              $sq->where('shift_date', today())
                                 ->whereRaw('TIME(scheduled_start) < ?', [now()->subMinutes(15)->format('H:i:s')]);
                          });
                    })
                    ->with(['user', 'department'])
                    ->get();
    }

    public static function getDepartmentShiftSummary($departmentId, $date = null): array
    {
        $date = $date ? Carbon::parse($date) : today();
        
        $shifts = static::where('department_id', $departmentId)
                       ->whereDate('shift_date', $date)
                       ->with(['user', 'transactions'])
                       ->get();
        
        return [
            'date' => $date->format('Y-m-d'),
            'total_shifts' => $shifts->count(),
            'active_shifts' => $shifts->where('status', 'active')->count(),
            'completed_shifts' => $shifts->where('status', 'completed')->count(),
            'cancelled_shifts' => $shifts->where('status', 'cancelled')->count(),
            'no_show_shifts' => $shifts->where('status', 'no_show')->count(),
            'total_revenue' => $shifts->sum('total_revenue'),
            'total_transactions' => $shifts->sum('total_transactions'),
            'total_patients_served' => $shifts->sum('patients_served'),
            'shifts_with_discrepancy' => $shifts->where('has_cash_discrepancy', true)->count(),
            'total_cash_difference' => $shifts->sum('cash_difference'),
            'shifts' => $shifts->map(function($shift) {
                return [
                    'id' => $shift->id,
                    'number' => $shift->shift_number,
                    'user' => $shift->user->name,
                    'type' => $shift->shift_type_display,
                    'status' => $shift->status_display,
                    'revenue' => $shift->total_revenue,
                    'transactions' => $shift->total_transactions,
                    'patients' => $shift->patients_served,
                    'cash_difference' => $shift->cash_difference,
                    'duration' => $shift->actual_duration ?? $shift->current_duration
                ];
            })
        ];
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shift) {
            if (!$shift->shift_number) {
                $shift->shift_number = static::generateShiftNumber();
            }
        });

        static::created(function ($shift) {
            $shift->addToAuditTrail('created', [
                'shift_number' => $shift->shift_number,
                'user_id' => $shift->user_id,
                'department_id' => $shift->department_id,
                'shift_type' => $shift->shift_type
            ]);
        });
    }
}