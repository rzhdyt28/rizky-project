<?php

namespace App\Modules\Skripsi\Saw\Http\Controllers;

use App\Modules\Skripsi\Saw\Models\SawAlternative;
use App\Modules\Skripsi\Saw\Models\SawCase;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SawAlternativeController extends Controller
{
    public function index(SawCase $case)
    {
        $this->authorizeCase($case);

        return $case->alternatives()->with('scores')->get();
    }

    public function store(Request $request, SawCase $case)
    {
        $this->authorizeCase($case);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);

        $data['case_id'] = $case->id;

        return response()->json(SawAlternative::create($data), 201);
    }

    public function update(Request $request, SawCase $case, SawAlternative $alternative)
    {
        $this->authorizeAlternative($case, $alternative);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);

        $alternative->update($data);

        return $alternative;
    }

    public function destroy(SawCase $case, SawAlternative $alternative)
    {
        $this->authorizeAlternative($case, $alternative);
        $alternative->delete();

        return response()->noContent();
    }

    private function authorizeCase(SawCase $case): void
    {
        abort_unless($case->user_id === Auth::guard('skripsi')->id(), 403);
    }

    private function authorizeAlternative(SawCase $case, SawAlternative $alternative): void
    {
        $this->authorizeCase($case);
        abort_unless($alternative->case_id === $case->id, 404);
    }
}
