<?php

namespace App\Core\Http\Controllers;

use App\Core\Models\Tenant;
use App\Core\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Auth untuk SPA Vue terpisah (Sanctum cookie-based).
 * Alur di Vue: GET /sanctum/csrf-cookie -> POST /api/auth/login -> session cookie.
 */
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($data);
        $user->assignRole('user');

        // Setiap pendaftar = 1 tenant (pemilik data undangannya sendiri)
        Tenant::create([
            'id'            => Str::slug($data['name']).'-'.Str::lower(Str::random(6)),
            'name'          => $data['name'],
            'owner_user_id' => $user->id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['user' => $user->only('id', 'name', 'email')], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($data, remember: true)) {
            throw ValidationException::withMessages(['email' => 'Email atau password salah.']);
        }

        $request->session()->regenerate();

        return response()->json(['user' => $request->user()->only('id', 'name', 'email')]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user'   => $user->only('id', 'name', 'email'),
            'roles'  => $user->getRoleNames(),
            'tenant' => $user->tenants()->first()?->only('id', 'name'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
