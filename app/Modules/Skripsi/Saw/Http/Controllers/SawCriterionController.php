<?php

namespace App\Modules\Skripsi\Saw\Http\Controllers;

use App\Modules\Skripsi\Saw\Models\SawCase;
use App\Modules\Skripsi\Saw\Models\SawCriterion;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SawCriterionController extends Controller
{
    public function index(SawCase $case)
    {
        $this->authorizeCase($case);

        return $case->criteria;
    }

    public function store(Request $request, SawCase $case)
    {
        $this->authorizeCase($case);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'weight' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:benefit,cost'],
        ]);

        $data['case_id'] = $case->id;

        return response()->json(SawCriterion::create($data), 201);
    }

    public function update(Request $request, SawCase $case, SawCriterion $criterion)
    {
        $this->authorizeCriterion($case, $criterion);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'weight' => ['sometimes', 'required', 'numeric', 'min:0'],
            'type' => ['sometimes', 'required', 'in:benefit,cost'],
        ]);

        $criterion->update($data);

        return $criterion;
    }

    public function destroy(SawCase $case, SawCriterion $criterion)
    {
        $this->authorizeCriterion($case, $criterion);
        $criterion->delete();

        return response()->noContent();
    }

    private function authorizeCase(SawCase $case): void
    {
        abort_unless($case->user_id === Auth::guard('skripsi')->id(), 403);
    }

    private function authorizeCriterion(SawCase $case, SawCriterion $criterion): void
    {
        $this->authorizeCase($case);
        abort_unless($criterion->case_id === $case->id, 404);
    }
}
