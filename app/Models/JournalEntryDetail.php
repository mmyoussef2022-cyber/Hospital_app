<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'financial_account_id',
        'debit_amount',
        'credit_amount',
        'description',
        'description_ar'
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2'
    ];

    // العلاقات
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    // الخصائص المحسوبة
    public function getDescriptionAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->attributes['description'];
    }

    public function getNetAmountAttribute(): float
    {
        return $this->debit_amount - $this->credit_amount;
    }

    public function getFormattedDebitAttribute(): string
    {
        return $this->debit_amount > 0 ? number_format($this->debit_amount, 2) : '';
    }

    public function getFormattedCreditAttribute(): string
    {
        return $this->credit_amount > 0 ? number_format($this->credit_amount, 2) : '';
    }

    // النطاقات
    public function scopeDebits($query)
    {
        return $query->where('debit_amount', '>', 0);
    }

    public function scopeCredits($query)
    {
        return $query->where('credit_amount', '>', 0);
    }

    public function scopeByAccount($query, $accountId)
    {
        return $query->where('financial_account_id', $accountId);
    }
}