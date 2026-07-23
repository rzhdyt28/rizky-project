<?php

use App\Modules\Skripsi\Core\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
| CORE ROUTES project Skripsi -> /api/skripsi/auth/...
| Auth-only (register/login/logout), tanpa billing/subscription.
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::middleware('auth:skripsi')->group(function () {
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
