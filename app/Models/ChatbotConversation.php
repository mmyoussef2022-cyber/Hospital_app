<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'visitor_name',
        'visitor_phone',
        'visitor_email',
        'messages',
        'status',
        'transfer_type',
        'last_activity',
    ];

    protected $casts = [
        'messages' => 'array',
        'last_activity' => 'datetime',
    ];

    /**
     * Scope for active conversations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for recent conversations
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('last_activity', '>=', now()->subHours($hours));
    }

    /**
     * Add a message to the conversation
     */
    public function addMessage($message, $sender = 'bot')
    {
        $messages = $this->messages ?? [];
        $messages[] = [
            'sender' => $sender,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        $this->update([
            'messages' => $messages,
            'last_activity' => now(),
        ]);
    }

    /**
     * Mark conversation as transferred
     */
    public function markTransferred($type)
    {
        $this->update([
            'status' => 'transferred',
            'transfer_type' => $type,
        ]);
    }

    /**
     * Mark conversation as completed
     */
    public function markCompleted()
    {
        $this->update([
            'status' => 'completed',
        ]);
    }

    /**
     * Get conversation duration in minutes
     */
    public function getDurationAttribute()
    {
        if (empty($this->messages)) {
            return 0;
        }

        $firstMessage = collect($this->messages)->first();
        $lastMessage = collect($this->messages)->last();

        $start = Carbon::parse($firstMessage['timestamp']);
        $end = Carbon::parse($lastMessage['timestamp']);

        return $start->diffInMinutes($end);
    }
}
