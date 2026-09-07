<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health Check (Public)
Route::get('/v1/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'CampusConnect API is running.',
        'version' => '1.0.0',
        'timestamp' => now()->toDateTimeString(),
    ], 200);
});

// Authentication Routes (Public)
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [\App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
});

Route::prefix('v1')->group(function () {
    Route::apiResource('announcements', \App\Http\Controllers\Api\V1\AnnouncementController::class);
        Route::apiResource('events', \App\Http\Controllers\Api\V1\EventController::class);
    Route::apiResource('events', \App\Http\Controllers\Api\V1\EventController::class);
});

// Protected Routes (Requires Authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
    Route::get('/auth/me', [\App\Http\Controllers\Api\V1\AuthController::class, 'me']);
    

    
    Route::get('/schedules', [\App\Http\Controllers\Api\V1\ScheduleController::class, 'index']);
    Route::post('/chatbot', [\App\Http\Controllers\Api\V1\ChatbotController::class, 'chat']);
    
    Route::get('/notifications', [\App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
});