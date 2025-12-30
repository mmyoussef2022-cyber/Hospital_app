<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotFaq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'keywords',
        'category',
        'is_active',
        'action_buttons',
        'sort_order',
        'usage_count',
    ];

    protected $casts = [
        'keywords' => 'array',
        'action_buttons' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'usage_count' => 'integer',
    ];

    /**
     * Scope for active FAQs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Search FAQs by keywords
     */
    public static function searchByKeywords($searchText)
    {
        $searchWords = explode(' ', strtolower($searchText));
        
        return self::active()
            ->where(function ($query) use ($searchWords) {
                foreach ($searchWords as $word) {
                    $query->orWhere('question', 'LIKE', "%{$word}%")
                          ->orWhere('answer', 'LIKE', "%{$word}%")
                          ->orWhereJsonContains('keywords', $word);
                }
            })
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }
}
