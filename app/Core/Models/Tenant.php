<?php

namespace App\Core\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = ['id', 'name', 'owner_user_id'];

    public static function getCustomColumns(): array
    {
        return ['id', 'name', 'owner_user_id'];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'tenant_id');
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class, 'tenant_id')
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest('ends_at');
    }

    public function invitations()
    {
        return $this->hasMany(\App\Modules\Invitation\Models\Invitation::class, 'tenant_id');
    }
}
