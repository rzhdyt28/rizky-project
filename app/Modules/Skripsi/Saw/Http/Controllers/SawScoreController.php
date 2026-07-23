<?php

namespace App\Modules\Skripsi\Saw\Http\Controllers;

use App\Modules\Skripsi\Saw\Models\SawAlternative;
use App\Modules\Skripsi\Saw\Models\SawCase;
use App\Modules\Skripsi\Saw\Models\SawScore;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SawScoreController extends Controller
{
    /** Upsert nilai 1 sel matriks (alternatif x kriteria). */
    public function upsert(Request $request, SawCase $case, SawAlternative $alternative)
    {
        abort_unless($case->user_id === Auth::guard('skripsi')->id(), 403);
        abort_unless($alternative->case_id === $case->id, 404);

        $data = $request->validate([
            'criterion_id' => ['required', 'integer', 'exists:App\Modules\Skripsi\Saw\Models\SawCriterion,id'],
            'value' => ['required', 'numeric'],
        ]);

        $criterion = $case->criteria()->whereKey($data['criterion_id'])->firstOrFail();

        $score = SawScore::updateOrCreate(
            ['alternative_id' => $alternative->id, 'criterion_id' => $criterion->id],
            ['value' => $data['value']]
        );

        return response()->json($score);
    }
}
