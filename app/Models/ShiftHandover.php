<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ShiftHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'handover_number',
        'from_shift_id',
        'to_shift_id',
        'from_user_id',
        'to_user_id',
        'department_id',
        'cash_register_id',
        'handover_date',
        'cash_balance_handed_over',
        'cash_balance_received',
        'cash_difference',
        'cash_balance_verified',
        'register_keys_handed_over',
        'pending_transactions_reviewed',
        'system_access_transferred',
        'outstanding_tasks',
        'pending_issues',
        'important_notes',
        'equipment_status',
        'handover_notes',
        'checklist_items',
        'pending_transactions',
        'shift_summary',
        'status',
        'started_at',
        'completed_at',
        'witnessed_by',
        'witnessed_at',
        'witness_notes',
        'audit_trail'
    ];

    protected $casts = [
        'handover_date' => 'datetime',
        'cash_balance_handed_over' => 'decimal:2',
        'cash_balance_received' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'cash_balance_verified' => 'boolean',
        'register_keys_handed_over' => 'boolean',
        'pending_transactions_reviewed' => 'boolean',
        'system_access_transferred' => 'boolean',
        'checklist_items' => 'array',
        'pending_transactions' => 'array',
        'shift_summary' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'witnessed_at' => 'datetime',
        'audit_trail' => 'array'
    ];

    // Relationships
    public function fromShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'from_shift_id');
    }

    public function toShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'to_shift_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function witnessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witnessed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'pending' => 'معلق',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'disputed' => 'متنازع عليه'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getHasCashDiscrepancyAttribute(): bool
    {
        return abs($this->cash_difference) >= 0.01;
    }

    // Business Logic Methods
    public function complete(User $user = null): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
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
    public static function generateHandoverNumber(): string
    {
        $prefix = 'HND';
        $year = date('Y');
        $month = date('m');
        
        $lastHandover = static::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->orderBy('id', 'desc')
                            ->first();
        
        $sequence = $lastHandover ? (int)substr($lastHandover->handover_number, -4) + 1 : 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }
}