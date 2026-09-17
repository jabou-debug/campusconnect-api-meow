<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CampusConnect API Routes
|--------------------------------------------------------------------------
*/

// ── Health Check (public) ────────────────────────────────────────────────
Route::get("/v1/health", function () {
    return response()->json([
        "success"   => true,
        "message"   => "CampusConnect API is running.",
        "version"   => "1.0.0",
        "timestamp" => now()->toDateTimeString(),
    ]);
});

// ── Authentication (public) ──────────────────────────────────────────────
Route::prefix("v1/auth")->group(function () {
    Route::post("/register", [\App\Http\Controllers\Api\V1\AuthController::class, "register"]);
    Route::post("/login",    [\App\Http\Controllers\Api\V1\AuthController::class, "login"]);
});

// ── Public Read Routes ───────────────────────────────────────────────────
Route::prefix("v1")->group(function () {
    Route::get("announcements",           [\App\Http\Controllers\Api\V1\AnnouncementController::class, "index"]);
    Route::get("announcements/{id}",      [\App\Http\Controllers\Api\V1\AnnouncementController::class, "show"]);
    Route::get("events",                  [\App\Http\Controllers\Api\V1\EventController::class, "index"]);
    Route::get("events/{id}",             [\App\Http\Controllers\Api\V1\EventController::class, "show"]);
});

// ── Protected Routes (auth:sanctum required) ─────────────────────────────
Route::prefix("v1")->middleware("auth:sanctum")->group(function () {

    // Auth
    Route::post("/auth/logout", [\App\Http\Controllers\Api\V1\AuthController::class, "logout"]);
    Route::get("/auth/me",      [\App\Http\Controllers\Api\V1\AuthController::class, "me"]);

    // Announcements — write operations
    Route::post("announcements",        [\App\Http\Controllers\Api\V1\AnnouncementController::class, "store"]);
    Route::put("announcements/{id}",    [\App\Http\Controllers\Api\V1\AnnouncementController::class, "update"]);
    Route::patch("announcements/{id}",  [\App\Http\Controllers\Api\V1\AnnouncementController::class, "update"]);
    Route::delete("announcements/{id}", [\App\Http\Controllers\Api\V1\AnnouncementController::class, "destroy"]);

    // Events — write operations + RSVP
    Route::post("events",                  [\App\Http\Controllers\Api\V1\EventController::class, "store"]);
    Route::put("events/{id}",              [\App\Http\Controllers\Api\V1\EventController::class, "update"]);
    Route::patch("events/{id}",            [\App\Http\Controllers\Api\V1\EventController::class, "update"]);
    Route::delete("events/{id}",           [\App\Http\Controllers\Api\V1\EventController::class, "destroy"]);
    Route::post("events/{id}/rsvp",        [\App\Http\Controllers\Api\V1\EventController::class, "rsvp"]);

    // Schedules
    Route::get("schedules", [\App\Http\Controllers\Api\V1\ScheduleController::class, "index"]);

    // Chatbot
    Route::post("chatbot", [\App\Http\Controllers\Api\V1\ChatbotController::class, "chat"]);

    // Notifications
    Route::get("notifications",              [\App\Http\Controllers\Api\V1\NotificationController::class, "index"]);
    Route::get("notifications/{id}",         [\App\Http\Controllers\Api\V1\NotificationController::class, "show"]);
    Route::patch("notifications/{id}/read",  [\App\Http\Controllers\Api\V1\NotificationController::class, "markAsRead"]);
    Route::delete("notifications/{id}",      [\App\Http\Controllers\Api\V1\NotificationController::class, "destroy"]);
});
