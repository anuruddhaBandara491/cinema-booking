<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingFlowLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'step_name',
        'action',
        'user_name',
        'phone_number',
        'email',
        'movie_id',
        'show_date',
        'show_time',
        'ticket_count',
        'selected_seats',
        'ip_address',
        'user_agent',
        'status',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'show_date' => 'date',
        'selected_seats' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'booking_flow_logs';

    /**
     * Get the movie associated with the log.
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Scope to get logs for a specific session.
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId)->orderBy('created_at', 'asc');
    }

    /**
     * Scope to get logs by phone number.
     */
    public function scopeByPhoneNumber($query, $phoneNumber)
    {
        return $query->where('phone_number', $phoneNumber);
    }

    /**
     * Scope to get logs by email.
     */
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope to get logs by movie.
     */
    public function scopeByMovie($query, $movieId)
    {
        return $query->where('movie_id', $movieId);
    }

    /**
     * Scope to get logs by step name.
     */
    public function scopeByStep($query, $stepName)
    {
        return $query->where('step_name', $stepName);
    }

    /**
     * Scope to get logs by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    }

    /**
     * Get all unique step names that have been logged.
     */
    public static function getUniqueSteps()
    {
        return self::distinct('step_name')->pluck('step_name')->toArray();
    }

    /**
     * Get logs grouped by session ID.
     */
    public static function getSessionTimelines($limit = 100)
    {
        return self::selectRaw('session_id, COUNT(*) as log_count, MAX(created_at) as last_activity, MIN(created_at) as first_activity')
            ->groupBy('session_id')
            ->orderByDesc('last_activity')
            ->limit($limit)
            ->get();
    }

    /**
     * Get incomplete bookings (reached seat selection or ticket count but never completed payment).
     */
    public static function getIncompleteBookings()
    {
        $incompleteSessions = self::whereIn('step_name', ['ticket_count', 'seat_selection'])
            ->where('status', 'success')
            ->pluck('session_id')
            ->unique();

        $completedSessions = self::where('step_name', 'payment')
            ->where('status', 'success')
            ->pluck('session_id')
            ->unique();

        $abandonedSessions = $incompleteSessions->diff($completedSessions);

        return self::whereIn('session_id', $abandonedSessions)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get abandoned bookings with session details.
     */
    public static function getAbandonedBookingsSessions()
    {
        $incomplete = self::getIncompleteBookings();
        $sessions = [];

        foreach ($incomplete->groupBy('session_id') as $sessionId => $logs) {
            $lastLog = $logs->last();
            $sessions[] = [
                'session_id' => $sessionId,
                'user_name' => $lastLog->user_name,
                'phone_number' => $lastLog->phone_number,
                'email' => $lastLog->email,
                'movie_id' => $lastLog->movie_id,
                'last_step' => $lastLog->step_name,
                'abandoned_at' => $lastLog->created_at,
                'log_count' => $logs->count(),
            ];
        }

        return collect($sessions);
    }

    /**
     * Get failed bookings (payment failed).
     */
    public static function getFailedPayments()
    {
        return self::where('step_name', 'payment')
            ->where('action', 'payment_failed')
            ->where('status', 'failed')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get performance metrics between steps.
     */
    public static function getStepPerformanceMetrics()
    {
        $metrics = [];

        $steps = [
            ['from' => 'user_details', 'to' => 'movie_selection'],
            ['from' => 'movie_selection', 'to' => 'ticket_count'],
            ['from' => 'ticket_count', 'to' => 'seat_selection'],
            ['from' => 'seat_selection', 'to' => 'payment'],
        ];

        foreach ($steps as $step) {
            $fromLogs = self::where('step_name', $step['from'])
                ->where('status', 'success')
                ->pluck('session_id');

            $toLogs = self::where('step_name', $step['to'])
                ->where('status', 'success')
                ->whereIn('session_id', $fromLogs)
                ->get();

            if ($toLogs->count() > 0) {
                $timeDifferences = [];

                foreach ($toLogs as $toLog) {
                    $fromLog = self::where('session_id', $toLog->session_id)
                        ->where('step_name', $step['from'])
                        ->latest()
                        ->first();

                    if ($fromLog) {
                        $timeDifferences[] = $toLog->created_at->diffInSeconds($fromLog->created_at);
                    }
                }

                $metrics[$step['from'] . '_to_' . $step['to']] = [
                    'average_seconds' => round(array_sum($timeDifferences) / count($timeDifferences), 2),
                    'min_seconds' => min($timeDifferences),
                    'max_seconds' => max($timeDifferences),
                    'total_transitions' => count($timeDifferences),
                ];
            }
        }

        return $metrics;
    }

    /**
     * Get time spent in a specific step.
     */
    public static function getStepDuration($sessionId, $stepName)
    {
        $logs = self::where('session_id', $sessionId)
            ->where('step_name', $stepName)
            ->orderBy('created_at')
            ->get();

        if ($logs->count() > 0) {
            $firstLog = $logs->first();
            $lastLog = $logs->last();

            return $lastLog->created_at->diffInSeconds($firstLog->created_at);
        }

        return 0;
    }
}
