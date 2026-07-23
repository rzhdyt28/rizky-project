<?php

use App\Modules\Skripsi\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Project independen "Skripsi" -> semua endpoint diprefix /api/skripsi
// otomatis oleh ModuleServiceProvider. Tiap algoritma punya routes.php
// sendiri, di-require di sini supaya tetap 1 titik masuk per modul.

require __DIR__.'/Core/routes.php';
require __DIR__.'/Saw/routes.php';

Route::middleware('auth:skripsi')->get('/dashboard', [DashboardController::class, 'index']);
