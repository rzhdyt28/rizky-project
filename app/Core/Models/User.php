<?php

namespace App\Core\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, Notifiable, HasRoles, LogsActivity;

    protected $guard_name = 'web';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'owner_user_id');
    }

    /** Cuma staff (super-admin/admin) yang boleh masuk /admin — customer (role 'user') ditolak. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'email'])->logOnlyDirty();
    }
}
