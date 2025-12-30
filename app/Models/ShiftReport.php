<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ShiftReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_number',
        'shift_id',
        'user_id',
        'department_id',
        'report_date',
        'shift_start',
        'shift_end',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'cash_difference',
        'total_revenue',
        'cash_payments',
        'card_payments',
        'insurance_payments',
        'other_payments',
        'refunds_issued',
        'adjustments_made',
        'total_transactions',
        'cash_transactions',
        'card_transactions',
        'insurance_transactions',
        'patients_served',
        'appointments_handled',
        'new_registrations',
        'average_transaction_amount',
        'largest_transaction',
        'smallest_transaction',
        'summary_notes',
        'discrepancy_notes',
        'payment_breakdown',
        'hourly_breakdown',
        'service_breakdown',
        'status',
        'completed_at',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'audit_trail'
    ];

    protected $casts = [
        'report_date' => 'date',
        'shift_start' => 'datetime:H:i',
        'shift_end' => 'datetime:H:i',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'cash_payments' => 'decimal:2',
        'card_payments' => 'decimal:2',
        'insurance_payments' => 'decimal:2',
        'other_payments' => 'decimal:2',
        'refunds_issued' => 'decimal:2',
        'adjustments_made' => 'decimal:2',
        'average_transaction_amount' => 'decimal:2',
        'largest_transaction' => 'decimal:2',
        'smallest_transaction' => 'decimal:2',
        'payment_breakdown' => 'array',
        'hourly_breakdown' => 'array',
        'service_breakdown' => 'array',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'audit_trail' => 'array'
    ];

    // Relationships
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'draft' => 'مسودة',
            'completed' => 'مكتمل',
            'reviewed' => 'تمت المراجعة',
            'approved' => 'معتمد'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getFormattedTotalRevenueAttribute(): string
    {
        return number_format($this->total_revenue, 2) . ' ريال';
    }

    public function getFormattedCashDifferenceAttribute(): string
    {
        $diff = $this->cash_difference;
        $sign = $diff >= 0 ? '+' : '';
        return $sign . number_format($diff, 2) . ' ريال';
    }

    public function getHasCashDiscrepancyAttribute(): bool
    {
        return abs($this->cash_difference) >= 0.01;
    }

    public function getNetRevenueAttribute(): float
    {
        return $this->total_revenue - $this->refunds_issued;
    }

    public function getFormattedNetRevenueAttribute(): string
    {
        return number_format($this->net_revenue, 2) . ' ريال';
    }

    public function getIsCompletedAttribute(): bool
    {
        return in_array($this->status, ['completed', 'reviewed', 'approved']);
    }

    public function getIsReviewedAttribute(): bool
    {
        return in_array($this->status, ['reviewed', 'approved']);
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getCanReviewAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getCanApproveAttribute(): bool
    {
        return $this->status === 'reviewed';
    }

    public function getShiftDurationAttribute(): ?int
    {
        if (!$this->shift_start || !$this->shift_end) {
            return null;
        }

        $start = Carbon::parse($this->shift_start);
        $end = Carbon::parse($this->shift_end);
        
        return $start->diffInMinutes($end);
    }

    public function getTransactionsPerHourAttribute(): float
    {
        $duration = $this->shift_duration;
        return $duration > 0 ? ($this->total_transactions / ($duration / 60)) : 0;
    }

    public function getRevenuePerHourAttribute(): float
    {
        $duration = $this->shift_duration;
        return $duration > 0 ? ($this->total_revenue / ($duration / 60)) : 0;
    }

    // Business Logic Methods
    public function updateFromShift(Shift $shift): void
    {
        $transactions = $shift->transactions()->where('status', 'completed')->get();
        
        // Basic shift information
        $this->opening_balance = $shift->opening_cash_balance;
        $this->closing_balance = $shift->closing_cash_balance;
        $this->expected_balance = $shift->expected_cash_balance;
        $this->cash_difference = $shift->cash_difference;
        
        // Transaction totals
        $this->total_transactions = $transactions->count();
        $this->total_revenue = $transactions->where('transaction_type', 'payment')->sum('amount');
        $this->refunds_issued = $transactions->where('transaction_type', 'refund')->sum('amount');
        $this->adjustments_made = $transactions->where('transaction_type', 'adjustment')->sum('amount');
        
        // Payment method breakdown
        $this->cash_payments = $transactions->where('payment_method', 'cash')
                                          ->where('transaction_type', 'payment')
                                          ->sum('amount');
        $this->card_payments = $transactions->where('payment_method', 'card')
                                          ->where('transaction_type', 'payment')
                                          ->sum('amount');
        $this->insurance_payments = $transactions->where('payment_method', 'insurance')
                                               ->where('transaction_type', 'payment')
                                               ->sum('amount');
        $this->other_payments = $this->total_revenue - $this->cash_payments - $this->card_payments - $this->insurance_payments;
        
        // Transaction counts by method
        $this->cash_transactions = $transactions->where('payment_method', 'cash')->count();
        $this->card_transactions = $transactions->where('payment_method', 'card')->count();
        $this->insurance_transactions = $transactions->where('payment_method', 'insurance')->count();
        
        // Patient and service statistics
        $this->patients_served = $transactions->whereNotNull('patient_id')->unique('patient_id')->count();
        
        // Transaction statistics
        $paymentTransactions = $transactions->where('transaction_type', 'payment');
        $this->average_transaction_amount = $paymentTransactions->count() > 0 ? $paymentTransactions->avg('amount') : 0;
        $this->largest_transaction = $paymentTransactions->max('amount') ?? 0;
        $this->smallest_transaction = $paymentTransactions->min('amount') ?? 0;
        
        // Generate breakdowns
        $this->payment_breakdown = $this->generatePaymentBreakdown($transactions);
        $this->hourly_breakdown = $this->generateHourlyBreakdown($transactions);
        $this->service_breakdown = $this->generateServiceBreakdown($transactions);
        
        $this->status = 'completed';
        $this->completed_at = now();
        
        $this->addToAuditTrail('updated_from_shift', [
            'shift_id' => $shift->id,
            'total_transactions' => $this->total_transactions,
            'total_revenue' => $this->total_revenue
        ]);
        
        $this->save();
    }

    public function review(User $reviewer = null, string $notes = null): void
    {
        if ($this->status !== 'completed') {
            throw new \Exception('يمكن مراجعة التقارير المكتملة فقط');
        }

        $this->status = 'reviewed';
        $this->reviewed_by = $reviewer ? $reviewer->id : auth()->id();
        $this->reviewed_at = now();
        
        if ($notes) {
            $this->summary_notes = $notes;
        }

        $this->addToAuditTrail('reviewed', [
            'reviewed_by' => $this->reviewed_by,
            'notes' => $notes
        ]);

        $this->save();
    }

    public function approve(User $approver = null, string $notes = null): void
    {
        if ($this->status !== 'reviewed') {
            throw new \Exception('يمكن اعتماد التقارير المراجعة فقط');
        }

        $this->status = 'approved';
        $this->approved_by = $approver ? $approver->id : auth()->id();
        $this->approved_at = now();
        
        if ($notes) {
            $this->summary_notes = ($this->summary_notes ? $this->summary_notes . "\n\n" : '') . 
                                  "ملاحظات الاعتماد: " . $notes;
        }

        $this->addToAuditTrail('approved', [
            'approved_by' => $this->approved_by,
            'notes' => $notes
        ]);

        $this->save();
    }

    public function reject(string $reason, User $rejector = null): void
    {
        if (!in_array($this->status, ['completed', 'reviewed'])) {
            throw new \Exception('لا يمكن رفض هذا التقرير');
        }

        $this->status = 'draft';
        $this->discrepancy_notes = $reason;

        $this->addToAuditTrail('rejected', [
            'rejected_by' => $rejector ? $rejector->id : auth()->id(),
            'reason' => $reason
        ]);

        $this->save();
    }

    private function generatePaymentBreakdown($transactions): array
    {
        $breakdown = [];
        
        $paymentMethods = ['cash', 'card', 'bank_transfer', 'check', 'insurance', 'online'];
        
        foreach ($paymentMethods as $method) {
            $methodTransactions = $transactions->where('payment_method', $method);
            $payments = $methodTransactions->where('transaction_type', 'payment');
            $refunds = $methodTransactions->where('transaction_type', 'refund');
            
            $breakdown[$method] = [
                'count' => $methodTransactions->count(),
                'payment_count' => $payments->count(),
                'refund_count' => $refunds->count(),
                'payment_amount' => $payments->sum('amount'),
                'refund_amount' => $refunds->sum('amount'),
                'net_amount' => $payments->sum('amount') - $refunds->sum('amount')
            ];
        }
        
        return $breakdown;
    }

    private function generateHourlyBreakdown($transactions): array
    {
        $breakdown = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $hourTransactions = $transactions->filter(function($transaction) use ($hour) {
                return $transaction->transaction_date->format('H') == sprintf('%02d', $hour);
            });
            
            $payments = $hourTransactions->where('transaction_type', 'payment');
            
            $breakdown[sprintf('%02d:00', $hour)] = [
                'count' => $hourTransactions->count(),
                'payment_count' => $payments->count(),
                'amount' => $payments->sum('amount'),
                'patients' => $hourTransactions->whereNotNull('patient_id')->unique('patient_id')->count()
            ];
        }
        
        return array_filter($breakdown, function($data) {
            return $data['count'] > 0;
        });
    }

    private function generateServiceBreakdown($transactions): array
    {
        $breakdown = [];
        
        foreach ($transactions as $transaction) {
            if ($transaction->invoice && $transaction->invoice->items) {
                foreach ($transaction->invoice->items as $item) {
                    $serviceName = $item->item_name;
                    
                    if (!isset($breakdown[$serviceName])) {
                        $breakdown[$serviceName] = [
                            'count' => 0,
                            'amount' => 0,
                            'quantity' => 0
                        ];
                    }
                    
                    $breakdown[$serviceName]['count']++;
                    $breakdown[$serviceName]['amount'] += $item->total_amount;
                    $breakdown[$serviceName]['quantity'] += $item->quantity;
                }
            }
        }
        
        return $breakdown;
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
    public static function generateReportNumber(): string
    {
        $prefix = 'RPT';
        $year = date('Y');
        $month = date('m');
        
        $lastReport = static::whereYear('created_at', $year)
                           ->whereMonth('created_at', $month)
                           ->orderBy('id', 'desc')
                           ->first();
        
        $sequence = $lastReport ? (int)substr($lastReport->report_number, -4) + 1 : 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    public static function getDepartmentSummary($departmentId, $startDate, $endDate): array
    {
        $reports = static::where('department_id', $departmentId)
                        ->whereBetween('report_date', [$startDate, $endDate])
                        ->where('status', '!=', 'draft')
                        ->get();

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'total_reports' => $reports->count(),
            'total_shifts' => $reports->count(),
            'total_revenue' => $reports->sum('total_revenue'),
            'total_transactions' => $reports->sum('total_transactions'),
            'total_patients_served' => $reports->sum('patients_served'),
            'total_cash_difference' => $reports->sum('cash_difference'),
            'reports_with_discrepancy' => $reports->where('has_cash_discrepancy', true)->count(),
            'average_revenue_per_shift' => $reports->count() > 0 ? $reports->avg('total_revenue') : 0,
            'average_transactions_per_shift' => $reports->count() > 0 ? $reports->avg('total_transactions') : 0,
            'payment_method_totals' => [
                'cash' => $reports->sum('cash_payments'),
                'card' => $reports->sum('card_payments'),
                'insurance' => $reports->sum('insurance_payments'),
                'other' => $reports->sum('other_payments')
            ],
            'daily_breakdown' => $reports->groupBy(function($report) {
                return $report->report_date->format('Y-m-d');
            })->map(function($dayReports) {
                return [
                    'shifts' => $dayReports->count(),
                    'revenue' => $dayReports->sum('total_revenue'),
                    'transactions' => $dayReports->sum('total_transactions'),
                    'patients' => $dayReports->sum('patients_served'),
                    'cash_difference' => $dayReports->sum('cash_difference')
                ];
            })
        ];
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (!$report->report_number) {
                $report->report_number = static::generateReportNumber();
            }
        });

        static::created(function ($report) {
            $report->addToAuditTrail('created', [
                'report_number' => $report->report_number,
                'shift_id' => $report->shift_id
            ]);
        });
    }
}