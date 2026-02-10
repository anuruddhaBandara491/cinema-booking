<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');

Route::get('/bookings/flow', function () {
    return view('bookings.flow');
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
