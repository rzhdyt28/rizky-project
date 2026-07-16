<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $guarded = [];
    protected $casts = ['expires_at' => 'datetime', 'is_active' => 'bool'];

    public function isUsable(): bool
    {
        return $this->is_active
            && (! $this->expires_at || $this->expires_at->isFuture())
            && (! $this->max_uses || $this->used_count < $this->max_uses);
    }

    public function applyTo(int $amount): int
    {
        return max(0, $this->type === 'percent'
            ? (int) round($amount - ($amount * $this->value / 100))
            : $amount - $this->value);
    }
}
