<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\BookingController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/courts',    [CourtController::class, 'index']);
Route::get('/courts/{id}', [CourtController::class, 'show']);

// Protected routes (need token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Bookings CRUD
    Route::get('/bookings',        [BookingController::class, 'index']);
    Route::post('/bookings',       [BookingController::class, 'store']);
    Route::get('/bookings/{id}',   [BookingController::class, 'show']);
    Route::put('/bookings/{id}',   [BookingController::class, 'update']);
    Route::delete('/bookings/{id}',[BookingController::class, 'destroy']);

    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::post('/courts',         [CourtController::class, 'store']);
        Route::put('/courts/{id}',     [CourtController::class, 'update']);
        Route::delete('/courts/{id}',  [CourtController::class, 'destroy']);
        Route::get('/admin/bookings',  [BookingController::class, 'allBookings']);
    });
});