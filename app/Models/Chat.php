<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    /** @use HasFactory<\Database\Factories\ChatFactory> */
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        // 'sent_at',
        // 'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (Chat $chat) {
            $chat->sent_at = now();
        });
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopeRead(Builder $query):Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeUnread(Builder $query):Builder
    {
        return $query->whereNull('read_at');
    }
}
