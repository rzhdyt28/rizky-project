<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenancy()->initialized ? tenant() : $request->user()?->tenants()->first();

        if (! $tenant?->activeSubscription) {
            return response()->json([
                'message' => 'Langganan tidak aktif. Silakan pilih paket terlebih dahulu.',
            ], 402);
        }

        return $next($request);
    }
}
