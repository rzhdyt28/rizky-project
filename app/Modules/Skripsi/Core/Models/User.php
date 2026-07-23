<?php

namespace App\Modules\Skripsi\Core\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'skripsi';
    protected $fillable = [
        'name', 'email', 'password',
        'nim', 'universitas', 'jurusan', 'angkatan', 'dosen_pembimbing',
    ];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];
}
