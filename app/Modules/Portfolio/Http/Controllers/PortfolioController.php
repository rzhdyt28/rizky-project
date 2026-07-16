<?php

namespace App\Modules\Portfolio\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Modules\Portfolio\Models\ContactMessage;
use App\Modules\Portfolio\Models\Education;
use App\Modules\Portfolio\Models\Experience;
use App\Modules\Portfolio\Models\Profile;
use App\Modules\Portfolio\Models\Skill;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    /** JSON publik untuk halaman portofolio (bilingual id/en di tiap field). */
    public function show()
    {
        return response()->json([
            'profile'        => Profile::first(),
            'skills'         => Skill::orderBy('sort_order')->get(),
            'experiences'    => Experience::orderByDesc('start_date')->get(),
            'educations'     => Education::where('kind', 'education')->orderBy('sort_order')->get(),
            'certifications' => Education::where('kind', 'certification')->orderBy('sort_order')->get(),
        ]);
    }

    /** Form kontak publik -> masuk ke dashboard (throttle di routes). */
    public function contact(Request $request)
    {
        $data = $request->validate([
            'sender_name'  => ['required', 'string', 'max:120'],
            'sender_email' => ['required', 'email', 'max:190'],
            'message'      => ['required', 'string', 'max:2000'],
        ]);

        return response()->json(ContactMessage::create($data), 201);
    }
}
