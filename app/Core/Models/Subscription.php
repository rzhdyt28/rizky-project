<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $connection = 'undangan';
    protected $guarded = [];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function plan()   { return $this->belongsTo(Plan::class); }
    public function tenant() { return $this->belongsTo(Tenant::class, 'tenant_id'); }
    public function payments(){ return $this->hasMany(Payment::class); }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at?->isFuture();
    }
}
