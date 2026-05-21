<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // -------------------------
    // Public routes
    // -------------------------
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);
    });

    // -------------------------
    // Protected routes
    // -------------------------
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Categories
        Route::apiResource('categories', CategoryController::class);

        // Expenses
        Route::apiResource('expenses', ExpenseController::class);
    });
});

// Health check
Route::get('/health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]));
