<?php

namespace App\Core\Services;

use App\Core\Models\Coupon;
use App\Core\Models\Payment;
use App\Core\Models\Plan;
use App\Core\Models\Subscription;
use App\Core\Models\Tenant;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /** Buat subscription pending + Snap token untuk checkout. */
    public function checkout(Tenant $tenant, Plan $plan, ?Coupon $coupon = null): Payment
    {
        $amount = $plan->price;
        if ($coupon?->isUsable()) {
            $amount = $coupon->applyTo($amount);
        }

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id'   => $plan->id,
            'status'    => 'pending',
        ]);

        $payment = Payment::create([
            'order_id'        => 'INV-'.now()->format('ymd').'-'.Str::upper(Str::random(8)),
            'subscription_id' => $subscription->id,
            'coupon_id'       => $coupon?->id,
            'gross_amount'    => $amount,
        ]);

        $payment->snap_token = Snap::getSnapToken([
            'transaction_details' => ['order_id' => $payment->order_id, 'gross_amount' => $amount],
            'customer_details'    => ['first_name' => $tenant->owner?->name, 'email' => $tenant->owner?->email],
            'item_details'        => [[ 'id' => $plan->slug, 'price' => $amount, 'quantity' => 1, 'name' => "Paket {$plan->name}" ]],
        ]);
        $payment->save();

        return $payment;
    }

    /** Dipanggil oleh webhook notifikasi Midtrans. */
    public function handleNotification(array $payload): void
    {
        $signature = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('services.midtrans.server_key'));

        abort_unless(hash_equals($signature, $payload['signature_key'] ?? ''), 403, 'Invalid signature');

        $payment = Payment::where('order_id', $payload['order_id'])->firstOrFail();
        $payment->update([
            'status'          => $payload['transaction_status'],
            'payment_type'    => $payload['payment_type'] ?? null,
            'gateway_payload' => $payload,
        ]);

        if (in_array($payload['transaction_status'], ['settlement', 'capture'])) {
            $sub  = $payment->subscription;
            $plan = $sub->plan;
            $sub->update([
                'status'    => 'active',
                'starts_at' => now(),
                'ends_at'   => now()->addDays($plan->duration_days),
            ]);
            $payment->coupon?->increment('used_count');
        }
    }
}
