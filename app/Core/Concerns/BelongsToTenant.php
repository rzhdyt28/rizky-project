<?php

namespace App\Core\Concerns;

use App\Core\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

/**
 * Scoping otomatis per-tenant (mode single-database).
 * Setiap query model yang memakai trait BelongsToTenant otomatis difilter
 * berdasarkan tenant aktif (stancl) — mencegah bocor data antar pelanggan.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (! $model->tenant_id && tenancy()->initialized) {
                $model->tenant_id = tenant('id');
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (tenancy()->initialized) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', tenant('id'));
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
