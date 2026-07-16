<?php

use App\Modules\Portfolio\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

/*
| MODUL PORTFOLIO -> otomatis diprefix /api/portfolio
*/
Route::get('/', [PortfolioController::class, 'show']);
Route::post('/contact', [PortfolioController::class, 'contact'])->middleware('throttle:5,1');
Route::get('/documentation', [PortfolioController::class, 'documentation']);