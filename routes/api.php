<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and are assigned
| the "api" middleware group. All routes in this file are prefixed with /api.
|
*/

// ✅ Authenticated user route
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// ✅ Protected ticket routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/all', [TicketController::class, 'allTickets']);
    Route::put('/tickets/{id}/status', [TicketController::class, 'update']);
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);

    // ✅ Profile routes
    Route::put('/user/profile', [ProfileController::class, 'updateProfile']);
    Route::put('/user/change-password', [ProfileController::class, 'changePassword']);
    Route::delete('/user/delete', [ProfileController::class, 'deleteAccount']);
});

// ✅ Include Laravel Breeze / Sanctum auth routes
require __DIR__ . '/auth.php';
