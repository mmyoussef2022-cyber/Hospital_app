<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorFinancialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'department_id',
        'account_number',
        'commission_rate',
        'fixed_fee',
        'current_balance',
        'total_earned',
        'total_withdrawn',
        'payment_method',
        'is_active'
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'fixed_fee' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // العلاقات
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(DoctorFinancialTransaction::class);
    }

    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // الخصائص المحسوبة
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->current_balance, 2) . ' ريال';
    }

    public function getFormattedCommissionRateAttribute(): string
    {
        return $this->commission_rate . '%';
    }

    public function getPendingEarningsAttribute(): float
    {
        return $this->transactions()
                   ->where('status', 'pending')
                   ->where('transaction_type', 'commission')
                   ->sum('amount');
    }

    public function getMonthlyEarningsAttribute(): float
    {
        return $this->transactions()
                   ->where('status', 'approved')
                   ->whereIn('transaction_type', ['commission', 'bonus'])
                   ->whereMonth('transaction_date', now()->month)
                   ->sum('amount');
    }

    // الدوال المساعدة
    public function calculateCommission(float $serviceAmount): float
    {
        if ($this->payment_method === 'per_visit') {
            return $this->fixed_fee;
        }

        return ($serviceAmount * $this->commission_rate) / 100;
    }

    public function addCommission(float $amount, string $description, string $referenceType = null, int $referenceId = null): DoctorFinancialTransaction
    {
        $transaction = $this->transactions()->create([
            'transaction_number' => $this->generateTransactionNumber(),
            'transaction_type' => 'commission',
            'transaction_type_ar' => 'عمولة',
            'amount' => $amount,
            'description' => $description,
            'description_ar' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'transaction_date' => now()->toDateString(),
            'status' => 'pending',
            'created_by' => auth()->id()
        ]);

        return $transaction;
    }

    public function addBonus(float $amount, string $description): DoctorFinancialTransaction
    {
        return $this->transactions()->create([
            'transaction_number' => $this->generateTransactionNumber(),
            'transaction_type' => 'bonus',
            'transaction_type_ar' => 'مكافأة',
            'amount' => $amount,
            'description' => $description,
            'description_ar' => $description,
            'transaction_date' => now()->toDateString(),
            'status' => 'pending',
            'created_by' => auth()->id()
        ]);
    }

    public function addDeduction(float $amount, string $description): DoctorFinancialTransaction
    {
        return $this->transactions()->create([
            'transaction_number' => $this->generateTransactionNumber(),
            'transaction_type' => 'deduction',
            'transaction_type_ar' => 'خصم',
            'amount' => -$amount, // سالب للخصم
            'description' => $description,
            'description_ar' => $description,
            'transaction_date' => now()->toDateString(),
            'status' => 'pending',
            'created_by' => auth()->id()
        ]);
    }

    public function processWithdrawal(float $amount, string $description): DoctorFinancialTransaction
    {
        if ($amount > $this->current_balance) {
            throw new \Exception('Insufficient balance for withdrawal');
        }

        $transaction = $this->transactions()->create([
            'transaction_number' => $this->generateTransactionNumber(),
            'transaction_type' => 'withdrawal',
            'transaction_type_ar' => 'سحب',
            'amount' => -$amount,
            'description' => $description,
            'description_ar' => $description,
            'transaction_date' => now()->toDateString(),
            'status' => 'approved',
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        $this->current_balance -= $amount;
        $this->total_withdrawn += $amount;
        $this->save();

        return $transaction;
    }

    public function approveTransaction(DoctorFinancialTransaction $transaction): bool
    {
        if ($transaction->status !== 'pending') {
            return false;
        }

        $transaction->status = 'approved';
        $transaction->approved_by = auth()->id();
        $transaction->approved_at = now();
        $transaction->save();

        // تحديث الرصيد
        $this->current_balance += $transaction->amount;
        if ($transaction->amount > 0) {
            $this->total_earned += $transaction->amount;
        }
        $this->save();

        return true;
    }

    public function getMonthlyStatement($month = null, $year = null): array
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
            'total_commissions' => $transactions->where('transaction_type', 'commission')->sum('amount'),
            'total_bonuses' => $transactions->where('transaction_type', 'bonus')->sum('amount'),
            'total_deductions' => abs($transactions->where('transaction_type', 'deduction')->sum('amount')),
            'total_withdrawals' => abs($transactions->where('transaction_type', 'withdrawal')->sum('amount')),
            'closing_balance' => $this->current_balance,
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

        return "DT{$this->doctor_id}{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function createForDoctor(User $doctor, array $settings = []): self
    {
        return static::create([
            'doctor_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'account_number' => static::generateAccountNumber($doctor->id),
            'commission_rate' => $settings['commission_rate'] ?? 30.0,
            'fixed_fee' => $settings['fixed_fee'] ?? 0,
            'current_balance' => 0,
            'total_earned' => 0,
            'total_withdrawn' => 0,
            'payment_method' => $settings['payment_method'] ?? 'monthly',
            'is_active' => true
        ]);
    }

    private static function generateAccountNumber(int $doctorId): string
    {
        return 'DR' . str_pad($doctorId, 6, '0', STR_PAD_LEFT);
    }
}