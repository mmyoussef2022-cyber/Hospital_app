<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_name_ar',
        'account_type',
        'account_type_ar',
        'parent_account_id',
        'balance',
        'is_active',
        'description'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // العلاقات
    public function parentAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'parent_account_id');
    }

    public function childAccounts(): HasMany
    {
        return $this->hasMany(FinancialAccount::class, 'parent_account_id');
    }

    public function journalEntryDetails(): HasMany
    {
        return $this->hasMany(JournalEntryDetail::class);
    }

    // النطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeMainAccounts($query)
    {
        return $query->whereNull('parent_account_id');
    }

    // الخصائص المحسوبة
    public function getAccountNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->account_name_ar : $this->attributes['account_name'];
    }

    public function getAccountTypeDisplayAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->account_type_ar : $this->account_type;
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2) . ' ريال';
    }

    // الدوال المساعدة
    public function debit(float $amount, string $description = null): void
    {
        $this->balance += $amount;
        $this->save();
        
        $this->logTransaction('debit', $amount, $description);
    }

    public function credit(float $amount, string $description = null): void
    {
        $this->balance -= $amount;
        $this->save();
        
        $this->logTransaction('credit', $amount, $description);
    }

    public function getBalanceAsOf($date): float
    {
        $entries = $this->journalEntryDetails()
                       ->whereHas('journalEntry', function($q) use ($date) {
                           $q->where('entry_date', '<=', $date)
                             ->where('status', 'posted');
                       })
                       ->get();

        $balance = 0;
        foreach ($entries as $entry) {
            $balance += $entry->debit_amount - $entry->credit_amount;
        }

        return $balance;
    }

    private function logTransaction(string $type, float $amount, string $description = null): void
    {
        // يمكن إضافة سجل للمعاملات هنا
    }

    // الدوال الثابتة
    public static function createDefaultAccounts(): void
    {
        $accounts = [
            // الأصول
            ['code' => '1000', 'name' => 'Assets', 'name_ar' => 'الأصول', 'type' => 'asset', 'type_ar' => 'أصول'],
            ['code' => '1100', 'name' => 'Current Assets', 'name_ar' => 'الأصول المتداولة', 'type' => 'asset', 'type_ar' => 'أصول', 'parent' => '1000'],
            ['code' => '1110', 'name' => 'Cash', 'name_ar' => 'النقدية', 'type' => 'asset', 'type_ar' => 'أصول', 'parent' => '1100'],
            ['code' => '1120', 'name' => 'Accounts Receivable', 'name_ar' => 'حسابات المدينين', 'type' => 'asset', 'type_ar' => 'أصول', 'parent' => '1100'],
            
            // الخصوم
            ['code' => '2000', 'name' => 'Liabilities', 'name_ar' => 'الخصوم', 'type' => 'liability', 'type_ar' => 'خصوم'],
            ['code' => '2100', 'name' => 'Current Liabilities', 'name_ar' => 'الخصوم المتداولة', 'type' => 'liability', 'type_ar' => 'خصوم', 'parent' => '2000'],
            ['code' => '2110', 'name' => 'Accounts Payable', 'name_ar' => 'حسابات الدائنين', 'type' => 'liability', 'type_ar' => 'خصوم', 'parent' => '2100'],
            
            // حقوق الملكية
            ['code' => '3000', 'name' => 'Equity', 'name_ar' => 'حقوق الملكية', 'type' => 'equity', 'type_ar' => 'حقوق ملكية'],
            
            // الإيرادات
            ['code' => '4000', 'name' => 'Revenue', 'name_ar' => 'الإيرادات', 'type' => 'revenue', 'type_ar' => 'إيرادات'],
            ['code' => '4100', 'name' => 'Medical Services Revenue', 'name_ar' => 'إيرادات الخدمات الطبية', 'type' => 'revenue', 'type_ar' => 'إيرادات', 'parent' => '4000'],
            ['code' => '4200', 'name' => 'Laboratory Revenue', 'name_ar' => 'إيرادات المختبر', 'type' => 'revenue', 'type_ar' => 'إيرادات', 'parent' => '4000'],
            ['code' => '4300', 'name' => 'Radiology Revenue', 'name_ar' => 'إيرادات الأشعة', 'type' => 'revenue', 'type_ar' => 'إيرادات', 'parent' => '4000'],
            
            // المصروفات
            ['code' => '5000', 'name' => 'Expenses', 'name_ar' => 'المصروفات', 'type' => 'expense', 'type_ar' => 'مصروفات'],
            ['code' => '5100', 'name' => 'Salaries and Wages', 'name_ar' => 'الرواتب والأجور', 'type' => 'expense', 'type_ar' => 'مصروفات', 'parent' => '5000'],
            ['code' => '5200', 'name' => 'Medical Supplies', 'name_ar' => 'المستلزمات الطبية', 'type' => 'expense', 'type_ar' => 'مصروفات', 'parent' => '5000'],
        ];

        foreach ($accounts as $accountData) {
            $parentId = null;
            if (isset($accountData['parent'])) {
                $parent = static::where('account_code', $accountData['parent'])->first();
                $parentId = $parent ? $parent->id : null;
            }

            static::firstOrCreate(
                ['account_code' => $accountData['code']],
                [
                    'account_name' => $accountData['name'],
                    'account_name_ar' => $accountData['name_ar'],
                    'account_type' => $accountData['type'],
                    'account_type_ar' => $accountData['type_ar'],
                    'parent_account_id' => $parentId,
                    'balance' => 0,
                    'is_active' => true
                ]
            );
        }
    }
}