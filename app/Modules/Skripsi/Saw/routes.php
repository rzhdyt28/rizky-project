<?php

use App\Modules\Skripsi\Saw\Http\Controllers\SawAlternativeController;
use App\Modules\Skripsi\Saw\Http\Controllers\SawCalculationController;
use App\Modules\Skripsi\Saw\Http\Controllers\SawCaseController;
use App\Modules\Skripsi\Saw\Http\Controllers\SawCriterionController;
use App\Modules\Skripsi\Saw\Http\Controllers\SawScoreController;
use Illuminate\Support\Facades\Route;

/*
| SAW (Simple Additive Weighting) -> /api/skripsi/cases/...
*/

Route::middleware('auth:skripsi')->group(function () {
    Route::apiResource('cases', SawCaseController::class)->except(['show'])->parameters(['cases' => 'case']);
    Route::get('cases/{case}', [SawCaseController::class, 'show']);

    Route::get('cases/{case}/criteria', [SawCriterionController::class, 'index']);
    Route::post('cases/{case}/criteria', [SawCriterionController::class, 'store']);
    Route::put('cases/{case}/criteria/{criterion}', [SawCriterionController::class, 'update']);
    Route::delete('cases/{case}/criteria/{criterion}', [SawCriterionController::class, 'destroy']);

    Route::get('cases/{case}/alternatives', [SawAlternativeController::class, 'index']);
    Route::post('cases/{case}/alternatives', [SawAlternativeController::class, 'store']);
    Route::put('cases/{case}/alternatives/{alternative}', [SawAlternativeController::class, 'update']);
    Route::delete('cases/{case}/alternatives/{alternative}', [SawAlternativeController::class, 'destroy']);

    Route::put('cases/{case}/alternatives/{alternative}/scores', [SawScoreController::class, 'upsert']);

    Route::post('cases/{case}/calculate', [SawCalculationController::class, 'calculate']);
});
