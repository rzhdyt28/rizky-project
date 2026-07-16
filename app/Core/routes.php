<?php

use App\Core\Http\Controllers\AuthController;
use App\Core\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
| CORE ROUTES -> /api/...
| Auth (SPA Sanctum) + billing. Route modul ada di masing-masing
| app/Modules/<Nama>/routes.php (auto-load oleh ModuleServiceProvider).
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->post('/checkout', [CheckoutController::class, 'store']);
Route::post('/payments/midtrans/webhook', [CheckoutController::class, 'webhook']);
