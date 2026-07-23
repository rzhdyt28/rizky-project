<?php

namespace App\Modules\Skripsi\Core\Http\Controllers;

use App\Modules\Skripsi\Core\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Auth untuk SPA Vue terpisah (Sanctum cookie-based), khusus project Skripsi.
 * Auth ini independen dari produk lain (User & database sendiri).
 */
class AuthController extends Controller
{
    /** Field profil yang aman dikembalikan ke frontend (tanpa password). */
    private const PROFILE_FIELDS = ['id', 'name', 'email', 'nim', 'universitas', 'jurusan', 'angkatan', 'dosen_pembimbing'];

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:120'],
            'email'            => ['required', 'email', 'max:190', 'unique:App\Modules\Skripsi\Core\Models\User,email'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'nim'              => ['required', 'string', 'max:50'],
            'universitas'      => ['required', 'string', 'max:150'],
            'jurusan'          => ['required', 'string', 'max:150'],
            'angkatan'         => ['nullable', 'string', 'max:10'],
            'dosen_pembimbing' => ['nullable', 'string', 'max:150'],
        ]);

        $user = User::create($data);

        Auth::guard('skripsi')->login($user);
        $request->session()->regenerate();

        return response()->json(['user' => $user->only(self::PROFILE_FIELDS)], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('skripsi')->attempt($data, remember: true)) {
            throw ValidationException::withMessages(['email' => 'Email atau password salah.']);
        }

        $request->session()->regenerate();

        return response()->json(['user' => Auth::guard('skripsi')->user()->only(self::PROFILE_FIELDS)]);
    }

    public function me(Request $request)
    {
        $user = Auth::guard('skripsi')->user();

        return response()->json(['user' => $user->only(self::PROFILE_FIELDS)]);
    }

    public function logout(Request $request)
    {
        Auth::guard('skripsi')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
