<?php

namespace App\Core\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Core\Models\Coupon;
use App\Core\Models\Plan;
use App\Core\Services\MidtransService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private MidtransService $midtrans) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'coupon'  => ['nullable', 'string'],
        ]);

        $plan   = Plan::where('is_active', true)->findOrFail($data['plan_id']);
        $coupon = isset($data['coupon']) ? Coupon::where('code', $data['coupon'])->first() : null;
        $tenant = $request->user()->tenants()->firstOrFail();

        $payment = $this->midtrans->checkout($tenant, $plan, $coupon);

        return response()->json([
            'order_id'   => $payment->order_id,
            'snap_token' => $payment->snap_token, // dipakai window.snap.pay() di Vue
            'amount'     => $payment->gross_amount,
        ], 201);
    }

    /** Webhook Midtrans — daftarkan URL ini di dashboard Midtrans. */
    public function webhook(Request $request)
    {
        app(\App\Core\Services\MidtransService::class)->handleNotification($request->all());
        return response()->json(['ok' => true]);
    }
}
