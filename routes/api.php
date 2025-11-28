<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and are assigned
| the "api" middleware group. All routes in this file are prefixed with /api.
|
*/

// Authenticated user route
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Protected ticket routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Ticket routes
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/all', [TicketController::class, 'allTickets']);
    Route::get('/tickets/assigned/{userId}', [TicketController::class, 'getTicketsBySupportUser']);
    Route::put('/tickets/{id}/assign', [TicketController::class, 'assign']);
    Route::put('/tickets/{id}/status', [TicketController::class, 'update']);
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);

    // Profile routes
    Route::put('/user/profile', [ProfileController::class, 'updateProfile']);
    Route::put('/user/change-password', [ProfileController::class, 'changePassword']);
    Route::delete('/user/delete', [ProfileController::class, 'deleteAccount']);

    // User Management routes
    Route::get('/users', [ManageUserController::class, 'index']);
    Route::get('/users/support', [ManageUserController::class, 'showAllSupport']);
    Route::post('/users', [ManageUserController::class, 'store']);
    Route::put('/users/{id}', [ManageUserController::class, 'update']);
    Route::put('/users/{id}/status', [ManageUserController::class, 'updateStatus']);
    Route::delete('/users/{id}', [ManageUserController::class, 'destroy']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

require __DIR__ . '/auth.php';
