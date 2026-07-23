<?php

namespace App\Modules\Skripsi\Saw\Http\Controllers;

use App\Modules\Skripsi\Saw\Models\SawCase;
use App\Modules\Skripsi\Saw\Support\SawCalculator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SawCalculationController extends Controller
{
    public function calculate(SawCase $case, SawCalculator $calculator)
    {
        abort_unless($case->user_id === Auth::guard('skripsi')->id(), 403);

        $criteria = $case->criteria()->get(['id', 'weight', 'type'])->map(fn ($c) => [
            'id' => $c->id,
            'weight' => (float) $c->weight,
            'type' => $c->type,
        ])->all();

        $alternatives = $case->alternatives()->get(['id', 'name'])->pluck('name', 'id')->all();

        $matrix = [];
        foreach ($case->alternatives()->with('scores')->get() as $alternative) {
            foreach ($alternative->scores as $score) {
                $matrix[$alternative->id][$score->criterion_id] = (float) $score->value;
            }
        }

        $result = $calculator->calculate($criteria, $alternatives, $matrix);

        $case->update(['result_snapshot' => $result, 'calculated_at' => now()]);

        return response()->json(['result' => $result]);
    }
}
