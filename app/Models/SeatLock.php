<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SeatLock extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'movie_id',
        'show_date',
        'show_time',
        'seat_number',
        'session_id',
        'locked_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'show_date' => 'date',
        'locked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the movie associated with this lock.
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Scope: Get only active (non-expired) locks.
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope: Get locks for a specific show.
     */
    public function scopeForShow($query, $movieId, $showDate, $showTime)
    {
        return $query->where('movie_id', $movieId)
            ->where('show_date', $showDate)
            ->where('show_time', $showTime);
    }

    /**
     * Scope: Get locks for a specific session.
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Check if this lock is still active.
     */
    public function isActive(): bool
    {
        return $this->expires_at > now();
    }

    /**
     * Get remaining time in seconds.
     */
    public function getRemainingSeconds(): int
    {
        $remaining = $this->expires_at->diffInSeconds(now(), false);
        return max(0, $remaining);
    }

    /**
     * Get remaining time formatted as a readable string.
     */
    public function getFormattedRemainingTime(): string
    {
        $seconds = $this->getRemainingSeconds();
        
        if ($seconds <= 0) {
            return '0s';
        }

        $minutes = intdiv($seconds, 60);
        $secs = $seconds % 60;

        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $secs);
        }

        return sprintf('%ds', $secs);
    }
}
