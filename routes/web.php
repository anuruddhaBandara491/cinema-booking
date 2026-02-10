<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketTypeController;
use App\Models\Movie;
use App\Models\TicketType;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');

Route::get('/bookings/flow/{movie}', function (Movie $movie) {
    $ticketTypes = TicketType::orderBy('name')->get();

    return view('bookings.flow', [
        'movie' => $movie,
        'ticketTypes' => $ticketTypes,
    ]);
})->name('bookings.flow');

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
});

require __DIR__.'/auth.php';
