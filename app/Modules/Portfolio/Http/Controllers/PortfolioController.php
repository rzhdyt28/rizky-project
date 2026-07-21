<?php

namespace App\Modules\Portfolio\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Modules\Portfolio\Models\ContactMessage;
use App\Modules\Portfolio\Models\Education;
use App\Modules\Portfolio\Models\Experience;
use App\Modules\Portfolio\Models\ExperiencePhoto;
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

    /** JSON publik untuk halaman dokumentasi/galeri (portofolio.html) — hanya experience yang punya slug. */
    public function documentation()
    {
        $experiences = Experience::whereNotNull('slug')
            ->orderByDesc('start_date')
            ->with('photos')
            ->get()
            ->map(fn (Experience $exp) => [
                'id' => $exp->id,
                'slug' => $exp->slug,
                'company' => $exp->company,
                'role' => $exp->role,
                'start_date' => $exp->start_date,
                'end_date' => $exp->end_date,
                'tags' => $exp->tags ?? [],
                'photos' => $exp->photos->map(fn (ExperiencePhoto $p) => [
                    'id' => $p->id,
                    'url' => $p->url,
                    'caption' => $p->caption,
                ]),
            ]);

        return response()->json(['experiences' => $experiences]);
    }

    /** Form kontak publik -> masuk ke dashboard (throttle di routes). */
    public function contact(Request $request)
    {
        $data = $request->validate([
            'sender_name'  => ['required', 'string', 'max:120'],
            'sender_email' => ['required', 'email', 'max:190'],
            'message'      => ['required', 'string', 'max:5000'],
        ]);

        return response()->json(ContactMessage::create($data), 201);
    }
}
