<?php

use App\Http\Controllers\SliderImageController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketTypeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingFlowLogController;
use App\Models\Movie;
use App\Models\TicketType;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $nowShowing = Movie::query()
        ->where('is_published', true)
        ->orderByDesc('published_at')
        ->take(4)
        ->get();

    $upcoming = Movie::query()
        ->where('is_upcoming', true)
        ->orderBy('show_start_date')
        ->take(4)
        ->get();

    return view('welcome', [
        'nowShowing' => $nowShowing,
        'upcoming' => $upcoming,
    ]);
})->name('dashboard');

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');

Route::get('/bookings/flow/{movie}', function (Movie $movie) {
    $ticketTypes = TicketType::orderBy('name')->get();

    return view('bookings.flow', [
        'movie' => $movie,
        'ticketTypes' => $ticketTypes,
    ]);
})->name('bookings.flow');

Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{booking}/confirmation', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

Route::middleware('auth')->group(function () {
    Route::get('/manager/movies', [MovieController::class, 'manage'])
        ->name('manager.movies.index');
    Route::post('/manager/movies', [MovieController::class, 'store'])
        ->name('manager.movies.store');
    Route::put('/manager/movies/{movie}', [MovieController::class, 'update'])
        ->name('manager.movies.update');
    Route::delete('/manager/movies/{movie}', [MovieController::class, 'destroy'])
        ->name('manager.movies.destroy');

    Route::get('/admin/ticket-types', [TicketTypeController::class, 'index'])
        ->name('admin.ticket-types.index');
    Route::post('/admin/ticket-types', [TicketTypeController::class, 'store'])
        ->name('admin.ticket-types.store');
    Route::put('/admin/ticket-types/{ticketType}', [TicketTypeController::class, 'update'])
        ->name('admin.ticket-types.update');
    Route::delete('/admin/ticket-types/{ticketType}', [TicketTypeController::class, 'destroy'])
        ->name('admin.ticket-types.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['role:manager|admin'])->group(function () {
        Route::get('slider/manage', [SliderImageController::class, 'index'])->name('slider.manage');
        Route::get('slider/create', [SliderImageController::class, 'create'])->name('slider.create');
        Route::post('slider/store', [SliderImageController::class, 'store'])->name('slider.store');
        Route::delete('slider/{sliderImage}', [SliderImageController::class, 'destroy'])->name('slider.destroy');

        // Counter Booking Routes
        Route::get('/counter-booking', [BookingController::class, 'counterBookingIndex'])->name('bookings.counter.index');
        Route::post('/counter-booking/book', [BookingController::class, 'counterBookingStore'])->name('bookings.counter.store');
    });

    // Admin-only booking flow logging routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/booking-flow-logs', [BookingFlowLogController::class, 'index'])->name('admin.booking-flow-logs.index');
        Route::get('/admin/booking-flow-logs/session/{sessionId}', [BookingFlowLogController::class, 'sessionTimeline'])->name('admin.booking-flow-logs.timeline');
        Route::get('/admin/booking-flow-logs/abandoned', [BookingFlowLogController::class, 'abandonedBookings'])->name('admin.booking-flow-logs.abandoned');
        Route::get('/admin/booking-flow-logs/failed-payments', [BookingFlowLogController::class, 'failedPayments'])->name('admin.booking-flow-logs.failed-payments');
        Route::get('/admin/booking-flow-logs/performance', [BookingFlowLogController::class, 'performanceMetrics'])->name('admin.booking-flow-logs.performance');
        Route::get('/api/admin/booking-flow-logs/datatable', [BookingFlowLogController::class, 'datatable'])->name('api.booking-flow-logs.datatable');
    });

});

require __DIR__.'/auth.php';
