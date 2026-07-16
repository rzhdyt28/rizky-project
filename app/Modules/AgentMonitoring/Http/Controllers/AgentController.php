<?php

namespace App\Modules\AgentMonitoring\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Modules\AgentMonitoring\Models\JobApplication;
use App\Modules\AgentMonitoring\Models\RunLog;
use Illuminate\Http\Request;

/**
 * Modul 4 — monitoring Auto Apply Agent dari dashboard terpusat.
 * Read-only: Laravel hanya membaca SQLite agent, tidak menulis,
 * agar tidak bentrok dengan proses agent yang sedang berjalan.
 */
class AgentController extends Controller
{
    public function index(Request $request)
    {
        return JobApplication::query()
            ->platform($request->query('platform'))
            ->status($request->query('status'))
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    /** Statistik ringkas untuk widget dashboard monitoring. */
    public function stats()
    {
        return response()->json([
            'total'        => JobApplication::count(),
            'by_status'    => JobApplication::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'by_platform'  => JobApplication::selectRaw('platform, count(*) as total')->groupBy('platform')->pluck('total', 'platform'),
            'scam_blocked' => JobApplication::where('scam_status', 'BLOCK')->count(),
            'avg_match'    => round((float) JobApplication::avg('match_score'), 1),
            'last_runs'    => RunLog::orderByDesc('created_at')->limit(10)->get(),
        ]);
    }
}
