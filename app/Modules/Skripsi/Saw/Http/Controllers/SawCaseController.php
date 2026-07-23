<?php

namespace App\Modules\Skripsi\Saw\Http\Controllers;

use App\Modules\Skripsi\Saw\Models\SawCase;
use App\Modules\Skripsi\Saw\Support\SawExplanations;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SawCaseController extends Controller
{
    public function index()
    {
        return SawCase::where('user_id', Auth::guard('skripsi')->id())
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'alternative_label' => ['nullable', 'string', 'max:50'],
            'show_description' => ['sometimes', 'boolean'],
        ]);

        $data['user_id'] = Auth::guard('skripsi')->id();
        $data['description'] = ($data['description'] ?? null) ?: SawExplanations::default();
        $data['alternative_label'] = ($data['alternative_label'] ?? null) ?: 'Alternatif';

        return response()->json(SawCase::create($data), 201);
    }

    public function show(SawCase $case)
    {
        $this->authorizeCase($case);

        return $case->load('criteria', 'alternatives.scores');
    }

    public function update(Request $request, SawCase $case)
    {
        $this->authorizeCase($case);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'alternative_label' => ['sometimes', 'required', 'string', 'max:50'],
            'show_description' => ['sometimes', 'boolean'],
        ]);

        $case->update($data);

        return $case;
    }

    public function destroy(SawCase $case)
    {
        $this->authorizeCase($case);
        $case->delete();

        return response()->noContent();
    }

    private function authorizeCase(SawCase $case): void
    {
        abort_unless($case->user_id === Auth::guard('skripsi')->id(), 403);
    }
}
