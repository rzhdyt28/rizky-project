<?php

namespace App\Modules\Skripsi\Http\Controllers;

use App\Modules\Skripsi\Saw\Models\SawCase;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Ringkasan untuk halaman landing setelah login. Sengaja ditaruh di luar
 * Saw/ karena dashboard ini akan menggabungkan semua algoritma (AHP/TOPSIS/
 * dst) begitu ditambahkan, bukan cuma SAW.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('skripsi')->user();
        $userId = $user->id;

        // TODO: gabungkan jumlah/daftar terbaru dari algoritma lain (AHP,
        // TOPSIS, dst) begitu modulnya ada — untuk saat ini baru SAW.
        $totalCases = SawCase::where('user_id', $userId)->count();
        $recentCases = SawCase::where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get(['id', 'title', 'alternative_label', 'calculated_at', 'created_at']);

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'nim', 'universitas', 'jurusan', 'angkatan', 'dosen_pembimbing']),
            'total_cases' => $totalCases,
            'recent_cases' => $recentCases,
        ]);
    }
}
