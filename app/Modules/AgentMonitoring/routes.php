<?php

use App\Modules\AgentMonitoring\Http\Controllers\AgentController;
use Illuminate\Support\Facades\Route;

/*
| MODUL AGENT MONITORING -> otomatis diprefix /api/agent-monitoring
| Read-only: membaca SQLite auto-apply-agent (koneksi 'agent').
*/
Route::middleware(['auth:sanctum', 'role:super-admin|admin'])->group(function () {
    Route::get('/applications', [AgentController::class, 'index']);
    Route::get('/stats',        [AgentController::class, 'stats']);
});
