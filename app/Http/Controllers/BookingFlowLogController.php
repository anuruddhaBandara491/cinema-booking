<?php

namespace App\Http\Controllers;

use App\Models\BookingFlowLog;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class BookingFlowLogController extends Controller
{
    /**
     * Show the booking flow logs index page (admin only).
     */
    public function index(Request $request): View
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to booking flow logs.');
        }

        $movies = Movie::orderBy('title')->get();
        $steps = BookingFlowLog::distinct()->pluck('step_name')->toArray();
        $statuses = ['success', 'failed'];

        // Get paginated logs based on filters
        $logs = $this->applyFilters($request)->paginate(50);

        return view('admin.booking-flow-logs.index', [
            'logs' => $logs,
            'movies' => $movies,
            'steps' => $steps,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Show detailed timeline for a specific session (admin only).
     */
    public function sessionTimeline(string $sessionId): View
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to booking flow logs.');
        }

        $logs = BookingFlowLog::bySession($sessionId)->get();

        if ($logs->isEmpty()) {
            abort(404, 'Session not found.');
        }

        // Calculate metrics for this session
        $firstLog = $logs->first();
        $lastLog = $logs->last();
        $totalDuration = $lastLog->created_at->diffInSeconds($firstLog->created_at);
        $isComplete = $logs->where('step_name', 'payment')
            ->where('action', 'payment_success')
            ->isNotEmpty();

        return view('admin.booking-flow-logs.timeline', [
            'sessionId' => $sessionId,
            'logs' => $logs,
            'firstLog' => $firstLog,
            'lastLog' => $lastLog,
            'totalDuration' => $totalDuration,
            'isComplete' => $isComplete,
        ]);
    }

    /**
     * Show abandoned bookings report (admin only).
     */
    public function abandonedBookings(): View
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to booking flow logs.');
        }

        $abandonedSessions = BookingFlowLog::getAbandonedBookingsSessions();

        return view('admin.booking-flow-logs.abandoned', [
            'abandonedSessions' => $abandonedSessions->paginate(50),
        ]);
    }

    /**
     * Show failed payments report (admin only).
     */
    public function failedPayments(Request $request): View
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to booking flow logs.');
        }

        $failedPayments = BookingFlowLog::getFailedPayments();

        // Apply date range filter if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $failedPayments = $failedPayments->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        return view('admin.booking-flow-logs.failed-payments', [
            'failedPayments' => $failedPayments->paginate(50),
        ]);
    }

    /**
     * Show performance metrics (admin only).
     */
    public function performanceMetrics(): View
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to booking flow logs.');
        }

        $metrics = BookingFlowLog::getStepPerformanceMetrics();

        return view('admin.booking-flow-logs.performance', [
            'metrics' => $metrics,
        ]);
    }

    /**
     * API endpoint for DataTables (admin only).
     */
    public function datatable(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = BookingFlowLog::query();

        // Apply filters
        if ($request->filled('phone_number')) {
            $query->where('phone_number', 'like', '%' . $request->phone_number . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->filled('movie_id')) {
            $query->where('movie_id', $request->movie_id);
        }

        if ($request->filled('step_name')) {
            $query->where('step_name', $request->step_name);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        // Sort
        $orderBy = $request->input('orderBy', 'created_at');
        $orderDirection = $request->input('orderDirection', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // Paginate
        $logs = $query->paginate($request->input('perPage', 25));

        return response()->json([
            'data' => $logs->items(),
            'total' => $logs->total(),
            'per_page' => $logs->perPage(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
        ]);
    }

    /**
     * Apply filters to the logs query.
     */
    protected function applyFilters(Request $request)
    {
        $query = BookingFlowLog::query();

        if ($request->filled('phone_number')) {
            $query->where('phone_number', 'like', '%' . $request->phone_number . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->filled('movie_id')) {
            $query->where('movie_id', $request->movie_id);
        }

        if ($request->filled('step_name')) {
            $query->where('step_name', $request->step_name);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        return $query->orderByDesc('created_at');
    }
}
