<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\TestController;
use Illuminate\Support\Facades\Route;

// API версионирование
Route::prefix('v1')->group(function () {
    // Без аутентификации
    Route::get('/health', [HealthCheckController::class, 'check'])->name('api.health');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

    // С JWT (php-open-source-saver/jwt-auth, guard api)
    Route::middleware(['auth:api'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('/auth/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');

        // Тестовые роуты
        Route::apiResource('test', TestController::class);
    });
});
