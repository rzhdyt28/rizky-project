<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];
    protected $casts = ['gateway_payload' => 'array'];

    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function coupon()       { return $this->belongsTo(Coupon::class); }
}
