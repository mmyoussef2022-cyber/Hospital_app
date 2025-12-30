<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentFinancialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'account_number',
        'budget_allocated',
        'budget_spent',
        'revenue_generated',
        'expenses_incurred',
        'current_balance',
        'is_active'
    ];

    protected $casts = [
        'budget_allocated' => 'decimal:2',
        'budget_spent' => 'decimal:2',
        'revenue_generated' => 'decimal:2',
        'expenses_incurred' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // العلاقات
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(DepartmentFinancialTransaction::class);
    }

    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // الخصائص المحسوبة
    public function getFormattedBudgetAllocatedAttribute(): string
    {
        return number_format($this->budget_allocated, 2) . ' ريال';
    }

    public function getFormattedBudgetSpentAttribute(): string
    {
        return number_format($this->budget_spent, 2) . ' ريال';
    }

    public function getFormattedRevenueGeneratedAttribute(): string
    {
        return number_format($this->revenue_generated, 2) . ' ريال';
    }

    public function getFormattedCurrentBalanceAttribute(): string
    {
        return number_format($this->current_balance, 2) . ' ريال';
    }

    public function getBudgetUtilizationPercentageAttribute(): float
    {
        if ($this->budget_allocated <= 0) {
            return 0;
        }
        return ($this->budget_spent / $this->budget_allocated) * 100;
    }

    public function getRemainingBudgetAttribute(): float
    {
        return $this->budget_allocated - $this->budget_spent;
    }

    public function getNetProfitAttribute(): float
    {
        return $this->revenue_generated - $this->expenses_incurred;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->revenue_generated <= 0) {
            return 0;
        }
        return ($this->net_profit / $this->revenue_generated) * 100;
    }

    // الدوال المساعدة
    public function allocateBudget(float $amount, string $description): DepartmentFinancialTransaction
    {
        $transaction = $this->transactions()->create([
            'transaction_number' => $this->generateTransactionNumber(),
            'transaction_type' => 'budget_allocation',
            'transaction_type_ar' => 'تخصيص ميزانية',
            'amount' => $amount,
            'description' => $description,
            'description_ar' => $description,
            'transaction_date' => now()->toDateString(),
            'status' => 'approved',
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        $this->budget_allocated += $amount;
        $this->current_balance += $amount;
        $this->save();

        return $transaction;
    }

    public function addRevenue(float $amount, string $description, string $referenceType = null, int $referenceId = null): DepartmentFinancialTransaction
    {
        $transaction = $this->transactions()->create([
            'transaction_number' => $this->generateTransactionNumber(),
            'transaction_type' => 'revenue',
            'transaction_type_ar' => 'إيراد',
            'amount' => $amount,
            'description' => $description,
            'description_ar' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'transaction_date' => now()->toDateString(),
            'status' => 'approved',
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        $this->revenue_generated += $amount;
        $this->current_balance += $amount;
        $this->save();

        return $transaction;
    }

    public function addExpense(float $amount, string $description, string $referenceType = null, int $referenceId = null): DepartmentFinancialTransaction
    {
        if ($amount > $this->current_balance) {
            throw new \Exception('Insufficient budget for this expense');
        }

        $transaction = $this->transactions()->create([
            'transaction_number' => $this->generateTransactionNumber(),
            'transaction_type' => 'expense',
            'transaction_type_ar' => 'مصروف',
            'amount' => -$amount,
            'description' => $description,
            'description_ar' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'transaction_date' => now()->toDateString(),
            'status' => 'approved',
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        $this->expenses_incurred += $amount;
        $this->budget_spent += $amount;
        $this->current_balance -= $amount;
        $this->save();

        return $transaction;
    }

    public function transferTo(DepartmentFinancialAccount $targetDepartment, float $amount, string $description): array
    {
        if ($amount > $this->current_balance) {
            throw new \Exception('Insufficient balance for transfer');
        }

        // معاملة الخصم من القسم المرسل
        $debitTransaction = $this->transactions()->create([
            'transaction_number' => $this->generateTransactionNumber(),
            'transaction_type' => 'transfer',
            'transaction_type_ar' => 'تحويل',
            'amount' => -$amount,
            'description' => "Transfer to {$targetDepartment->department->name}: {$description}",
            'description_ar' => "تحويل إلى {$targetDepartment->department->name_ar}: {$description}",
            'transaction_date' => now()->toDateString(),
            'status' => 'approved',
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // معاملة الإضافة للقسم المستقبل
        $creditTransaction = $targetDepartment->transactions()->create([
            'transaction_number' => $targetDepartment->generateTransactionNumber(),
            'transaction_type' => 'transfer',
            'transaction_type_ar' => 'تحويل',
            'amount' => $amount,
            'description' => "Transfer from {$this->department->name}: {$description}",
            'description_ar' => "تحويل من {$this->department->name_ar}: {$description}",
            'transaction_date' => now()->toDateString(),
            'status' => 'approved',
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // تحديث الأرصدة
        $this->current_balance -= $amount;
        $this->save();

        $targetDepartment->current_balance += $amount;
        $targetDepartment->save();

        return [$debitTransaction, $creditTransaction];
    }

    public function getMonthlyReport($month = null, $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $transactions = $this->transactions()
                            ->whereMonth('transaction_date', $month)
                            ->whereYear('transaction_date', $year)
                            ->orderBy('transaction_date')
                            ->get();

        $summary = [
            'opening_balance' => $this->getBalanceAsOf(now()->startOfMonth()->subDay()),
            'budget_allocated' => $transactions->where('transaction_type', 'budget_allocation')->sum('amount'),
            'total_revenue' => $transactions->where('transaction_type', 'revenue')->sum('amount'),
            'total_expenses' => abs($transactions->where('transaction_type', 'expense')->sum('amount')),
            'transfers_in' => $transactions->where('transaction_type', 'transfer')->where('amount', '>', 0)->sum('amount'),
            'transfers_out' => abs($transactions->where('transaction_type', 'transfer')->where('amount', '<', 0)->sum('amount')),
            'closing_balance' => $this->current_balance,
            'net_profit' => $transactions->where('transaction_type', 'revenue')->sum('amount') - abs($transactions->where('transaction_type', 'expense')->sum('amount')),
            'transactions' => $transactions
        ];

        return $summary;
    }

    private function getBalanceAsOf($date): float
    {
        $transactions = $this->transactions()
                            ->where('transaction_date', '<=', $date)
                            ->where('status', 'approved')
                            ->sum('amount');

        return $transactions;
    }

    private function generateTransactionNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $lastTransaction = $this->transactions()
                               ->whereYear('transaction_date', $year)
                               ->whereMonth('transaction_date', $month)
                               ->orderBy('transaction_number', 'desc')
                               ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "DT{$this->department_id}{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function createForDepartment(Department $department, float $initialBudget = 0): self
    {
        return static::create([
            'department_id' => $department->id,
            'account_number' => static::generateAccountNumber($department->id),
            'budget_allocated' => $initialBudget,
            'budget_spent' => 0,
            'revenue_generated' => 0,
            'expenses_incurred' => 0,
            'current_balance' => $initialBudget,
            'is_active' => true
        ]);
    }

    private static function generateAccountNumber(int $departmentId): string
    {
        return 'DEPT' . str_pad($departmentId, 4, '0', STR_PAD_LEFT);
    }

    // إحصائيات الأقسام
    public static function getDepartmentStats(): array
    {
        return [
            'total_budget_allocated' => static::sum('budget_allocated'),
            'total_budget_spent' => static::sum('budget_spent'),
            'total_revenue_generated' => static::sum('revenue_generated'),
            'total_expenses_incurred' => static::sum('expenses_incurred'),
            'total_current_balance' => static::sum('current_balance'),
            'departments_count' => static::active()->count()
        ];
    }
}