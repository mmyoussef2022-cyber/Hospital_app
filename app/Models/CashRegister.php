<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'register_number',
        'register_name',
        'department_id',
        'opening_balance',
        'current_balance',
        'expected_balance',
        'status',
        'location',
        'last_reconciled_at',
        'last_reconciled_by',
        'reconciliation_difference',
        'reconciliation_notes',
        'audit_trail',
        'is_active'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'reconciliation_difference' => 'decimal:2',
        'last_reconciled_at' => 'datetime',
        'audit_trail' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function lastReconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_reconciled_by');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ShiftTransaction::class);
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(ShiftHandover::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive')->orWhere('is_active', false);
    }

    public function scopeNeedsReconciliation($query)
    {
        return $query->where('status', 'reconciling')
                    ->orWhere('reconciliation_difference', '!=', 0);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'maintenance' => 'صيانة',
            'reconciling' => 'تحت المراجعة'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'active' => 'success',
            'inactive' => 'secondary',
            'maintenance' => 'warning',
            'reconciling' => 'info'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getFormattedCurrentBalanceAttribute(): string
    {
        return number_format($this->current_balance, 2) . ' ريال';
    }

    public function getFormattedExpectedBalanceAttribute(): string
    {
        return number_format($this->expected_balance, 2) . ' ريال';
    }

    public function getFormattedReconciliationDifferenceAttribute(): string
    {
        $diff = $this->reconciliation_difference;
        $sign = $diff >= 0 ? '+' : '';
        return $sign . number_format($diff, 2) . ' ريال';
    }

    public function getIsBalancedAttribute(): bool
    {
        return abs($this->reconciliation_difference) < 0.01; // Within 1 cent
    }

    public function getHasDiscrepancyAttribute(): bool
    {
        return abs($this->reconciliation_difference) >= 0.01;
    }

    public function getDaysLastReconciledAttribute(): ?int
    {
        return $this->last_reconciled_at ? 
               $this->last_reconciled_at->diffInDays(now()) : null;
    }

    public function getNeedsReconciliationAttribute(): bool
    {
        return $this->status === 'reconciling' || 
               $this->has_discrepancy ||
               ($this->days_last_reconciled && $this->days_last_reconciled > 1);
    }

    // Business Logic Methods
    public function openRegister(float $openingBalance, User $user = null): void
    {
        $this->opening_balance = $openingBalance;
        $this->current_balance = $openingBalance;
        $this->expected_balance = $openingBalance;
        $this->status = 'active';
        
        $this->addToAuditTrail('opened', [
            'opening_balance' => $openingBalance,
            'opened_by' => $user ? $user->id : auth()->id(),
            'opened_at' => now()->toISOString()
        ]);
        
        $this->save();
    }

    public function closeRegister(float $closingBalance, User $user = null): void
    {
        $this->reconciliation_difference = $closingBalance - $this->expected_balance;
        $this->current_balance = $closingBalance;
        $this->status = $this->is_balanced ? 'inactive' : 'reconciling';
        
        $this->addToAuditTrail('closed', [
            'closing_balance' => $closingBalance,
            'expected_balance' => $this->expected_balance,
            'difference' => $this->reconciliation_difference,
            'closed_by' => $user ? $user->id : auth()->id(),
            'closed_at' => now()->toISOString()
        ]);
        
        $this->save();
    }

    public function addTransaction(float $amount, string $type = 'payment', array $details = []): void
    {
        if ($type === 'payment' || $type === 'deposit') {
            $this->current_balance += $amount;
            $this->expected_balance += $amount;
        } elseif ($type === 'refund' || $type === 'expense') {
            $this->current_balance -= $amount;
            $this->expected_balance -= $amount;
        }
        
        $this->addToAuditTrail('transaction', array_merge($details, [
            'type' => $type,
            'amount' => $amount,
            'new_balance' => $this->current_balance,
            'new_expected' => $this->expected_balance
        ]));
        
        $this->save();
    }

    public function reconcile(float $actualBalance, User $user = null, string $notes = null): void
    {
        $this->reconciliation_difference = $actualBalance - $this->expected_balance;
        $this->current_balance = $actualBalance;
        $this->last_reconciled_at = now();
        $this->last_reconciled_by = $user ? $user->id : auth()->id();
        $this->reconciliation_notes = $notes;
        $this->status = $this->is_balanced ? 'active' : 'reconciling';
        
        $this->addToAuditTrail('reconciled', [
            'actual_balance' => $actualBalance,
            'expected_balance' => $this->expected_balance,
            'difference' => $this->reconciliation_difference,
            'reconciled_by' => $this->last_reconciled_by,
            'notes' => $notes
        ]);
        
        $this->save();
    }

    public function adjustBalance(float $adjustment, string $reason, User $user = null): void
    {
        $oldBalance = $this->current_balance;
        $this->current_balance += $adjustment;
        $this->expected_balance += $adjustment;
        
        $this->addToAuditTrail('adjustment', [
            'adjustment_amount' => $adjustment,
            'old_balance' => $oldBalance,
            'new_balance' => $this->current_balance,
            'reason' => $reason,
            'adjusted_by' => $user ? $user->id : auth()->id()
        ]);
        
        $this->save();
    }

    public function setMaintenance(string $reason = null, User $user = null): void
    {
        $this->status = 'maintenance';
        
        $this->addToAuditTrail('maintenance', [
            'reason' => $reason,
            'set_by' => $user ? $user->id : auth()->id()
        ]);
        
        $this->save();
    }

    public function activate(User $user = null): void
    {
        $this->status = 'active';
        $this->is_active = true;
        
        $this->addToAuditTrail('activated', [
            'activated_by' => $user ? $user->id : auth()->id()
        ]);
        
        $this->save();
    }

    public function deactivate(string $reason = null, User $user = null): void
    {
        $this->status = 'inactive';
        $this->is_active = false;
        
        $this->addToAuditTrail('deactivated', [
            'reason' => $reason,
            'deactivated_by' => $user ? $user->id : auth()->id()
        ]);
        
        $this->save();
    }

    public function getTodayTransactions()
    {
        return $this->transactions()->whereDate('transaction_date', today());
    }

    public function getTodayRevenue(): float
    {
        return $this->getTodayTransactions()
                   ->where('transaction_type', 'payment')
                   ->where('status', 'completed')
                   ->sum('amount');
    }

    public function getTodayTransactionCount(): int
    {
        return $this->getTodayTransactions()
                   ->where('status', 'completed')
                   ->count();
    }

    public function getCurrentShift(): ?Shift
    {
        return $this->shifts()
                   ->where('status', 'active')
                   ->whereDate('shift_date', today())
                   ->first();
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
    public static function generateRegisterNumber(): string
    {
        $prefix = 'REG';
        $year = date('Y');
        
        $lastRegister = static::where('register_number', 'like', "{$prefix}-{$year}-%")
                             ->orderBy('id', 'desc')
                             ->first();
        
        $sequence = $lastRegister ? (int)substr($lastRegister->register_number, -3) + 1 : 1;
        
        return sprintf('%s-%s-%03d', $prefix, $year, $sequence);
    }

    public static function getAvailableRegisters($departmentId = null)
    {
        $query = static::active();
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->whereDoesntHave('shifts', function($q) {
            $q->where('status', 'active')
              ->whereDate('shift_date', today());
        })->get();
    }

    public static function getDailyReconciliationReport($date = null): array
    {
        $date = $date ? Carbon::parse($date) : today();
        
        $registers = static::with(['department', 'transactions' => function($q) use ($date) {
            $q->whereDate('transaction_date', $date);
        }])->get();
        
        $report = [
            'date' => $date->format('Y-m-d'),
            'total_registers' => $registers->count(),
            'active_registers' => $registers->where('status', 'active')->count(),
            'registers_with_discrepancy' => $registers->where('has_discrepancy', true)->count(),
            'total_revenue' => 0,
            'total_transactions' => 0,
            'registers' => []
        ];
        
        foreach ($registers as $register) {
            $dayTransactions = $register->transactions;
            $dayRevenue = $dayTransactions->where('transaction_type', 'payment')
                                        ->where('status', 'completed')
                                        ->sum('amount');
            
            $report['total_revenue'] += $dayRevenue;
            $report['total_transactions'] += $dayTransactions->where('status', 'completed')->count();
            
            $report['registers'][] = [
                'id' => $register->id,
                'number' => $register->register_number,
                'name' => $register->register_name,
                'department' => $register->department->name,
                'status' => $register->status,
                'current_balance' => $register->current_balance,
                'expected_balance' => $register->expected_balance,
                'difference' => $register->reconciliation_difference,
                'day_revenue' => $dayRevenue,
                'day_transactions' => $dayTransactions->where('status', 'completed')->count(),
                'needs_reconciliation' => $register->needs_reconciliation
            ];
        }
        
        return $report;
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($register) {
            if (!$register->register_number) {
                $register->register_number = static::generateRegisterNumber();
            }
        });

        static::created(function ($register) {
            $register->addToAuditTrail('created', [
                'register_number' => $register->register_number,
                'department_id' => $register->department_id
            ]);
        });
    }
}